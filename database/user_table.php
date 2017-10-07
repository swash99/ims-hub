<?php
require_once "database_table.php";

class UserTable extends DatabaseTable{

    /**
     * Added a new user in the database.
     *
     * @param string $user_name     User name of new user.
     * @param string $first_name    Firt name of new user.
     * @param string $last_name     Last name of new user.
     * @param string $password      Password of new user.
     * @param string $user_role     Role of new user.
     * @return boolean              Returns true on query success and fails if user already exists.
     * @throws exception            If query fails.
     */
    public static function add_new_user($user_name, $first_name, $last_name, $password, $user_role) {
        $sql = "SELECT username FROM User
                WHERE  username = '$user_name'";

        $result = parent::query($sql);
        $row = $result->fetch_assoc();
        if ($row["username"] == $user_name) {
            return false;
        }
        $sql = "INSERT INTO User (username, first_name, last_name, password_hash, userrole_id)
                VALUES('$user_name', '$first_name', '$last_name', '" .password_hash($password, PASSWORD_DEFAULT). "',
                      (SELECT id FROM UserRole WHERE role='{$user_role}'))";

        if (parent::query($sql)) {
            return true;
        } else {
            throw new Exception("add_new_user query failed");
        }
    }

    /**
     * Gets all users.
     *
     * @return object|false  Returns mysqli_result object on query success or false if query fails.
     */
    public static function get_users() {
        $sql = "SELECT *, User.id AS user_id FROM User
                INNER JOIN UserRole ON User.userrole_id = UserRole.id
                WHERE username != 'System'
                ORDER BY username ASC";

        return parent::query($sql);
    }

    public static function get_admin_users() {
        $sql = "SELECT * FROM User
                INNER JOIN UserRole ON User.userrole_id = UserRole.id
                WHERE role = 'admin'
                AND username != 'System'
                ORDER BY username ASC";

        return parent::query($sql);
    }

    /**
     * Get details for given user only.
     *
     * @param  string $user_name    Name of the user.
     * @return object|false         Returns mysqli_result object on query success or false if query fails.
     */
    public static function get_user_details($user_name) {
        $sql = "SELECT * FROM User
                INNER JOIN UserRole ON User.userrole_id = UserRole.id
                WHERE username = '$user_name'";

        return parent::query($sql);
    }

    /**
     * Update a given users details.
     *
     * @param  string $current_username     Current user name of the user.
     * @param  string $new_username         New user name to update.
     * @param  string $first_name           New first name to update.
     * @param  string $last_name            New last name to update.
     * @param  string $time_zone            New time zone to update.
     * @return boolean                      Return true on query success and false if it fails.
     */
    public static function update_user_details($current_username, $new_username, $first_name, $last_name, $time_zone, $time_out) {
        $sql = "UPDATE User
                SET username = '$new_username',
                    first_name = '$first_name',
                    last_name = '$last_name',
                    time_zone = '$time_zone',
                    time_out = '$time_out'
                WHERE username ='$current_username'";

        return parent::query($sql);
    }

    /**
     * Delete a given user.
     *
     * @param  string $user_name    Name of the user to delete.
     * @return boolean              Returns true on query success and fails if it fails.
     */
    public static function delete_user($user_name) {
        $sql = "DELETE FROM User WHERE username = '$user_name'";

        return parent::query($sql);
    }

    /**
     * Verify give user name and password with database.
     *
     * @param  string $user_name Name of user to verify.
     * @param  string $password  Password of user to verify.
     * @return boolean           Returns true if verified successfully and false it verification fails.
     * @throws exception         If query fails.
     */
    public static function verify_credentials($user_name, $password) {
        $sql = "SELECT * FROM User
                INNER JOIN UserRole ON User.userrole_id = UserRole.id
                WHERE username='$user_name'";

        if (!$result = parent::query($sql)) {
            throw new Exception("verify_credentials query failed");
        }
        $row = $result->fetch_assoc();
        return $row != null && password_verify($password, $row['password_hash']);
    }

    /**
     * Update a given users password.
     *
     * @param  string $user_name        Name of user whos password will be updated.
     * @param  string $new_password     New password to update.
     * @return boolean                  Returns true on query success and false if it fails.
     */
    public static function update_user_password($user_name, $new_password) {
        $sql = "UPDATE User
                SET password_hash='" .password_hash($new_password, PASSWORD_DEFAULT). "'
                WHERE username='$user_name'";

        return parent::query($sql);
    }

    public static function update_user_email($user_name, $email) {
        $sql = "UPDATE User
                SET email = '$email'
                WHERE username = '$user_name'";

        return parent::query($sql);
    }

    /**
     * Set variables for a session for a given user.
     *
     * @param string $user_name     Name of user to set variables for.
     * @return boolean              Returns true on query success and false if it fails.
     */
    public static function set_session_variables($user_name) {
        $sql = "SELECT * FROM User
                INNER JOIN UserRole ON User.userrole_id = UserRole.id
                WHERE username='$user_name'";

        if (!$result = parent::query($sql)) {
            return false;
        }
        $row = $result->fetch_assoc();
        $_SESSION["username"] = $row["username"];
        $_SESSION["userrole"] = $row["role"];

        if (!empty($row["time_zone"])) {
            $_SESSION["timezone"] = $row["time_zone"];
        } else {
            $_SESSION["timezone"] = date_default_timezone_get();
        }
        $_SESSION["date"] = date_format((date_create(NULL, timezone_open($_SESSION["timezone"]))), "Y-m-d");
        $_SESSION["time_out"] = $row["time_out"];

        $sql = "SELECT value FROM Variables
                WHERE name='HistoryEdit'";

        $result = parent::query($sql)->fetch_assoc()['value'];
        $_SESSION["history_limit"] = "$result days";

        return true;
    }
}
?>
