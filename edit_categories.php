<?php
session_start();
require_once "database/category_table.php";
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

if (isset($_POST["new_name"]) AND !empty($_POST["new_name"])) {
    try {
        if (!CategoryTable::add_category($_POST["new_name"], $_SESSION["date"])) {
            echo '<div class="error">Category already exists</div>';
        }
    } catch (Exception $e) {
        echo '<div class="error">'.$e->getMessage().'</div>';
    }
}
if(isset($_POST["delete_id"])) {
    CategoryTable::remove_category($_POST["delete_id"], $_SESSION["date"]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="category_main font_open_sans">
        <div class="div_category">
            <h4 class="font_roboto">Suppliers</h4>
            <div class="div_list_category">
            <ul class="category_list" id="category_list">
                <?php $result = CategoryTable::get_categories($_SESSION["date"]) ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li id="<?php echo $row['id']?>" class="list_category_li" onclick=categorySelect(this)>
                        <span><?php echo $row["name"]?></span>
                        <form action="edit_categories.php" method="post">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']?>" >
                        </form>
                    </li>
                <?php endwhile ?>
            </ul>
            </div>
            <input type="hidden" id="category_select">
            <div class="category_add" id="category_add">
                <button class="button_flat entypo-trash float_left" onclick=deleteCategory()>delete</button>
                <button class="button_flat entypo-plus float_right" onclick=slideDrawer()>Add</button>
            </div>
            <div class="category_add_drawer">
                <input class="category_input" type="text" name="category" id="category_name" placeholder="Supplier Name">
                <button name="add_button" class="button" onclick=addCategory()>Add</button>
                <button class="button_cancel"  onclick=slideDrawer()>cancel</button>
            </div>
        </div>

        <div class="list_container" id="list_container">
            <div class="div_item_list">
                <h4 class="font_roboto">Categorized Items</h4>
                <div id="div" class="div_list">
                    <ul class="category_list" name="" id="categorized_list" ></ul>
                </div>
            </div>
            <div class="div_item_list">
                <h4 class="font_roboto">Uncategorized Items</h4>
                <div class="div_list">
                    <ul class="category_list" name="select_uncat" id="uncategorized_list" >
                    <?php $result = ItemTable::get_uncategorized_items(); ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <li class="list_li" id="<?php echo $row['id'] ?>" item-name="<?php echo $row['name'] ?>"><?php echo $row["name"];?></li>
                    <?php endwhile ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <form action="edit_categories.php" method="post" id="add_form">
        <input type="hidden" name="new_name" id="new_name">
    </form>
</body>
</html>

<script type="text/javascript" src="//code.jquery.com/jquery-2.2.0.min.js"></script>
<script src="https://cdn.rawgit.com/alertifyjs/alertify.js/v1.0.10/dist/js/alertify.js"></script>
<script
      src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"
      integrity="sha256-xNjb53/rY+WmG+4L6tTl9m6PpqknWZvRt0rO1SRnJzw="
      crossorigin="anonymous"></script>
<script src="touch_punch.js"></script>
<script>
    function categorySelect(obj) {
        var categoryName = obj.children[0].innerHTML;
        document.getElementById("category_select").value = obj.children[0].innerHTML;

        $.post("jq_ajax.php", {getCategorizedItems: categoryName}, function(data,status){
            document.getElementById("div").innerHTML = data;
            $("#categorized_list").sortable({
                delay: 50,
                revert: 120,
                containment: $("#categorized_list").parent().parent().parent(),
                connectWith: "#uncategorized_list",
                helper: function (event, ui) {
                    var helper = $('<li/>');
                    if (!ui.hasClass('selected')) {
                        ui.addClass('selected').siblings().removeClass('selected');
                    }
                    $("#uncategorized_list li").removeClass("selected");
                    var elements = ui.parent().children('.selected').clone();
                    ui.data('multidrag', elements).siblings('.selected').remove();
                    return helper.append(elements);
                },
                stop: function(event, ui) {
                    ui.item.after(ui.item.data('multidrag')).remove();
                },
                receive: function(event, ui) {
                    var categoryName = document.getElementById("category_select").value;
                    $(this).children(".selected").each(function(){
                        $.post("jq_ajax.php", {UpdateItemsCategory: "", itemName: $(this).attr("item-name"), categoryName: categoryName});
                    });
                },
                update: function(event, ui) {
                    ui.item.after(ui.item.data('multidrag')).remove();
                    var itemIds = $(this).sortable('toArray');
                    $.post("jq_ajax.php", {UpdateItemOrder: "", itemIds: itemIds});
                }
            }).on("click", "li", function () {
                $(this).toggleClass("selected");
                $("#uncategorized_list li").removeClass("selected");
            });
        });
    }

    function slideDrawer() {
        $(".category_add_drawer").slideToggle(180, "linear");
    }

    function deleteCategory() {
        alertify.confirm("Delete Supplier '"+$(".active").children("span").html()+"' ?", function() {
            $(".active").children("form").submit();
        });
    }

    function addCategory() {
        var ids = $(".list_category_li")
                    .map(function() {
                        return this.id;
                    }).get();
        $.post("jq_ajax.php", {UpdateCategoryOrder: "", categoryIds: ids});
        $("#new_name").val($("#category_name").val());
        $("#add_form").submit();
    }

    $(document).ready(function() {

        $(".list_category_li:first").each(function() {
           categorySelect($(this)[0]);
           $(this).addClass("active");
        });

        $(".list_category_li").click(function() {
            $(".list_category_li").removeClass("active");
            $(this).addClass("active");
        });
        
        $("#uncategorized_list").sortable({
            delay: 50,
            revert: 120,
            containment: $("#uncategorized_list").parent().parent().parent(),
            connectWith: "#categorized_list",
            helper: function (event, ui) {
                var helper = $('<li/>');
                if (!ui.hasClass('selected')) {
                    ui.addClass('selected').siblings().removeClass('selected');
                }
                $("#categorized_list li").removeClass("selected");
                var elements = ui.parent().children('.selected').clone();
                ui.data('multidrag', elements).siblings('.selected').remove();
                return helper.append(elements);
            },
            stop: function(event, ui) {
                ui.item.after(ui.item.data('multidrag')).remove();
            },
            update: function(event, ui) {
                ui.item.after(ui.item.data('multidrag')).remove();
            },
            receive: function(event, ui) {
                $(this).children(".selected").each(function() {
                    $.post("jq_ajax.php", {UpdateItemsCategory: "", itemName: $(this).attr("item-name"), categoryName: null});
                });
            }
        });

        $("#category_list").sortable({
            revert: 150,
            containment: "#category_list",
            start: function(event, ui) {
                ui.item.addClass("category_drag");
            },
            stop: function (event, ui) {
                ui.item.removeClass("category_drag");
            },
            update: function(event, ui) {
                var ids = $(this).sortable("toArray");
                $.post("jq_ajax.php", {UpdateCategoryOrder: "", categoryIds: ids});
            }
        });

        $("#uncategorized_list").on('click', 'li', function() {
            $(this).toggleClass("selected");
            $("#categorized_list li").removeClass("selected");
        });
    });
</script>
