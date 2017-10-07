<?php 
require_once "database_table.php";

class NotificationListTable extends DatabaseTable{

    public static function get_notification_list() {
        $sql = "SELECT * FROM NotificationList";

        return parent::query($sql);
    }
}
?>