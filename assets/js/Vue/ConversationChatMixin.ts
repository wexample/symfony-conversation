import ApiService from '@wexample/symfony-loader/js/Services/ApiService';
import ConversationMessage from '../../Entity/ConversationMessage';

// What the server publishes on the conversation's topic when a line is written.
const EVENT_MESSAGE_CREATED = 'message-created';

// One conversation as any chat draws it, whoever is on the other side — people,
// an agent among them: its lines fetched and heard live, each by its author —
// a face, a name, a marker for an agent —, the reader's own on their side, the
// people it names set apart, what the reader saw marked read. Who takes part
// (`people`), who the reader is among them (`me`) and what they last read
// (`unreadSince`) are the page's to give (ConversationChatPropsService).
//
// Mixed into a chat (abstract-entity-chat), or into a chat that does more —
// an agent's, running the calls it makes (abstract-agent-chat).
const ConversationChatMixin = {
  props: {
    conversationId: {
      type: String,
      required: true
    },
    // The reader's own participant, by id.
    me: {
      type: String,
      default: null
    },
    // Read as a channel is — every line at the start, its author first —,
    // the days apart.
    dayDividers: {
      type: Boolean,
      default: true
    }
  },

  data() {
    return {
      // Whether the reader has the conversation before them: drawn, in a
      // zone that is open. A chat kept in a closed dock is not read.
      isSeen: false
    };
  },

  // Read once seen, and again each time it comes back into view: what was
  // written while its zone was closed is read when the zone opens.
  mounted() {
    if (!(this.$el instanceof Element) || typeof IntersectionObserver === 'undefined') {
      this.isSeen = true;
      this.markRead();

      return;
    }

    this.seenObserver = new IntersectionObserver((entries) => {
      const seen = entries.some((entry) => entry.isIntersecting);

      if (seen && !this.isSeen) {
        this.markRead();
      }

      this.isSeen = seen;
    });
    this.seenObserver.observe(this.$el);
  },

  beforeUnmount() {
    this.seenObserver?.disconnect();
  },

  methods: {
    getEntityClass() {
      return ConversationMessage;
    },

    getEntitiesFetchParams() {
      return {
        query: {
          conversation: this.conversationId
        }
      };
    },

    getPageLength() {
      return 20;
    },

    startsAtLastPage() {
      return true;
    },

    getConversationKey() {
      return this.conversationId;
    },

    // Its author's name; the conversation's own for a line nobody wrote.
    getMessageAuthor(entity) {
      return entity.author?.label ?? this.trans('@vue::author.system');
    },

    // The author's face, as the design system's avatar takes it.
    getMessageAvatar(entity) {
      const author = entity.author;

      return author ? { label: author.label, src: author.src, emoji: author.emoji } : null;
    },

    getMessageAuthorMarker(entity) {
      return entity.author?.marker ?? null;
    },

    getMessageContent(entity) {
      return entity.body ?? '';
    },

    getMessageDate(entity) {
      return entity.dateCreated ?? null;
    },

    getMessageVariant(entity) {
      return entity.type;
    },

    isOwnMessage(entity) {
      return Boolean(this.me) && entity.author?.id === this.me;
    },

    // Neither the author nor the type is sent: the endpoint writes the words
    // of whoever sends them.
    buildMessageEntity(content) {
      return new ConversationMessage({
        conversation: this.conversationId,
        body: content
      });
    },

    // The people the line names travel with it, by their participant id.
    async postMessage(content, files = [], { mentions = [] } = {}) {
      await this.app.getServiceOrFail(ApiService).getClient().post({
        path: 'conversation-message/create',
        options: {
          json: {
            conversation: this.conversationId,
            body: content,
            mentions: mentions.map((person) => person.id).filter(Boolean)
          }
        }
      });

      this.markRead();
    },

    // A line from the other side, arriving while the reader looks at the
    // conversation, is read as it comes; unseen, it waits.
    onIncomingMessage() {
      if (this.isSeen) {
        this.markRead();
      }
    },

    async markRead() {
      await this.app.getServiceOrFail(ApiService).getClient().post({
        path: `conversation/${this.conversationId}/read`
      });
    },

    getLiveThread() {
      return {
        entityName: 'conversation',
        id: this.conversationId,
        event: EVENT_MESSAGE_CREATED
      };
    }
  }
};

export default ConversationChatMixin;
