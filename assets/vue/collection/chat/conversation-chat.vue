<script>
import AbstractEntityChat from '@wexample/symfony-design-system/components/collection/chat/abstract-entity-chat/abstract-entity-chat.vue';
import ApiService from '@wexample/symfony-loader/js/Services/ApiService';
import ConversationMessage from '../../../Entity/ConversationMessage';

// What the server publishes on the conversation's topic when a line is written.
const EVENT_MESSAGE_CREATED = 'message-created';

// A conversation between people: each line by its author — a face, a name, a
// marker for an agent —, the reader's own lines on their side, the people it
// names set apart. Who takes part (`people`), who the reader is among them
// (`me`) and what they last read (`unreadSince`) are the page's to give.
export default {
  extends: AbstractEntityChat,

  template: '#vue-template-wexample-symfony-conversation-bundle-vue-collection-chat-conversation-chat',

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
    layout: {
      type: String,
      default: 'aligned'
    },
    dayDividers: {
      type: Boolean,
      default: true
    }
  },

  mounted() {
    this.markRead();
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

    // A line from someone else arriving while the reader looks at the
    // conversation is read as it comes.
    onLiveEntity(entity) {
      AbstractEntityChat.methods.onLiveEntity?.call(this, entity);

      if (!this.isOwnMessage(entity)) {
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
</script>
