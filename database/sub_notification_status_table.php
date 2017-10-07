<?php 
require_once "database_table.php";

class SubNotificationStatusTable extends DatabaseTable{

    public static function set_notification_status($user_name, $notification_id, $status, $parent_noti_id) {
        $sql = "INSERT INTO SubNotificationStatus (user_name, notification_id, status, parent_noti_id)
                VALUES ('$user_name', '$notification_id', '$status', '$parent_noti_id')
                ON DUPLICATE KEY UPDATE
                user_name = VALUES(user_name), notification_id = VALUES(notification_id),
                status = VALUES(status), parent_noti_id = VALUES(parent_noti_id)";

        return parent::query($sql);
    }

    public static function get_notification_status($user_name, $noti_id, $parent_noti_id) {
        $sql = "SELECT * FROM SubNotificationStatus
                WHERE user_name = '$user_name'
                AND notification_id = '$noti_id'
                AND parent_noti_id = '$parent_noti_id'";

        return parent::query($sql);
    }

    public static function get_all_status_for_notification($notification_name) {
        $sql = "SELECT id FROM SubNotificationList 
                WHERE name = '$notification_name'";

        if ($result = parent::query($sql)) {
            $id = (int) $result->fetch_assoc()["id"];
        }

        $sql = "SELECT * FROM SubNotificationStatus
                INNER JOIN User 
                ON username = user_name
                WHERE notification_id = '$id'";

        return parent::query($sql);
    }
}
?>