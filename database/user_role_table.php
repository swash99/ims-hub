<?php
require_once "database_table.php";

class UserRoleTable extends DatabaseTable {

    /**
     * Gets all user roles.
     *
     * @return object|false  Returns mysqli_result object on query success or false if query fails.
     */
    public static function get_roles() {
        $sql = "SELECT * FROM UserRole";

        return parent::query($sql);
    }

    /**
     * Updates role for given user.
     *
     * @param  string $user_name Name of user to update role of.
     * @param  string $role      Name of the new role.
     * @return boolean           Returns true on query success or false if it fails.
     */
    public static function update_user_role($user_name, $role) {
        $sql = "UPDATE User
                SET userrole_id= (SELECT id FROM UserRole WHERE role='$role')
                WHERE username='$user_name'";

        return parent::query($sql);
    }

}
?>