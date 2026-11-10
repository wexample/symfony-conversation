import conversation from '../data/entity/conversation.json';
import message from '../data/entity/conversation_message.json';

type EntitySchema = { name: string };

export default function getGeneratedEntitySchemas(): Record<string, EntitySchema> {
  return {
    [conversation.name]: conversation,
    [message.name]: message,
  };
}
