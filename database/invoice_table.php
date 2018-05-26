<?php
require_once "db_remote_table.php";


class InvoiceTable extends DbRemoteTable {

    public static function get_tracked_invoices($database) {
    $sql = "SELECT * FROM Invoice
            ORDER BY `date` DESC";

        return parent::query($sql, $database);
    }

    public static function get_tracked($date, $database) {
        $sql = "SELECT * FROM Invoice
                WHERE `date` = '$date'";

        return parent::query($sql, $database);
    }

    public static function get_unread_count($database) {
        $sql = "SELECT COUNT(id) AS count FROM Invoice
                WHERE status = 1";

        return parent::query($sql, $database)->fetch_assoc()["count"];
    }

    public static function get_bulk_unread_count($database) {
        $sql = "SELECT COUNT(id) AS count FROM InvoiceBulk
                WHERE status = 1";

        return parent::query($sql, $database)->fetch_assoc()["count"];
    }

    public static function get_total_unread_count($database) {
        $sql = "SELECT COUNT(id) AS count FROM
                (SELECT id FROM Invoice
                WHERE status = 1
                UNION ALL
                SELECT id FROM InvoiceBulk
                WHERE status = 1) AS CountTable";

        return parent::query($sql, $database)->fetch_assoc()["count"];
    }

    public static function update_invoice_status($id, $status, $database) {
        $sql = "UPDATE Invoice
                SET status = $status
                WHERE id = $id";

        return parent::query($sql, $database);
    }

    public static function mark_invoice_read($id, $status, $database) {
        $sql = "UPDATE Invoice
                SET status = $status
                WHERE id = $id";

        return parent::query($sql, $database);
    }

    public static function mark_bulk_invoice_read($id, $status, $database) {
        $sql = "UPDATE InvoiceBulk
                SET status = $status
                WHERE id = $id";

        return parent::query($sql, $database);
    }

    public static function get_quantity_required($date, $item_id, $database) {
        $sql = "SELECT quantity_required, quantity_custom FROM Inventory
                WHERE date = '$date'
                AND item_id = $item_id";

        return parent::query($sql, $database);
    }

    public static function get_invoice_table($date, $database) {
        $sql = "SELECT Category.name AS category_name, Item.name AS item_name, Item.id AS item_id,
                    IFNULL(unit, '-') AS unit, IFNULL(quantity, '-') AS quantity, Inv.notes AS notes,
                    Inv.invoice_notes, Inv.quantity_delivered, Category.order_id AS Cat_order, Item.order_id AS Item_order,
                    Item.rounding_option, Item.rounding_factor, IFNULL(price, '-') AS price, Inv.quantity_required,
                    Inv.quantity_custom, Inv.cost_required, Inv.cost_delivered
                FROM Category
                INNER JOIN Item ON Item.category_id = Category.id
                LEFT OUTER JOIN (SELECT * FROM Inventory WHERE date='{$date}') AS Inv ON Inv.item_id = Item.id
                WHERE (Category.creation_date <= '{$date}' AND (Category.deletion_date > '{$date}' OR Category.deletion_date IS NULL))
                AND (Item.creation_date <= '{$date}' AND (Item.deletion_date > '{$date}' OR Item.deletion_date IS NULL))
                ORDER BY Cat_order, Item_order";

        return parent::query($sql, $database);
    }

    public static function update_quantity_delivered($quantity, $item_id, $date, $database) {
        $sql = "INSERT INTO Inventory (item_id, quantity_delivered, `date`)
                VALUES ('$item_id', $quantity, '$date')
                ON DUPLICATE KEY UPDATE
                item_id = VALUES(item_id), quantity_delivered = VALUES(quantity_delivered), `date` = VALUES(`date`)";

        return parent::query($sql, $database);
    }

    public static function update_invoice_note($note, $item_id, $date, $database) {
        $sql = "INSERT INTO Inventory (item_id, invoice_notes, `date`)
                VALUES ('$item_id', '$note', '$date')
                ON DUPLICATE KEY UPDATE
                item_id = VALUES(item_id), invoice_notes = VALUES(invoice_notes), `date` = VALUES(`date`)";

        return parent::query($sql, $database);
    }

    public static function remove_invoice($date) {
        $sql = "DELETE FROM Invoice
                WHERE `date` = '$date'";

        return parent::query($sql);
    }

    public static function get_item_price($item_id, $database) {
        $sql = "SELECT price FROM Item
                WHERE id = '$item_id'";

        return parent::query($sql, $database)->fetch_assoc()["price"];
    }

    public static function update_cost_delivered($cost, $item_id, $date, $database) {
        $sql = "UPDATE Inventory
                SET cost_delivered = $cost
                WHERE item_id = $item_id
                AND `date` = '$date'";

        return parent::query($sql, $database);
    }

    public static function get_sales_tax($database) {
        $sql = "SELECT value FROM Variables WHERE name='SalesTax'";

        if ($result = parent::query($sql, $database)) {
            return (int) $result->fetch_assoc()['value'];
        } else {
            throw new Exception("get_sales_tax query failed");
        }
    }

    public static function get_bulk_quantity($item_id, $date_start, $date_end, $database) {
        $sql = "SELECT quantity_required, quantity_custom FROM Inventory
                WHERE item_id = $item_id
                AND `date` BETWEEN '$date_start' AND '$date_end'";

        return parent::query($sql, $database);
    }
}
?>