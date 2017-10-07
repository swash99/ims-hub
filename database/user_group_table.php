<?php
require_once "database_table.php";

class UserGroupTable extends DatabaseTable {

    public static function add_group($group_name) {
        $sql= "INSERT INTO UserGroups (name)
               VALUES ('$group_name')
               ON DUPLICATE KEY UPDATE
               name = VALUES(name)";

        return parent::query($sql);
    }

    public static function remove_group($group_name) {
        $sql = "DELETE FROM UserGroups
                WHERE name = '$group_name'";

        return parent::query($sql);
    }

     public static function get_groups() {
        $sql = "SELECT * FROM UserGroups
                ORDER BY name";

        return parent::query($sql);
    }
}
?>
