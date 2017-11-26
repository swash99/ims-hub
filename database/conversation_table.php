<?php
require_once "database_table.php";

class ConversationTable extends DatabaseTable {

    /**
     * Create a conversation.
     *
     * Creates a conversation between two users. An initial message sent by the sender is also created and linked to the conversation view the conversation_id.
     *
     * @param  string $sender_name     Name of the user creating the conversation.
     * @param  string $receiver_name   Name of the user receiving the conversation.
     * @param  string $title           The title of the conversation.
     * @param  string $message         The first message written by the sender.
     * @param  string $date            Date and time the message is created at.
     * @param  string $attachment      Any extra information added with a message.
     * @param  string $sender_status   Conversation status of the sender.
     * @param  string $receiver_status Conversation status of the receiver.
     * @return boolean                 Returns true if query is successful and false if it fails.
     */
    public static function create_conversation($sender_name, $receiver_name, $title, $message, $date, $attachment, $attachment_title, $sender_status, $receiver_status) {
        $sql = "INSERT INTO Conversation (`timestamp`, sender, receiver, title, sender_conversationStatusId, receiver_conversationStatusId)
                VALUES ('$date' , '$sender_name' , '$receiver_name' , '$title', (SELECT id FROM ConversationStatus WHERE status = '$sender_status'),
                       (SELECT id FROM ConversationStatus WHERE status = '$receiver_status'))";

        if ($result = parent::query($sql)) {
            $sql = "SELECT id from Conversation ORDER BY id DESC LIMIT 1";

            if ($result = parent::query($sql)) {
                $id = (int) $result->fetch_assoc()['id'];

                $sql = "INSERT INTO Message (`timestamp`, sender, receiver, message, attachment, attachment_title, conversation_id)
                        VALUES ('$date', '$sender_name', '$receiver_name', '$message', '$attachment', '$attachment_title', '$id')";

                return parent::query($sql);
            }
        }
    }

    /**
     * Get all read and unread conversations.
     *
     * Gets all the conversations which have not been deleted and the given user is a participant of.
     *
     * @param  string $user Name of the user whos conversations are to be retrieved.
     * @return boolean      Returns true if query is successful or false if it fails.
     */
    public static function get_received_conversations($user) {
        $sql = "SELECT id, `timestamp`, sender, receiver, first_name, last_name, sender_status, receiver_status, title, mSender, mTable.message FROM Conversation
                INNER JOIN (SELECT first_name, last_name, username FROM User) AS nameTable
                ON ((sender != receiver) AND (nameTable.username = sender OR nameTable.username = receiver) AND (nameTable.username != '$user')) OR ((sender = receiver) AND  (nameTable.username = sender))
                INNER JOIN (SELECT id AS sstId, status AS sender_status FROM ConversationStatus) AS senderStatusTable
                ON senderStatusTable.sstId = sender_conversationStatusId
                INNER JOIN (SELECT id AS rstId, `status` AS receiver_status FROM ConversationStatus) AS receiverStatusTable
                ON receiverStatusTable.rstId = receiver_conversationStatusId
                LEFT JOIN (SELECT conversation_id, sender AS mSender, message FROM Message AS M1
                            JOIN ( SELECT conversation_id AS conID, MAX(id) AS id FROM Message GROUP BY conID) AS M2
                            ON M1.id = M2.id) AS mTable
                ON mTable.conversation_id = Conversation.id
                WHERE (sender = '$user' AND (sender_status != 'deleted' AND sender_status != 'destroy'))
                OR (receiver = '$user'AND (receiver_status != 'deleted' AND receiver_status != 'destroy'))
                ORDER BY `timestamp` DESC ";

        return parent::query($sql);
    }

    /**
     * Get all deleted conversations.
     *
     * Gets all the conversations that have been deleted and the given user is a participant of.
     *
     * @param  string $user Name of the user whos conversations are to be retrieved.
     * @return boolean      Returns true if query is successful or false if it fails.
     */
    public static function get_deleted_conversations($user) {
        $sql = "SELECT id, `timestamp`, sender, receiver, first_name, last_name, sender_status, receiver_status, title, mSender, message FROM Conversation
                INNER JOIN (SELECT first_name, last_name, username FROM User) AS nameTable
                ON (nameTable.username = sender OR nameTable.username = receiver) AND (nameTable.username != '$user')
                INNER JOIN (SELECT id as sstId, status AS sender_status FROM ConversationStatus) as senderStatusTable
                ON senderStatusTable.sstId = sender_conversationStatusId
                INNER JOIN (SELECT id as rstId, `status` AS receiver_status FROM ConversationStatus) as receiverStatusTable
                ON receiverStatusTable.rstId = receiver_conversationStatusId
                INNER JOIN (SELECT conversation_id, sender AS mSender, message FROM Message AS M1
                            JOIN ( SELECT conversation_id AS conID, MAX(id) AS id FROM Message GROUP BY conID) AS M2
                            ON M1.id = M2.id) AS mTable
                ON mTable.conversation_id = Conversation.id
                WHERE (sender = '$user' AND sender_status = 'deleted' )
                OR (receiver = '$user' AND receiver_status = 'deleted')
                ORDER BY `timestamp` DESC";

        return parent::query($sql);
    }

    /**
     * Update the status of a conversation.
     *
     * Updates the status of a conversation for the given user. The status will either be changed for the sender or the receiver of the conversation.
     *
     * @param  string $user               Name of the user whos conversation status is updated.
     * @param  int    $conversation_id    Id of the conversation to be updated.
     * @param  string $status             New status that will be set for the conversation.
     * @return boolean                    Returns true if query if successful or false if it fails.
     */
    public static function update_conversation_status($user, $conversation_id, $status) {
        $sql = "UPDATE Conversation
                SET sender_conversationStatusId = IF(sender = '$user', (SELECT id FROM ConversationStatus WHERE status = '$status'), sender_conversationStatusId),
                    receiver_conversationStatusId = IF(receiver = '$user', (SELECT id FROM ConversationStatus WHERE status = '$status'), receiver_conversationStatusId)
                WHERE id = '$conversation_id'";

        return parent::query($sql);
    }

    /**
     * Update conversation status of multiple conversations.
     *
     * Updates the conversation status of multiple conversations for the given user.
     *
     * @param  string $user               Name of the user whos conversation statuses are updated.
     * @param  array  $conversation_id    Id's of the conversations to be updated
     * @param  string $status             New status to be set for each conversation.
     * @return boolean                    Returns true if query is successful and false if it fails.
     */
    public static function update_multiple_conversation_status($user, $conversation_id, $status) {
        $sql = "UPDATE Conversation
                SET sender_conversationStatusId = IF(sender = '$user', (SELECT id FROM ConversationStatus WHERE status = '$status'), sender_conversationStatusId),
                    receiver_conversationStatusId = IF(receiver = '$user', (SELECT id FROM ConversationStatus WHERE status = '$status'), receiver_conversationStatusId)
                WHERE id IN ('".implode("','", $conversation_id)."')";

        return parent::query($sql);
    }

    /**
     * Set the date of a conversation to 'destory'.
     *
     * Sets the destroy date of a conversation for the given user. The user will either be a sender or a reciever.
     *
     * @param string $user              Name of the user whos conversation date will be changed.
     * @param int    $conversation_id   Id of the conversation to be updated.
     * @param string $date              Value of the date to be set
     */
    public static function set_destroy_date($user, $conversation_id, $date) {
        $sql = "UPDATE Conversation
                SET sender_destroyDate = IF(sender = '$user', ".$date.", sender_destroyDate),
                receiver_destroyDate = IF(receiver = '$user', ".$date.", receiver_destroyDate)
                WHERE id = '$conversation_id'";

        return parent::query($sql);
    }

    /**
     * Set the destroy date for multiple conversations.
     *
     * @param string $user             Name of the user whos conversation dates will be changed.
     * @param array  $conversation_id  Id's of the conversations to be updated.
     * @param string $date             Value of the date to be set
     */
    public static function set_multiple_destroy_date($user, $conversation_id, $date) {
        $sql = "UPDATE Conversation
                SET sender_destroyDate = IF(sender = '$user', ".$date.", sender_destroyDate),
                receiver_destroyDate = IF(receiver = '$user', ".$date.", receiver_destroyDate)
                WHERE id IN ('".implode("','", $conversation_id)."')";

        return parent::query($sql);
    }

    /**
     * Set the status of a conversation to 'destroy'.
     *
     * Sets the status to 'destroy' for the given user for all conversations that have their destroy dates set to the given date or earlier.
     *
     * @param string $user Name of user whos conversation status will be changed.
     * @param string $date Date on or below which conversation statuses will be changed.
     */
    public static function set_destroy_status($user, $date) {
        $sql = "UPDATE Conversation
                SET sender_conversationStatusId = IF((sender = '$user' AND sender_destroyDate <= '$date'), (SELECT id FROM ConversationStatus WHERE status = 'destroy') , sender_conversationStatusId),
                receiver_conversationStatusId = IF((receiver = '$user' AND receiver_destroyDate <= '$date'), (SELECT id FROM ConversationStatus WHERE status = 'destroy') , receiver_conversationStatusId)";

        return parent::query($sql);
    }

    /**
     * Count unread conversations for a given user.
     *
     * @param  string $user Name of user whos conversations will be counted.
     * @return int          Returns count value on query success.
     * @throws exception    If query fails.
     */
    public static function count_unread_conversations($user) {
        $sql = "SELECT COUNT(id) AS unreadConversations FROM Conversation
                WHERE (sender = '$user' AND sender_conversationStatusId = (SELECT id FROM ConversationStatus WHERE status = 'unread'))
                OR (receiver = '$user' AND receiver_conversationStatusId = (SELECT id FROM ConversationStatus WHERE status = 'unread'))";

        if ($result = parent::query($sql)){
            return $result->fetch_assoc()['unreadConversations'];
        } else {
            throw new Exception("count_unread_conversations query failed");
        }
    }
}
?>
