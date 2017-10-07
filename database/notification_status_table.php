<?php
require_once "database_table.php";

class NotificationStatusTable extends DatabaseTable{

    public static function set_notification_status($user_name, $notification_id, $status) {
        $sql = "INSERT INTO NotificationStatus (user_name, notification_id, status)
                VALUES ('$user_name', '$notification_id', '$status')
                ON DUPLICATE KEY UPDATE
                user_name = VALUES(user_name), notification_id = VALUES(notification_id),
                status = VALUES(status)";

        return parent::query($sql);
    }

    public static function get_notification_status($user_name, $note_id) {
        $sql = "SELECT * FROM NotificationStatus
                WHERE user_name = '$user_name'
                AND notification_id = '$note_id'";

        return parent::query($sql);
    }

    public static function get_all_status_for_notification($notification_name) {
        $sql = "SELECT id FROM NotificationList
                WHERE name = '$notification_name'";

        if ($result = parent::query($sql)) {
            $id = (int) $result->fetch_assoc()["id"];
        }

        $sql = "SELECT * FROM NotificationStatus
                INNER JOIN User
                ON username = user_name
                WHERE notification_id = '$id'";

        return parent::query($sql);
    }

    public static function get_alert_info($noti_name, $sub_noti_name) {
         $sql = "SELECT id FROM NotificationList
                WHERE name = '$noti_name'";

        if ($result = parent::query($sql)) {
            $noti_id = (int) $result->fetch_assoc()["id"];
        }

        $sql = "SELECT id FROM SubNotificationList
                WHERE name = '$sub_noti_name'";

        if ($result = parent::query($sql)) {
            $sub_noti_id = (int) $result->fetch_assoc()["id"];
        }

        $sql = "SELECT NotificationStatus.user_name, User.first_name, User.last_name, User.email,
                       UserRole.role, NotificationStatus.status AS noti_status, SubNotificationStatus.status AS sub_noti_status
                       FROM NotificationStatus
                INNER JOIN User ON username = user_name
                INNER JOIN UserRole ON User.userrole_id = UserRole.id
                INNER JOIN SubNotificationStatus ON NotificationStatus.notification_id = parent_noti_id
                AND NotificationStatus.user_name = SubNotificationStatus.user_name
                WHERE NotificationStatus.notification_id = '$noti_id'
                AND SubNotificationStatus.notification_id = '$sub_noti_id'";

        return parent::query($sql);
    }
}
?>