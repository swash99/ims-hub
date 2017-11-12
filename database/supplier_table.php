<?php
require_once "database_table.php";

class SupplierTable extends DatabaseTable {

    /**
     * Add a new supplier or update an existing one.
     *
     * Function adds a new supplier if supplier name does not exist. If it does exist and was deleted on todays date
     * the supplier is updated and its deletion_date set to null.
     *
     * @param  string $supplier_name name of the supplier.
     * @return boolean               returns true on query success and false if supplier already exists.
     * @throws Exception             if query fails.
     */
    public static function add_supplier($supplier_name, $date) {

        $sql = "SELECT * FROM Suppliers
                WHERE name = '{$supplier_name}' AND deletion_date IS NULL";
        $result = parent::query($sql);
        if ($result->num_rows == 0) {
            $sql = "SELECT * FROM Suppliers
                    WHERE name = '{$supplier_name}' AND deletion_date = '{$date}'";
            $result = parent::query($sql);
            if ($result->num_rows == 0) {
                $sql = "INSERT INTO Suppliers (name, creation_date)
                        VALUES ('{$supplier_name}', '{$date}')";
            } else {
                $sql = "UPDATE Suppliers SET deletion_date = NULL
                        WHERE name = '{$supplier_name}' and deletion_date = '{$date}'";
            }
            if (parent::query($sql)) {
                return true;
            } else {
                throw new Exception("add_supplier query failed");
            }
        } else {
            return false;
        }
    }

    /**
     * Delete a supplier in the database.
     *
     * Changes the supplier "deletion_date" to todays date. If successful, updates all items whos "supplier_id" matches the id
     * of the removed supplier and changes item "supplier_id"s to NULL.
     *
     * @param  int $supplier_id      id of the supplier to remove.
     * @return boolean               return true if query is succesful and false if it fails.
     */
    public static function remove_supplier($supplier_id, $category_ids, $date) {
        $sql = "UPDATE Suppliers SET deletion_date = '$date'
                WHERE id = '$supplier_id' and deletion_date IS NULL";
        if (parent::query($sql)) {
            $sql = "UPDATE Category SET deletion_date = '$date'
                    WHERE deletion_date IS NULL AND supplier_id = '$supplier_id'";
            if (parent::query($sql)) {
                $sql = "UPDATE Item SET category_id = NULL
                        WHERE deletion_date IS NULL AND category_id IN ('".implode("','", $category_ids)."')";

            return parent::query($sql);
            }
        }
    }

    /**
     * Get suppliers from the database.
     *
     * Returns all the suppliers that have a "deletion_date" of NULL or greater than todays date.
     *
     * @param  string $date       date till which suppliers will be retrieved.
     * @return object|false       returns mysqli_result object if data is retrieved or false if query fails.
     */
    public static function get_suppliers($date) {
        $sql = "SELECT * FROM Suppliers
                WHERE creation_date <= '{$date}' AND (deletion_date > '{$date}' OR deletion_date IS NULL)
                ORDER BY order_id ASC";

        return parent::query($sql);
    }

    /**
     * Update order_id for the given supplier.
     *
     * @param  int  $supplier_id     id of the supplier to update.
     * @param  int  $order_id        new if to set for order_id.
     * @return boolean               return true if query is succesful and false if it fails.
     */
    public static function update_supplier_order($supplier_id, $order_id) {
        $sql = "UPDATE Suppliers
                SET order_id = '$order_id'
                WHERE id = '$supplier_id'";

        return parent::query($sql);
    }

    /**
     * Get data for print preview table
     *
     * @param  string $date     date till which data should be retrieved.
     * @return object|false     returns mysqli_result object if data is retrieved or false if query fails.
     */
    public static function get_print_preview($date) {
        $sql = "SELECT Category.name AS category_name, Item.name AS item_name, Item.id AS item_id,
                    IFNULL(unit, '-') AS unit, IFNULL(quantity, '-') AS quantity, Inv.notes AS notes,
                    Category.order_id AS Cat_order, Item.order_id AS Item_order
                FROM Category
                INNER JOIN Item ON Item.category_id = Category.id
                LEFT OUTER JOIN (SELECT * FROM Inventory WHERE date='{$date}') AS Inv ON Inv.item_id = Item.id
                WHERE (Category.creation_date <= '{$date}' AND (Category.deletion_date > '{$date}' OR Category.deletion_date IS NULL))
                AND (Item.creation_date <= '{$date}' AND (Item.deletion_date > '{$date}' OR Item.deletion_date IS NULL))
                ORDER BY Cat_order, Item_order";

        return parent::query($sql);
    }

}
?>
