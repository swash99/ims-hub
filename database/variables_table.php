<?php
require_once "database_table.php";

class VariablesTable extends DatabaseTable {

    /**
     * Get the expected sales value.
     *
     * @return int          Value of the expected sales.
     * @throws exception    If query fails.
     */
    public static function get_expected_sales() {
        $sql = "SELECT value FROM Variables WHERE name='ExpectedSales'";

        if ($result = parent::query($sql)) {
            return (int) $result->fetch_assoc()['value'];
        } else {
            throw new Exception("get_expected_sales query failed");
        }
    }

    /**
     * Updates the expected sales value.
     *
     * @param  int $expected_sales  New value for expected sales.
     * @return boolean              Returns true on query success and false if it fails.
     */
    public static function update_expected_sales($expected_sales) {
        $sql = "INSERT INTO Variables (name, value)
                VALUES ('ExpectedSales', '$expected_sales')
                ON DUPLICATE KEY UPDATE name = VALUES(name), value = VALUES(value)";

        return parent::query($sql);
    }

    /**
     * Get the base sales value.
     * @return int          Value of the base sales.
     * @throws exception    If query fails.
     */
    public static function get_base_sales() {
        $sql = "SELECT value FROM Variables WHERE name='BaseSales'";

        if ($result = parent::query($sql)) {
            return (int) $result->fetch_assoc()['value'];
        } else {
            throw new Exception("get_base_sales query failed");
        }
    }

    /**
     * Update base sales with given value.
     *
     * @param  int $base_sales   New value for base sales.
     * @return boolean           Return true on query success and false if it fails.
     */
    public static function update_base_sales($base_sales) {
        $sql = "INSERT INTO Variables (name, value)
                VALUES ('BaseSales', '$base_sales')
                ON DUPLICATE KEY UPDATE name = VALUES(name), value = VALUES(value)";

        return parent::query($sql);
    }

    public static function update_history_edit($value) {
        $sql = "UPDATE Variables
                SET value = $value
                WHERE name = 'HistoryEdit'";

        return parent::query($sql);
    }

    public static function get_history_edit() {
        $sql = "SELECT value FROM Variables WHERE name = 'HistoryEdit' ";

        if ($result = parent::query($sql)) {
            return (int) $result->fetch_assoc()['value'];
        } else {
            throw new Exception("get_history_edit query failed");
        }
    }
}
?>
