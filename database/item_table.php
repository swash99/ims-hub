<?php
require_once "database_table.php";

class ItemTable extends DatabaseTable {

    /**
     * Add a new item or update an existing one.
     *
     * Adds a new item if the item name does not exist. If it does exist and was deleted on todays date
     * the item is updated and its delete_date is set to null.
     *
     * @param   string  $item_name    Name of the item to add or update.
     * @param   int     $item_unit    Unit value of the item.
     * @return  boolean               Returns true on query success and false if item already exists.
     * @throws  exception             If query fails.
     */
    public static function add_new_item($item_name, $item_unit, $date) {

        $sql = "SELECT * FROM Item
                WHERE name = '{$item_name}'
                AND deletion_date IS NULL";

        $result = parent::query($sql);
        if ($result->num_rows != 0) {
            return false; // Item already exists
        }
        $sql = "SELECT * FROM Item
                WHERE name = '{$item_name}' AND deletion_date = '{$date}'";
        $result = parent::query($sql);
        if ($result->num_rows == 0) {
            $sql = "INSERT INTO Item (name, unit, creation_date)
                    VALUES('{$item_name}', '{$item_unit}', '{$date}')";
        } else {
            $sql = "UPDATE Item
                    SET deletion_date = null, unit = '{$item_unit}'
                    WHERE name = '{$item_name}' AND deletion_date = '{$date}'";
        }
        if (parent::query($sql)) {
            return true; // Item added sucessfully
        } else {
            throw new Exception("add_new_item query failed!");
        }
    }

    /**
     * Get the total count of items present.
     *
     * @return  int         Returns count on query success.
     * @throws  exception   If query fails.
     */
    public static function get_items_count() {
        $sql = "SELECT COUNT(name) AS num FROM Item
                WHERE Item.deletion_date IS NULL
                ORDER BY name ASC";

        if ($result = parent::query($sql)) {
            return $result->fetch_assoc()['num'];
        } else {
            throw new Exception("get_items_count query failed");
        }
    }

    /**
     * Get items from database.
     *
     * @return object|false     Returns mysqli_result object on query success or false if query fails.
     */
    public static function get_items() {
        $sql = "SELECT name, unit, id FROM Item
                WHERE Item.deletion_date IS NULL
                ORDER BY name ASC";

        return parent::query($sql);
    }

    /**
     * Gets all items within a specified range.
     *
     * @param  int  $offset      Start value of the item range.
     * @param  int  $limit       End value of the item range.
     * @return object|false      Returns mysqli_result object on query success or false if query fails.
     */
    public static function get_items_in_range($offset, $limit) {
        $sql = "SELECT name, unit, id FROM Item
                WHERE Item.deletion_date IS NULL
                ORDER BY name ASC
                LIMIT $offset, $limit";

        return parent::query($sql);
    }

    /**
     * Gets all items with their categories.
     *
     * @return object|false    Returns mysqli_result object on query success or false if query fails.
     */
    public static function get_items_categories($date) {
        $sql = "SELECT Item.name, unit, Item.id, Category.name AS category_name, Item.order_id FROM Item
                LEFT JOIN Category ON Item.category_id = Category.id
                WHERE Item.creation_date <= '{$date}' AND (Item.deletion_date > '{$date}' OR Item.deletion_date IS NULL)
                ORDER BY Category.order_id ASC, Item.order_id ASC";

        return parent::query($sql);
    }

    /**
     * Delete an item in the database.
     *
     * Updates an items deletion_date to todays date for the given item name.
     *
     * @param  string   $item_name  Name of the item to be deleted.
     * @return boolean              Returns true on query success and false if it fails.
     */
    public static function delete_item($item_name) {
        $date = date('Y-m-d');

        $sql = "UPDATE Item SET deletion_date = '{$date}'
                WHERE name = '{$item_name}'";

        return parent::query($sql);
    }

    /**
     * Delete multiple items.
     *
     * @param  array $item_ids   Id's of items to be deleted.
     * @return boolean           Returns true on query success and false if it fails.
     */
    public static function delete_multiple_items($item_ids, $date) {

        $sql = "UPDATE Item SET deletion_date = '$date'
                WHERE id IN ('".implode("','", $item_ids)."')";

        return parent::query($sql);
    }

    /**
     * Update an item in the database.
     *
     * Updates item name and item unit for a give item id.
     *
     * @param  int      $item_id    Id of the item to be updated.
     * @param  string   $new_name   New name of the item.
     * @param  int      $new_unit   New unit value of the item.
     * @return boolean              Returns true on query success and false if it fails.
     */
    public static function update_item_details($item_id, $new_name, $new_unit) {
        $sql = "UPDATE Item
                SET name='$new_name',
                    unit='$new_unit'
                WHERE id='$item_id'";

        return parent::query($sql);
    }

    /**
     * Get total item count for items assigned to a given category till a given date.
     *
     * @param   int     $category_id    Id of the category whos item count is needed.
     * @param   string  $date           Date till which items will be counted.
     * @return  int                     Returns count value if query is successful
     * @throws  excetion                If query fails.
     */
    public static function get_total_items($category_id, $date) {
        $sql = "SELECT COUNT(Item.name) AS num
                FROM Category INNER JOIN Item
                ON Category.id = Item.category_id
                WHERE Category.id = {$category_id}
                    AND (Category.creation_date <= '{$date}' AND (Category.deletion_date > '{$date}' OR Category.deletion_date IS NULL))
                    AND (Item.creation_date <= '{$date}' AND (Item.deletion_date > '{$date}' OR Item.deletion_date IS NULL))";

        if ($result = parent::query($sql)) {
            return $result->fetch_assoc()['num'];
        } else {
            throw new Exception("get_total_items query failed");
        }
    }

    /**
     * Get total count of items that have been assigned a quantity for a given category till a given date.
     *
     * @param  int      $category_id    Id of the category whos item count is needed.
     * @param  string   $date           Date till which items will be counted.
     * @return int                      Returns count value if query is susccessful.
     * @throws exception                If query fails.
     */
    public static function get_updated_items_count($category_id, $date) {
        $sql = "SELECT COUNT(Item.name) as num
                FROM Category INNER JOIN Item ON Category.id = Item.category_id
                LEFT JOIN Inventory ON Item.id = Inventory.item_id
                WHERE Category.id = {$category_id} AND Inventory.date = '{$date}'
                    AND (Category.creation_date <= '{$date}' AND (Category.deletion_date > '{$date}' OR Category.deletion_date IS NULL))
                    AND (Item.creation_date <= '{$date}' AND (Item.deletion_date > '{$date}' OR Item.deletion_date IS NULL))
                    AND Inventory.quantity IS NOT NULL";

        if ($result = parent::query($sql)) {
            return $result->fetch_assoc()['num'];
        } else {
            throw new Exception("get_updated_items_count query failed");
        }
    }

    /**
     * Get items assigned to a given category.
     *
     * @param  string   $category_name  Name of the category to get items for.
     * @return object|false             Returns mysqli_result object on query success or false if query fails.
     */
    public static function get_categorized_items($category_name) {
        $sql = "SELECT Item.name, Item.unit, Item.id FROM Item
                INNER JOIN Category ON Item.category_id = Category.id
                WHERE Category.name = '{$category_name}' AND Item.deletion_date IS NULL
                ORDER BY Item.order_id ASC";

       return parent::query($sql);
    }

    /**
     * Get items that have not been assigned to any category.
     *
     * @return object|false     Returns mysqli_result object on query success or false if query fails.
     */
    public static function get_uncategorized_items() {
        $sql = "SELECT name, unit, id FROM Item WHERE category_id IS NULL AND deletion_date IS NULL";

        return parent::query($sql);
    }

    /**
     * Update the category a given item is assigned to.
     *
     * If a category name is given the item is assigned to that name. Otherwise the items category_id is set to NULL.
     *
     * @param  string   $category_name  Name of the category to assign item to.
     * @param  string   $item_name      Name of the item to assign a category for.
     * @return boolean                  Returns true on query success and false if it fails.
     */
    public static function update_items_category($category_name, $item_name) {
        $category_id = null;

        if ($category_name != null) {
            $sql = "SELECT Category.id FROM Category
                    WHERE Category.name = '$category_name' AND deletion_date IS NULL";

            if ($result = parent::query($sql)) {
                $category_id = $result->fetch_assoc()['id'];
            }
        }
        $sql = "UPDATE Item SET category_id =" .($category_id == null ? "null":$category_id). " WHERE name = '$item_name'";

        return parent::query($sql);
    }

    /**
     * Updates a given items list order value.
     *
     * @param  int  $category_id    Id of the item to update.
     * @param  int  $order_id       Order number to set for the item.
     * @return boolean              Returns true on query success and false if it fails.
     */
    public static function update_item_order($item_id, $order_id) {
        $sql = "UPDATE Item
                SET order_id = '$order_id'
                WHERE id = '$item_id'";

        return parent::query($sql);
    }

}
?>
