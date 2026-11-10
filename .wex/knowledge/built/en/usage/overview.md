# Conversations held in the application

`symfony-conversation` holds conversations between the people of an application, and whoever else takes part in them — an agent (`wexample/symfony-ai-conversation`).

| Entity | Table | |
|---|---|---|
| `Conversation` | `conversation` | A title, when it opened and last spoke. The root kind is a conversation between people; engines add theirs (`kind`, one table each). |
| `ConversationParticipant` | `conversation_participant` | Its part (`owner`, `member`), when it joined, what it last read. A person is a `UserConversationParticipant` (`conversation_user_participant`), an agent an `AgentConversationParticipant`. |
| `ConversationMessage` | `conversation_message` | Its author (a participant, none for the conversation's own line), its type (`text`, `system`, `error`, `tool`), its body, the participants it names (`conversation_message_mention`). |

## Who may

A conversation is its participants': read, written in and followed live by the people taking part, nobody else (`ConversationVoter`, VIEW and EDIT; symfony-live asks VIEW for the topic `entity/conversation/event/{id}`).

## Doing things

- `ConversationService::openWith($user, $other)`: the conversation of the two alone, the one they hold when there is one.
- `ConversationService::join()`, `markRead()`.
- `MessageRepository::saveNewMessageAndPushEvent()`: a line written, the conversation's last, `MessageCreatedEvent` said for whoever answers it.

## Api, under `/_api/`

| | |
|---|---|
| `conversation/list` | The conversations of the person asking, the one that last spoke first, with their unread count and last line. |
| `conversation/open/{user}` | The conversation with that person, opened if none. |
| `conversation/{id}`, `conversation/{id}/participants` | One conversation, its people. |
| `conversation/{id}/read` | Read by the person asking, as of now. |
| `conversation-message/list?conversation=`, `conversation-message/create` | Its lines; a line written by the person sending it, with the participants it names (`mentions`). |

## In the page

`vue/collection/chat/conversation-chat`, on the design system's chat: `conversationId`, the reader's participant (`me`), the people (`people`, what the composer offers to name) and what they last read (`unreadSince`). How a person reads — face, handle — is the application's: alias `ConversationPersonPresenterInterface`. The screens — the list, one conversation, writing to someone from their account — are `wexample/symfony-conversation-ds`'s.
