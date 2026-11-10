import AbstractApiRepository from '@wexample/js-api-entity/Common/AbstractApiRepository';
import Conversation from '../Entity/Conversation.js';

export default class ConversationRepository extends AbstractApiRepository<Conversation> {
  static getEntityType() {
    return Conversation;
  }
}
