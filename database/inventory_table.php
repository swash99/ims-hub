<?php
require_once "database_table.php";

class InventoryTable extends DatabaseTable {

    /**
     * Get inventory and item data for a given category.
     *
     * Gets items and their inventory data for a given category till a given date.
     *
     * @param  int      $category_id    Id of the category to get items of.
     * @param  string   $date           Date till which data will be returned.
     * @return object|false             Returns mysqli_result object on query success or false if query fails.
     */
    public static function get_inventory($category_id, $date) {
        $sql =  "SELECT Item.id, Item.name, IFNULL(Inventory.quantity, null) AS quantity, Item.unit, Inventory.notes,
                        Item.order_id, Item.category_id AS cat_id FROM Item
                LEFT JOIN
                (SELECT * FROM Inventory WHERE Inventory.date = '$date') AS Inventory ON Item.id = Inventory.item_id
                WHERE (Item.creation_date <= '{$date}' AND (Item.deletion_date > '{$date}' OR Item.deletion_date IS NULL))
                ORDER BY Item.order_id ASC";

        return parent::query($sql);
    }

    public static function get_inventory_by_category($category_id, $date) {
        $sql = "SELECT Item.name, Item.id,
                    IFNULL(unit, '-') AS unit, IFNULL(quantity, '-') AS quantity, Inv.notes AS notes,
                    Item.order_id AS Item_order
                FROM Item
                LEFT OUTER JOIN (SELECT * FROM Inventory WHERE date='{$date}') AS Inv ON Inv.item_id = Item.id
                WHERE Item.category_id = $category_id
                AND (Item.creation_date <= '{$date}' AND (Item.deletion_date > '{$date}' OR Item.deletion_date IS NULL))
                ORDER BY Item_order";

        return parent::query($sql);
    }

    /**
     * Update inventory entry if exists or create a new one.
     *
     * @param  string   $date        Date value to update or add.
     * @param  int      $item_id     Id of item to add if id doesn't exist.
     * @param  int      $quantity    Quantity value to update or add.
     * @param  string   $item_note   Note value to update or add.
     * @return boolean               Returns true on query success or false if it fails.
     */
    public static function update_inventory($date, $item_id, $quantity, $item_note) {
        $sql = "INSERT INTO Inventory (`date`, item_id, quantity, notes)
                VALUES ('$date', '$item_id', $quantity, '$item_note')
                ON DUPLICATE KEY UPDATE
                `date`= VALUES(`date`), item_id = VALUES(item_id), quantity = VALUES(quantity),
                notes = VALUES(notes)";

        return parent::query($sql);
    }

    public static function get_quantity_by_date($date, $item_id) {
        $sql = "SELECT quantity FROM Inventory
                WHERE date = '$date'
                AND item_id = $item_id";

        return parent::query($sql)->fetch_assoc()["quantity"];
    }

}
?>
