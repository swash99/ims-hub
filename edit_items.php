<?php
session_start();
require_once "database/item_table.php";

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION["userrole"] != "admin") {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION["last_activity"]) && $_SESSION["last_activity"] + $_SESSION["time_out"] * 60 < time()) {
    session_unset();
    session_destroy();
?>
    <script>
        window.parent.location.href = window.parent.location.href;
    </script>
<?php
exit();
}
$_SESSION["last_activity"] = time();

if (isset($_POST["checkbox"])) {
    ItemTable::delete_multiple_items($_POST["checkbox"], $_SESSION["date"]);
}
$item_table = ItemTable::get_items_categories($_SESSION["date"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Time Slots</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="main_iframe">
        <div id="add_div_main" class="none font_open_sans">
            <div id="add_div" class="add_div">
                <div>
                    <h4>Add New Item</h4>
                    <div class="inline">
                        <label for="new_item_name">Name</label>
                        <input class="userinput" type="text" id="new_item_name" placeholder="Required" required autofocus>
                    </div>
                    <div class="inline">
                        <label for="new_item_unit">Unit</label>
                        <input class="userinput" type="text" id="new_item_unit" placeholder="Required" required>
                    </div>
                    <div class="block" id="item_add_div" >
                        <input type="submit" value="Add Item" class="button button_add_drawer" id="item_add_button">
                    </div>
                </div>
            </div>
            <button id="drawer_tag_item" class="drawer_tag_open">Close</button>
        </div>

        <div class="div_table font_roboto" id="items_div_table">
            <div id="div_print_table">
                <table class="table_view" id="item_table_view" border="1px" >
                    <tr class="table_option_bar">
                        <th colspan="2" id="button_th">
                            <button class="button_flat entypo-plus" id="add_item_button">Add</button>
                            <div class="divider"></div>
                            <button class="button_flat entypo-trash" id="delete_item">Delete</button>
                        </th>
                        <th >
                            <input class="search_bar" id="search_bar" type="search" placeholder="search" oninput=searchBar(this)>
                        </th>
                    </tr>
                    <tr class="tr_confirm">
                        <td class="td_checkbox">
                            <div class="checkbox">
                                <input type="checkbox" class="item_checkbox" id="select_all">
                                <span class="checkbox_style"></span>
                            </div>
                        </td>
                        <td id="td_cancel">Cancel
                        <td id="td_done">Done</th>
                    </tr>
                    <tr>
                        <th id="buffer"></th>
                        <th>Item</th>
                        <th>Unit</th>
                    </tr>
                    <tbody id="item_tbody">
                    <?php  $current_category = 1;?>
                    <?php mysqli_data_seek($item_table, 0); ?>
                    <?php  while($row = $item_table->fetch_assoc()): ?>
                    <?php  if ($row["category_name"] != $current_category AND $row["category_name"] != null): ?>
                            <?php $current_category = $row["category_name"];?>
                            <tr class="item_category_tr">
                                <td id="category" colspan="3" class="table_heading"><?php echo $row["category_name"]?><span class="arrow_down float_right collapse_arrow"></span></td>
                            </tr>
                    <?php elseif ($row["category_name"] != $current_category AND $row["category_name"] == null): ?>
                    <?php  $current_category = $row["category_name"];?>
                            <tr class="item_category_tr">
                                <td id="category" colspan="3" class="table_heading">Uncategorized Items<span class="arrow_down float_right collapse_arrow"></span></td>
                            </tr>
                    <?php endif ?>
                        <tr>
                            <input type="hidden" class="item_id" name="item_id" value="<?php echo $row["id"]?>">
                            <td class="td_checkbox">
                                <div class="checkbox">
                                    <input type="checkbox" class="item_checkbox" name="checkbox[]" value="<?php echo $row["id"]?>" form="checkbox_form">
                                    <span class="checkbox_style"></span>
                                </div>
                            </td>
                            <td><input type="text" name="item_name" value="<?php echo $row["name"]?>" onchange=updateItem(this) class="align_center item_name"></td>
                            <td><input type="text" name="item_unit" value="<?php echo $row["unit"]?>" onchange=updateItem(this) class="align_center"></td>
                        </tr>
                    <?php endwhile ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <form action="edit_items.php" method="post" id="checkbox_form"></form>
    <form action="edit_items.php" method="post">
        <input type="hidden" name="tab_name" id="tab_name">
    </form>
 </body>
 </html>
<script type="text/javascript" src="jq/jquery-3.2.1.min.js"></script>
<script src="https://cdn.rawgit.com/alertifyjs/alertify.js/v1.0.10/dist/js/alertify.js"></script>
<script>
    function  getItems() {
        $.post("jq_ajax.php", {getItems: ""}, function(data, status) {
            document.getElementById("item_tbody").innerHTML = data;
        });
    }

    function updateItem(obj) {
        var row =document.getElementById("item_table_view").rows[obj.parentNode.parentNode.rowIndex];
        var itemName = row.children[2].children[0].value;
        var itemUnit  = row.children[3].children[0].value;
        var itemId  = row.children[0].value;
        $.post("jq_ajax.php", {updateItems: "", itemName: itemName, itemUnit: itemUnit, itemId: itemId});
    }

    function searchBar(obj) {
        var searchText = new RegExp(obj.value, "i");
        if (obj.value != "") {
            $("#item_tbody").children().hide();
            $(".item_name").each(function() {
                var val = $(this).val();
                if (val.search(searchText) > -1) {
                    $(this).parent().parent().show();
                }
            });
        } else {
            $("#item_tbody").children().show();
        }
    }

    $(document).ready(function() {

        $(document).on("click", ".item_category_tr", function() {
            $(this).nextUntil(".item_category_tr").toggle();
            if ($(this).find("span").hasClass("up")) {
                $(this).find("span").removeClass("up").css("transform", "rotate(45deg)");
            } else {
                $(this).find("span").addClass("up").css("transform", "rotate(225deg)")
            }
        });

        $("#add_item_button").click(function() {
            if ($(this).html() == "Item List") {
                $("#item_list_div").css({"flex": "1","max-width": "initial"});
            } else {
                $("#add_div").slideDown(180, "linear", function() {
                    $("#drawer_tag_item").fadeIn(300, "linear");
                    $("#drawer_tag_item").css("display", "inline-block");
                });
            }
        });

        $("#drawer_tag_item").click(function() {
            $("#drawer_tag_item").fadeOut(100, "linear");
            $("#add_div").slideUp(180, "linear");
        });

        $("#item_add_button").click(function() {
            var itemName = $("#new_item_name").val();
            var itemUnit = $("#new_item_unit").val();

            if (itemName != "" && itemUnit != "") {
                $.post("jq_ajax.php", {addItem: "", itemName: itemName, itemUnit: itemUnit}, function(data, status) {
                    if (data == "item added") {
                        alertify
                            .delay(2500)
                            .success("Item added successfully");
                        getItems();
                        $("userinput").trigger("reset");
                    } else if (data == "item exists") {
                        alertify
                            .delay(2500)
                            .log("Item name already exists");
                    } else {
                        alertify
                            .delay(2500)
                            .error("Process failed. Try again");
                    }
                });
            } else {
                alertify
                    .delay(3000)
                    .error("Fill all required fields");
            }
        });

        $(document).on("click", "#delete_item", function() {
            $(".tr_confirm").css("display", "table");
            $(".checkbox").css("display", "block");
        });

        $("#td_done").click(function() {
            document.getElementById("checkbox_form").submit();
        });

        $("#td_cancel").click(function() {
            $(".tr_confirm").css("display", "none");
            $(".checkbox").css("display", "none");
        });

        $("#item_list_cancel").click(function() {
            $("#item_list_div").css({"flex": "0","max-width": "0"});
        });

        $("#select_all").change(function() {
            $("input[type='checkbox']").prop("checked", $(this).prop("checked"));
        });

    });
</script>
