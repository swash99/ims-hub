<?php
require_once "database_table.php";

class UserGroupListTable extends DatabaseTable {

    public static function add_user($user_id, $group_id) {
        $sql = "INSERT INTO UserGroupList (user_id, group_id)
                VALUES ('$user_id', '$group_id')";

        return parent::query($sql);
    }

    public static function get_users($group_id) {
        $sql = "SELECT User.username, User.id, group_id FROM UserGroupList
                INNER JOIN User ON User.id = user_id
                WHERE group_id = '$group_id'";

        return parent::query($sql);
    }

    public static function remove_user($user_id, $group_id) {
        $sql = "DELETE FROM UserGroupList
                WHERE user_id = '$user_id'
                AND group_id = '$group_id'";

        return parent::query($sql);

    }

}
?>