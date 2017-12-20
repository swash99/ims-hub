<?php
session_start();
require_once "database/category_table.php";
require_once "database/supplier_table.php";
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

if (isset($_POST["edit_s_name"]) AND !empty($_POST["edit_s_name"])) {
    SupplierTable::update_supplier($_POST["edit_s_name"], $_POST["edit_s_id"]);
}
if (isset($_POST["edit_c_name"]) AND !empty($_POST["edit_c_name"])) {
    CategoryTable::update_category($_POST["edit_c_name"], $_POST["edit_c_id"]);
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
            <div class="div_list_title">
                <h4 class="font_roboto">Suppliers</h4>
                <span class="list_sort fa-sort-alpha-asc" id="supplier_sort"></span>
            </div>
            <div class="div_list_category">
                <ul class="category_list" id="supplier_list">
                    <?php $result = SupplierTable::get_suppliers($_SESSION["date"]) ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <li id="<?php echo $row['id']?>" class="list_category_li supplier_li" onclick=supplierSelect(this)>
                            <span><?php echo $row["name"]?></span>
                            <form action="edit_categories.php" method="post">
                                <input type="hidden" name="supplier_delete_id" value="<?php echo $row['id']?>" >
                            </form>
                        </li>
                    <?php endwhile ?>
                </ul>
            </div>
            <input type="hidden" id="supplier_select">
            <input type="hidden" id="delete_cat_ids">
            <div class="option_bar" id="category_add">
                <div class="toolbar_div option" onclick=deleteSupplier()>
                    <span class="icon_small entypo-trash"></span>
                    <span class="icon_small_text">delete</span>
                </div>
                <div class="toolbar_div option" onclick='supplierDrawer("add")'>
                    <button class="button_round entypo-plus"></button>
                </div>
                <div class="toolbar_div option" onclick='supplierDrawer("edit")' >
                    <span class="icon_small fa-edit"></span>
                    <span class="icon_small_text">edit</span>
                </div>
            </div>
            <div class="category_add_drawer" id="add_supplier">
                <input class="category_input" type="text" name="category" id="supplier_name" placeholder="Supplier Name">
                <button id="supplier_add_button" class="button" onclick=addSupplierButton(this)>Add</button>
                <button class="button_cancel" onclick=closeDrawer(this)>close</button>
            </div>
        </div>
        <div class="div_category">
            <div class="div_list_title">
                <h4 class="font_roboto">Categories</h4>
                <span class="list_sort fa-sort-alpha-asc" id="cat_sort"></span>
            </div>
            <div class="div_list_category">
                <ul class="category_list" id="category_list">
                </ul>
            </div>
            <input type="hidden" id="category_select">
             <div class="option_bar" id="category_add">
                <div class="toolbar_div option" onclick=deleteCategory()>
                    <span class="icon_small entypo-trash"></span>
                    <span class="icon_small_text">delete</span>
                </div>
                <div class="toolbar_div option" onclick='categoryDrawer("add")'>
                    <button class="button_round entypo-plus"></button>
                </div>
                <div class="toolbar_div option" onclick='categoryDrawer("edit")' >
                    <span class="icon_small fa-edit"></span>
                    <span class="icon_small_text">edit</span>
                </div>
            </div>
            <div class="category_add_drawer" id="add_category">
                <input class="category_input" type="text" name="category" id="category_name" placeholder="Category Name">
                <button id="category_add_button" class="button" onclick=addCategoryButton(this)>Add</button>
                <button class="button_cancel" onclick=closeDrawer(this)>close</button>
            </div>
        </div>
        <div class="list_container" id="list_container">
            <div class="div_item_list">
                <div class="div_list_title">
                    <h4 class="font_roboto">Categorized Items</h4>
                    <span class="list_sort fa-sort-alpha-asc" id="cat_item_sort"></span>
                </div>
                <div id="div" class="div_list">
                    <ul class="category_list" name="" id="categorized_list" ></ul>
                </div>
            </div>
            <div class="div_item_list">
                <h4 class="font_roboto">Uncategorized Items</h4>
                <div class="div_list">
                    <ul class="category_list" name="select_uncat" id="uncategorized_list" >
                    <?php $result = ItemTable::get_uncategorized_items($_SESSION["date"]); ?>
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
    <form action="edit_categories.php" method="post" id="supplier_form">
        <input type="hidden" name="edit_s_name" id="edit_s_name">
        <input type="hidden" name="edit_s_id" id="edit_s_id">
    </form>
    <form action="edit_categories.php" method="post" id="category_form">
        <input type="hidden" name="edit_c_name" id="edit_c_name">
        <input type="hidden" name="edit_c_id" id="edit_c_id">
    </form>
    <input type="hidden" id="session_date" value="<?php echo $_SESSION["date"] ?>">
</body>
</html>

<script type="text/javascript" src="jq/jquery-3.2.1.min.js"></script>
<script src="jq/jquery-ui.min.js"></script>
<script src="https://cdn.rawgit.com/alertifyjs/alertify.js/v1.0.10/dist/js/alertify.js"></script>
<script src="touch_punch.js"></script>
<script>
    function supplierSelect(obj) {
        var supplierId = $(obj).attr("id");
        var date = $("#session_date").val() ;
        document.getElementById("supplier_select").value = obj.children[0].innerHTML;

        $.post("jq_ajax.php", {getCategories: "", supplierId: supplierId, date: date}, function(data,status){
            $("#category_list").html(data);
            $(".category_li:first").each(function() {
                categorySelect($(this)[0]);
                $(this).addClass("active");
            });
            $("#div").html("");
        });
    }

    function categorySelect(obj) {
        var categoryId = $(obj).find("#cat_id").val();
        var date = $("#session_date").val() ;
        document.getElementById("category_select").value = obj.children[0].innerHTML;

        $.post("jq_ajax.php", {getCategorizedItems: "", categoryId: categoryId, date: date}, function(data,status){
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
                    var categoryId = $(obj).find("#cat_id").val();
                    $(this).children(".selected").each(function(){
                        $.post("jq_ajax.php", {UpdateItemsCategory: "", itemId: $(this).attr("id"), categoryId: categoryId});
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

    function supplierDrawer(type) {
        $("#add_supplier").slideToggle(150, "swing");
        switch (type) {
            case 'add':
                $("#supplier_name").val("").focus();
                $("#supplier_add_button").html("Add");
                break;
            case 'edit':
                $("#supplier_name").val($(".supplier_li.active").children("span").html()).focus();
                $("#supplier_add_button").html("Save");
        }
    }

    function categoryDrawer(type) {
        $("#add_category").slideToggle(150, "swing");
        switch (type) {
            case 'add':
                $("#category_name").val("").focus();
                $("#category_add_button").html("Add");
                break;
            case 'edit':
                $("#category_name").val($(".category_li.active").children("span").html()).focus();
                $("#category_add_button").html("Save");
        }
    }

    function closeDrawer(obj) {
        $(obj).parent().slideToggle(150, "swing");
    }

    function addSupplierButton(obj) {
        switch ($(obj).html()) {
            case 'Add':
                addSupplier();
                break;
            case 'Save':
                editSupplier();
        }
    }

    function addCategoryButton(obj) {
        switch ($(obj).html()) {
            case 'Add':
                addCategory();
                break;
            case 'Save':
                editCategory();
        }
    }

    function addSupplier() {
        supplierName = $("#supplier_name").val();
        date = $("#session_date").val();
        if (supplierName == "") {
            return false;
        }
        $.post("jq_ajax.php", {addSupplier: "", supplierName: supplierName, date: date}, function(data, success) {
            $("#supplier_name").val("");
            if (data) {
                alertify
                    .delay(1000)
                    .success("New Supplier Added");
                updateSupplierOrder();
                $.post("jq_ajax.php", {getSuppliers: "", date: date}, function(data) {
                    $("#supplier_list").html(data);
                });
            } else {
                alertify
                    .delay(2500)
                    .log('"'+supplierName+'" already exists');
            }
        });
    }

    function addCategory() {
        supplierId = $("#supplier_list .active").attr("id");
        categoryName = $("#category_name").val();
        date = $("#session_date").val();
        if (categoryName == "") {
            return false;
        }
        $.post("jq_ajax.php", {addCategory: "", categoryName: categoryName, supplierId: supplierId, date: date}, function(data) {
            $("#category_name").val("");
            if (data) {
                alertify
                    .delay(2000)
                    .success("New Category Added");
                updateCategoryOrder();
                $.post("jq_ajax.php",  {getCategories: "", supplierId: supplierId, date: date}, function(data) {
                    $("#category_list").html(data);
                });
            } else {
                alertify
                    .delay(2500)
                    .log('"'+categoryName+'" already exists');
            }
        });
    }

    function editSupplier() {
        $("#edit_s_name").val($("#supplier_name").val());
        $("#edit_s_id").val($(".supplier_li.active").attr("id"));
        $("#supplier_form").submit();
    }

    function editCategory() {
        $("#edit_c_name").val($("#category_name").val());
        $("#edit_c_id").val($(".category_li.active").attr("id"));
        $("#category_form").submit();
    }

    function updateSupplierOrder() {
         var ids = $(".supplier_li")
                    .map(function() {
                        return this.id;
                    }).get();
        $.post("jq_ajax.php", {UpdateSupplierOrder: "", supplierIds: ids});
    }

    function updateCategoryOrder() {
        var ids = $(".category_li")
                    .map(function() {
                        return this.id;
                    }).get();
        $.post("jq_ajax.php", {UpdateCategoryOrder: "", categoryIds: ids});
    }

    function deleteSupplier() {
        alertify.confirm("Delete Supplier '"+$("#supplier_list .active").children("span").html()+"' ?", function() {
            var supplierId = $("#supplier_list .active").attr("id");
            var date = $("#session_date").val();
            var categoryIds = $(".category_li").map(function() {return this.id;}).get();
            $.post("jq_ajax.php", {deleteSupplier: "", supplierId: supplierId, categoryIds: categoryIds}, function() {
                $.post("jq_ajax.php", {getSuppliers: "", date: date}, function(data) {
                    $("#supplier_list").html(data);
                    $("#categorized_list").html("");
                    $("#uncategorized_list").html("");
                    $(".supplier_li:first").each(function() {
                       supplierSelect($(this)[0]);
                       $(this).addClass("active");
                    });
                });
                $.post("jq_ajax.php", {getUncategorizedItems: ""}, function(data) {
                    $("#uncategorized_list").html(data);
                });
            });
        });
    }

    function deleteCategory() {
        alertify.confirm("Delete Category '"+$("#category_list .active").children("span").html()+"' ?", function() {
            var supplierId = $("#supplier_list .active").attr("id");
            var categoryId = $("#category_list .active").attr("id");
            var date = $("#session_date").val();
            $.post("jq_ajax.php", {deleteCategory: "", categoryId: categoryId}, function() {
                $.post("jq_ajax.php",  {getCategories: "", supplierId: supplierId, date: date}, function(data) {
                    $("#category_list").html(data);
                    $("#categorized_list").html("");
                    $(".category_li:first").each(function() {
                       categorySelect($(this)[0]);
                       $(this).addClass("active");
                    });
                });
                $.post("jq_ajax.php", {getUncategorizedItems: ""}, function(data) {
                    $("#uncategorized_list").html(data);
                });
            });
        });
    }
    $(document).ready(function() {
        $(".supplier_li:first").each(function() {
           supplierSelect($(this)[0]);
           $(this).addClass("active");
        });

        $(document).on("click", ".supplier_li", function() {
            $(".supplier_li").removeClass("active");
            $(this).addClass("active");
        });

        $(document).on("click", ".category_li", function() {
            $(".category_li").removeClass("active");
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
                    $.post("jq_ajax.php", {UpdateItemsCategory: "", itemId: $(this).attr("id"), categoryId: ""});
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
                updateCategoryOrder();
            }
        });

        $("#supplier_list").sortable({
            revert: 150,
            containment: "#supplier_list",
            start: function(event, ui) {
                ui.item.addClass("category_drag");
            },
            stop: function (event, ui) {
                ui.item.removeClass("category_drag");
            },
            update: function(event, ui) {
                updateSupplierOrder();
            }
        });

        $("#uncategorized_list").on('click', 'li', function() {
            $(this).toggleClass("selected");
            $("#categorized_list li").removeClass("selected");
        });

        $("#supplier_sort").click(function() {
            alertify.confirm("Sort Suppliers alphabetically?", function() {
                $(".supplier_li").each(function() {
                    var item = $(this);
                    $(".supplier_li").each(function() {
                        if (item.find("span").html().toLowerCase() > $(this).find("span").html().toLowerCase()) {
                            $(this).insertBefore(item);
                        }
                    });
                });
                updateSupplierOrder();
            });
        });

        $("#cat_sort").click(function() {
            alertify.confirm("Sort Categories alphabetically?", function() {
                $(".category_li").each(function() {
                    var item = $(this);
                    $(".category_li").each(function() {
                        if (item.find("span").html().toLowerCase() > $(this).find("span").html().toLowerCase()) {
                            $(this).insertBefore(item);
                        }
                    });
                });
                updateCategoryOrder();
            });
        });

        $("#cat_item_sort").click(function() {
            alertify.confirm("Sort Items alphabetically?", function() {
                $("#categorized_list").find(".list_li").each(function() {
                    var item = $(this);
                    $("#categorized_list").find(".list_li").each(function() {
                        if (item.html().toLowerCase() > $(this).html().toLowerCase()) {
                            $(this).insertBefore(item);
                        }
                    });
                });
                var ids = $("#categorized_list").find(".list_li")
                    .map(function() {
                        return this.id;
                    }).get();
                $.post("jq_ajax.php", {UpdateItemOrder: "", itemIds: ids});
            });
        });
    });
</script>
