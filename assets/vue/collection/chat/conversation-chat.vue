<script>
import AbstractEntityChat from '@wexample/symfony-design-system/components/collection/chat/abstract-entity-chat/abstract-entity-chat.vue';
import ConversationChatMixin from '../../../js/Vue/ConversationChatMixin';

// A conversation, whoever takes part (ConversationChatMixin): the chat of
// messages and nothing more. A conversation whose agent acts in the page is
// drawn by a chat that runs its calls besides (symfony-ai-conversation's
// message-chat).
export default {
  extends: AbstractEntityChat,

  mixins: [ConversationChatMixin],

  template: '#vue-template-wexample-symfony-conversation-bundle-vue-collection-chat-conversation-chat'
};
</script>
