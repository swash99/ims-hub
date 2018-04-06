<?php
class DbRemoteTable {
    /**
     * Connects to the sql database and runs a sql query.
     *
     * @param  sql_query $sql   mysql query to be performed on the database.
     * @return object|boolean   returns a mysqli_result object if data is retrieved from the database
     *                          or boolean value if there is no data(true on success/false on failure).
     */
    final protected static function query($sql, $database) {
        switch ($database) {
            case "Waterloo":
                $servername = "localhost";
                $username = "root";
                $password = null;
                $dbname = "report_waterloo";
                $conn = null;
                break;
            case "Mississauga":
                $servername = "localhost";
                $username = "root";
                $password = null;
                $dbname = "ivs_database";
                $conn = null;
                break;
            case "Eglinton":
                $servername = "localhost";
                $username = "root";
                $password = null;
                $dbname = "ivs_database";
                $conn = null;
                break;
        }
        if ($conn == null) {
            $conn = new mysqli($servername, $username,
                                     $password, $dbname);
            if($conn->connect_error) {
                die("Connection failed: " .$conn->connect_error);
            }
        }
        return $conn->query($sql);
    }
}
?>
