import AbstractApiEntity from '@wexample/js-api-entity/Common/AbstractApiEntity';
import type { ApiEntitySchema } from '@wexample/js-api-entity/Common/AbstractApiEntity';
import schema from '../data/entity/conversation.json';

export default class Conversation extends AbstractApiEntity {
  static readonly entityName = 'conversation';

  // Typed as any schema, so that a subclass may answer its own.
  static retrieveEntitySchema(): ApiEntitySchema {
    return schema;
  }
}
