<?php 
require_once "database_table.php";

class SubNotificationListTable extends DatabaseTable{

    public static function get_notification_list() {
        $sql = "SELECT * FROM SubNotificationList";

        return parent::query($sql);
    }
}
?>