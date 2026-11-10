import AbstractApiRepository from '@wexample/js-api-entity/Common/AbstractApiRepository';
import ConversationMessage from '../Entity/ConversationMessage.js';

export default class MessageRepository extends AbstractApiRepository<ConversationMessage> {
  static getEntityType() {
    return ConversationMessage;
  }
}
