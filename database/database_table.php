<?php
class DatabaseTable {
    private static $servername = "localhost";
    private static $username = "root";
    private static $password = null;
    private static $dbname = "ims_hub";
    private static $conn = null;

    /**
     * Connects to the sql database and runs a sql query.
     *
     * @param  sql_query $sql   mysql query to be performed on the database.
     * @return object|boolean   returns a mysqli_result object if data is retrieved from the database
     *                          or boolean value if there is no data(true on success/false on failure).
     */
    final protected static function query($sql) {
        if (self::$conn == null) {
            self::$conn = new mysqli(self::$servername, self::$username,
                                     self::$password, self::$dbname);
            if(self::$conn->connect_error) {
                die("Connection failed: " .self::$conn->connect_error);
            }
        }
        return self::$conn->query($sql);
    }
}
?>
