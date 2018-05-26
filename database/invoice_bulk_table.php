<?php
require_once "db_remote_table.php";


class InvoiceBulkTable extends DbRemoteTable {

    public static function get_tracked_invoices($database) {
    $sql = "SELECT * FROM InvoiceBulk
            ORDER BY date_end DESC";

        return parent::query($sql, $database);
    }

    public static function track_invoice($date_start, $date_end, $database) {

        $sql = "INSERT INTO InvoiceBulk (date_start, date_end)
                VALUES ('$date_start', '$date_end')
                ON DUPLICATE KEY UPDATE
                date_start = VALUES(date_start), date_end = VALUES(date_end)";

        return parent::query($sql, $database);
    }

    public static function get_tracked($date_start, $date_end, $database) {
        $sql = "SELECT * FROM InvoiceBulk
                WHERE  date_start = '$date_start'
                AND date_end = '$date_end'";

        if (parent::query($sql, $database)) {
            $tracked = count($result->fetch_assoc());
            return $tracked;
        }
    }

    public static function get_status($date_created, $database) {
        $sql = "SELECT * FROM InvoiceBulk
                WHERE  date_created = '$date_created'";

        return parent::query($sql, $database);
    }

    public static function update_invoice_status($id, $status, $database) {
        $sql = "UPDATE InvoiceBulk
                SET status = $status
                WHERE id = $id";

        return parent::query($sql, $database);
    }

    public static function get_bulk_invoice($date_start, $date_end, $database) {
        $sql = "SELECT Category.name AS category_name, Item.name AS item_name, Item.id AS item_id,
                    IFNULL(unit, '-') AS unit, IFNULL(quantity, '-') AS quantity, Inv.notes AS notes,
                    Inv.invoice_notes, Category.order_id AS Cat_order, Item.order_id AS Item_order,
                    Item.rounding_option, Item.rounding_factor, IFNULL(price, '-') AS price, Inv.quantity_required,
                    Inv.quantity_custom, Inv.quantity_delivered, Inv.quantity_received, Inv.cost_required, Inv.cost_delivered
                FROM Category
                INNER JOIN Item ON Item.category_id = Category.id
                LEFT OUTER JOIN 
                    (SELECT item_id, SUM(quantity_required) AS quantity_required, SUM(cost_required) AS cost_required,
                    SUM(quantity) AS quantity, SUM(quantity_custom) AS quantity_custom,
                    SUM(quantity_delivered) AS quantity_delivered, SUM(quantity_received) AS quantity_received, 
                    SUM(cost_delivered) AS cost_delivered, invoice_notes, notes FROM Inventory 
                    WHERE `date` BETWEEN '$date_start' AND '$date_end'
                    GROUP BY item_id) AS Inv ON Inv.item_id = Item.id
                WHERE (Category.creation_date <= '$date_start' AND (Category.deletion_date > '$date_start' OR Category.deletion_date IS NULL))
                AND (Item.creation_date <= '$date_start' AND (Item.deletion_date > '$date_start' OR Item.deletion_date IS NULL))
                ORDER BY Cat_order, Item_order";

        return parent::query($sql, $database);
                
    }

    public static function remove_invoice($date_start, $date_end, $database) {
        $sql = "DELETE FROM InvoiceBulk
                WHERE date_start = '$date_start'
                AND date_end = '$date_end'";

        return parent::query($sql, $database);
    }
}
?>