<?php
session_start();
require_once "database/user_table.php";
require_once "database/user_role_table.php";
require_once "database/category_table.php";
require_once "database/item_table.php";
require_once "database/inventory_table.php";
require_once "database/conversation_table.php";
require_once "database/notification_status_table.php";
require_once "database/sub_notification_status_table.php";
require_once "database/user_group_list_table.php";

$readonly = $_SESSION["date"] <= date('Y-m-d', strtotime("-".$_SESSION["history_limit"])) ? "readonly" : "";

/*---------------manage_users.php-------------*/
if (isset($_POST["newRole"])) {
    UserRoleTable::update_user_role($_POST["roleUserName"], $_POST["newRole"]);
}

/*----------------------update_inventory.php-----------------*/
if (isset($_POST["itemQuantity"])) {
    echo InventoryTable::update_inventory($_POST["itemDate"], $_POST["itemId"], $_POST["itemQuantity"], $_POST["itemNote"]);
}
/*--------------edit_items.php------------*/
if (isset($_POST["updateItemQuantity"])) {
    BaseQuantityTable::update_base_quantity($_POST["itemId"], $_POST["quantity"]);
}

/*-----------------edit_categories.php-------------*/
if (isset($_POST["getCategorizedItems"])) {

    $result = ItemTable::get_categorized_items($_POST["getCategorizedItems"]);
    if ($result) {
        echo '<ul class="category_list" id="categorized_list" >';
        while ($row = $result->fetch_assoc()) {
            echo '<li class="list_li" id="'.$row["id"].'" item-name="'.$row["name"].'">' .$row["name"]. ' </li>';
        }
         echo '</ul>';
    }
}

if (isset($_POST["UpdateItemOrder"])) {
    $order_number = 0;
    foreach ($_POST["itemIds"] as $value) {
        ItemTable::update_item_order($value, $order_number);
        $order_number++;
    }
}

if (isset($_POST["UpdateCategoryOrder"])) {
    $order_number = 1;
    foreach ($_POST["categoryIds"] as $value) {
        CategoryTable::update_category_order($value, $order_number);
        $order_number++;
    }
}

/*----------------edit_categories.php----------------*/
if (isset($_POST["UpdateItemsCategory"])) {
    ItemTable::update_items_category($_POST["categoryName"], $_POST["itemName"]);
}

/*---------user_account.php--------------*/
if (isset($_POST["userName"])) {

    if (UserTable::verify_credentials($_POST["userName"], $_POST['password'])) {
        echo "true";
    }  else {
        echo "false";
    }
}

/*-------------------------user_account.php----------------------*/
if (isset($_POST["timeZoneRegion"])) {

    $timezones = array( "Africa"=>"1", "America"=>"2", "Asia"=>"16", "Australia"=>"64", "Europe"=>"128");

    foreach (timezone_identifiers_list($timezones[$_POST["timeZoneRegion"]]) as $tz){
        $tzs = explode("/", $tz, 2);
        echo  '<option value="' .$tzs[1]. '">' .$tzs[1]. '</option>' ;
   }
}

/*--------------------------messages.php----------------------------*/
if (isset($_POST["sessionName"])) {
    echo ConversationTable::count_unread_conversations($_POST["sessionName"]);
}

/*--------------------------------received_messages.php----------------*/
if (isset($_POST["checkedId"])) {
    echo ConversationTable::update_multiple_conversation_status($_SESSION["username"], $_POST["checkedId"], $_POST["newStatus"]);
}

if(isset($_POST["getItemCount"])) {
    echo ItemTable::get_items_count();
}

if(isset($_POST["addItem"])) {
     try {
        if(!ItemTable::add_new_item($_POST["itemName"], $_POST["itemUnit"], $_SESSION["date"])) {
            echo 'item exists';
        } else {
            echo 'item added';
        }
    } catch (Exception $e) {
        echo '<div class="error">'.$e->getMessage().'</div>';
    }
}

if(isset($_POST["getItems"])) {
    $result = ItemTable::get_items_categories($_SESSION["date"]);
    $current_category = 1;
    while($row = $result->fetch_assoc()) {
        if ($row["category_name"] != $current_category AND $row["category_name"] != null) {
            $current_category = $row["category_name"];
            echo '
                <tr class="item_category_tr">
                    <td id="category" colspan="3" class="table_heading">'.$row["category_name"].'<span class="arrow_down float_right collapse_arrow"></span></td>
                </tr>';
        } else if ($row["category_name"] != $current_category AND $row["category_name"] == null) {
            $current_category = $row["category_name"];
            echo '
                <tr class="item_category_tr">
                    <td id="category" colspan="3" class="table_heading">Uncategorized Items<span class="arrow_down float_right collapse_arrow"></span></td>
                </tr>';
        }
        echo '
            <tr>
                <td class="td_drawer">
            </td>
                <input type="hidden" class="item_id" name="item_id" value="'.$row["id"].'">
                <td class="td_checkbox">
                    <div class="checkbox">
                        <input type="checkbox" class="item_checkbox" name="checkbox[]" value="'.$row["id"].'" form="checkbox_form">
                        <span class="checkbox_style"></span>
                    </div>
                </td>
                <td><input type="text" name="item_name" value="'.$row["name"].'" onchange=updateItem(this) class="align_center item_name"></td>
                <td><input type="text" name="item_unit" value="'.$row["unit"].'" onchange=updateItem(this) class="align_center"></td>
            </tr>';
    }
}

if(isset($_POST["getItemsInRange"])) {
    $result = ItemTable::get_items_in_range($_POST["offset"], $_POST["limit"]);
    while ($row = $result->fetch_assoc()) {
    echo ' <tr>
            <form action="edit_items.php" method="post">
            <td><input type="text" name="item_name" value="'.$row["name"].'" onchange="this.form.submit()" class="align_center"></td>
            <td><input type="text" name="item_unit" value="'.$row["unit"].'" onchange="this.form.submit()" class="align_center"></td>
            <td><input type="number" name="item_quantity" step="any" min="0" value="'.$row["quantity"].'" onchange=quantityChange(this) class="align_center"></td>
            <input type="hidden" name="item_id" value="'.$row["id"].'">
            </form>
            <td>
                <form action="edit_items.php" method="post" onsubmit="return confirm(\'delete this item?\');">
                    <input type="hidden" name="delete_item" value="'.$row["name"].'">
                    <input type="submit" value="delete" class="button" >
                </form>
            </td>
        </tr>';
    }
}

if (isset($_POST["getPrintPreview"])) {
    $result = InventoryTable::get_inventory($_POST["categoryId"], $_POST["date"]);
    while ($row = $result -> fetch_assoc()) {
        echo '<tr>
                <td class="item_name">'.$row["name"].'</td>
                <td>'.$row["unit"].'</td>
                <td class="td_quantity">'.$row["quantity"].'</td>
                <td id="td_notes">'.$row["notes"].'</td>
                <input type="hidden" value='.$row["id"].'>
                <input type="hidden" id="cat_id" value='.$row["cat_id"].'>
            </tr>';
    }
}

if (isset($_POST["getInventory"])) {
    $result = InventoryTable::get_inventory($_POST["categoryId"], $_POST["date"]);
    while ($row = $result -> fetch_assoc()) {
        $entry_last = InventoryTable::get_last_entry($row["id"], $_POST["date"])->fetch_assoc();
        $entry_second_last = InventoryTable::get_second_last_entry($row["id"], $_POST["date"])->fetch_assoc();
        echo '<tr>
                <td class="item_name">'.$row["name"].'</td>
                <td>'.$row["unit"].'</td>
                <td class="td_quantity"><input class="quantity_input align_center" type="number" min="0" step="any" value="'.$row["quantity"].
                                        '" onchange="updateInventory(this);" '.$readonly.' ></td>
                <td><input type="text" value="'.$row["notes"].'" onchange=updateInventory(this) '.$readonly.' ></td>
                <input type="hidden" value='.$row["id"].'>
                <input type="hidden" id="cat_id" value='.$row["cat_id"].'>
                <input type="hidden" id="quantity_yesterday" value='.($entry_last["quantity"] == "" ? "-" : $entry_last["quantity"]).'>
                <input type="hidden" id="last_date" value="'.($entry_last["date"] == "" ? "" : date("jS M Y", strtotime($entry_last["date"]))).'">
                <input type="hidden" id="quantity_day_before" value='.($entry_second_last["quantity"] == "" ? "-" : $entry_second_last["quantity"]).'>
                <input type="hidden" id="seclast_date" value="'.($entry_second_last["date"] == "" ? "" :date("jS M Y", strtotime($entry_second_last["date"]))).'">
            </tr>';
    }
}

if (isset($_POST["printAll"])) {
    $category_sql =  CategoryTable::get_categories($_POST["date"]);
    $cat_count = 0;
    while ($row = $category_sql->fetch_assoc()) {
        $row_count = 0;
        $rows = 0;
        $item_sql = InventoryTable::get_inventory_by_category($row["id"], $_POST["date"]);
        while ($item_row = $item_sql->fetch_assoc()) {
            $quantity = $item_row["quantity"] == "" ? "-" : $item_row["quantity"];

            if ($_POST["required"] == "true") {
                if (($quantity <= 0 OR $quantity == "-") AND $item_row["notes"] == "") {
                  continue;
                }
            }
            $rows .= '<tr id="column_data" class="row">
                    <td>'.$item_row["name"].'</td>
                    <td>'.$item_row["unit"].'</td>
                    <td>'.$item_row["quantity"].'</td>
                    <td id="td_notes">'.$item_row["notes"].'</td>
                </tr>';
            $row_count++;
        }
        if ($row_count > 0) {
            echo $result = $cat_count > 0 ? '<pagebreak>' : "" ;
            echo '<table class="table_view"><tr class="row"><th colspan="4" class="table_title">'.$row["name"].'</th></tr><tbody class="print_tbody" id="print_tbody">
                    <tr id="print_date" class="row">
                        <th colspan="4">
                            <span id="table_date_span">'.date_format((date_add(date_create($_POST["date"]), date_interval_create_from_date_string("1 day"))), 'D, jS M Y').'</span>
                            <div class="print_table_date">'."created on ".date('jS M Y', strtotime($_POST["date"])).'</div>
                        </th>
                    </tr>
                    <tr id="category_columns" class="heading">
                        <th>Item</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Notes</th>
                    </tr>';
            echo $rows;
            echo '</table>';
            $cat_count++;
        }
    }
}

if (isset($_POST["updateItems"])) {
    echo ItemTable::update_item_details($_POST["itemId"], $_POST["itemName"], $_POST["itemUnit"]);
}

if (isset($_POST["addGroupUser"])) {
    echo UserGroupListTable::add_user($_POST["userId"], $_POST["groupId"]);
}

if (isset($_POST["deleteGroupUser"])) {
    echo UserGroupListTable::remove_user($_POST["userId"], $_POST["groupId"]);
}

if (isset($_POST["getGroupUsers"])) {
    $result = UserGroupListTable::get_users($_POST["groupId"]);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo '<li class="list_li grouped_item" user-id="'.$row["id"].'" group-id="'.$row["group_id"].'"
                    item-name="'.$row["username"].'">' .$row["username"];
        }
    }
}

if (isset($_POST["setNotiStatus"])) {
    echo NotificationStatusTable::set_notification_status($_POST["user_name"], $_POST["notification_id"], $_POST["status"]);
}

if (isset($_POST["setSubNotiStatus"])) {
    echo SubNotificationStatusTable::set_notification_status($_POST["user_name"], $_POST["notification_id"], $_POST["status"], $_POST["parent_noti_id"]);
}

if (isset($_POST["setUserEmail"])) {
    echo UserTable::update_user_email($_POST["userName"], $_POST["email"]);
}
?>
