<?php
require_once "database_table.php";

class MessageTable extends DatabaseTable {

    /**
     * Create a new messsage.
     *
     * @param  string   $sender             Name of the sender of the message.
     * @param  string   $receiver           Name of the receiver of the message.
     * @param  string   $message            The message to be saved.
     * @param  int      $conversation_id    Id of the conversation the message belongs to.
     * @param  string   $date               Date on which message is created.
     * @return boolean                      Returns true on query success and false of it fails.
     */
    public static function create_message($sender, $receiver, $message, $conversation_id, $date) {
        $sql = "INSERT INTO Message (`timestamp`, sender, receiver, message, conversation_id)
                VALUES ('$date', '$sender', '$receiver', '$message', '$conversation_id')";

        if (parent::query($sql)) {
            $sql = "UPDATE Conversation
                    SET `timestamp`='$date'
                    WHERE id = '$conversation_id'";

            return parent::query($sql);
        }
    }

    /**
     * Get all messages for a given conversation.
     *
     * @param  int   $conversation_id    Id of the conversation to get messages of.
     * @return object|false              Returns mysqli_result object on query success or false if query fails.
     */
    public static function get_messages($conversation_id) {
        $sql = "SELECT * FROM Message
                INNER JOIN (SELECT first_name, last_name, username FROM User) as nameTable
                ON nameTable.username = Message.sender
                WHERE conversation_id = '$conversation_id'
                ORDER BY `timestamp` ASC";

        return parent::query($sql);
    }
}
?>