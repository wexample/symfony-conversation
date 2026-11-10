import AbstractApiEntity from '@wexample/js-api-entity/Common/AbstractApiEntity';
import schema from '../data/entity/conversation_message.json';

export default class ConversationMessage extends AbstractApiEntity {
  static readonly entityName = 'conversationMessage';

  static retrieveEntitySchema() {
    return schema;
  }
}
