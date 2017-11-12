<?php
session_start();
require_once "database/category_table.php";
require_once "database/supplier_table.php";
require_once "database/item_table.php";

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}
if(isset($_POST["new_date"])) {
    $_SESSION["date"] = $_POST["new_date"];
    $_SESSION["date_check"] = "checked";
}
if (isset($_SESSION["last_activity"]) && $_SESSION["last_activity"] + $_SESSION["time_out"] * 60 < time()) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
$_SESSION["last_activity"] = time();
$readonly = $_SESSION["date"] <= date('Y-m-d', strtotime("-".$_SESSION["history_limit"])) ? "readonly" : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta id="vp" name="viewport" content="width=device-width">
    <script>
        if (screen.width < 700)
        {
            var vp = document.getElementById('vp');
            vp.setAttribute('content','width=780');
        }
    </script>
    <title>Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="overflow_hidden">
    <div class="main">
        <div class="sidenav " id="home_list">
            <?php $current_date = strtotime($_SESSION["date"]); ?>
            <div class="time_div font_open_sans">
                <div class="time_div_container">
                    <span class="date_span fa-calendar" aria-hidden="true"><?php echo date("d", $current_date); ?></span>
                </div>
                <div class="time_div_container">
                    <span class="year_span"><?php echo date("Y", $current_date); ?></span>
                    <span class="month_span"><?php echo date("F", $current_date); ?></span>
                </div>
                    <span class="day_span"><?php echo date("l", $current_date); ?></span>
                <div id="div_cal"></div>
                <form action="category_status.php" method="post" id="cal_form">
                    <input type="hidden" id="cal_date" name="new_date" value="<?php echo $_SESSION["date"] ?>">
                </form>
            </div>
            <div class="div_actual_sales">
                <div id="left">
                    <span >Suppliers</span>
                </div>
            </div>
            <div class="div_list_con">
                <div id="div_invoice_restaurants">
                    <ul id="supplier_list">
                    <?php $result = SupplierTable::get_suppliers($_SESSION["date"]);
                         while ($row = $result->fetch_assoc()): ?>
                         <li class="list_category_li supplier_list_li">
                            <div class="list_li_div_left">
                                <span id="category_name"><?php echo $row["name"]; ?></span>
                            </div>
                            <input type="hidden" id="supplier_id" value="<?php echo $row["id"]; ?>">
                         </li>
                    <?php endwhile ?>
                    </ul>
                </div>
                <div id="div_invoice_list">
                    <div class="div_list_visible">
                        <div class="heading" ><h4>Categories</h4></div>
                        <ul class="side_nav invoice_list" id="category_list">
                        </ul>
                        <button class="button_flat" id="list_back">Back</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="inventory_div">
            <div class="inventory_toolbar font_roboto">
                <div class="toolbar_div" id="div_switch">
                   <label class="switch float_left">
                       <input class="switch-input" type="checkbox" onclick=checkEmpty() />
                       <span class="switch-label" data-on="incomplete" data-off="All"></span>
                       <span class="switch-handle"></span>
                   </label>
                </div>
                <div class="toolbar_div">
                    <h4 id="name"></h4>
                </div>
                <div class="toolbar_div" id="div_pp">
                    <a href="print_preview.php" class="fa-print pp_button">Print Preview</a>
                </div>
                <div class="toolbar_div search_div">
                    <input class="search_bar" id="search_bar" type="search" placeholder="search" oninput=searchBar(this)>
                </div>
            </div>
            <div class="inventory_table">
                <table class="table_view" id="upinven_table">
                    <tr  class="font_roboto">
                        <th id="heading_item">Item</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Notes</th>
                    </tr>
                    <tbody class="font_roboto" id="item_tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="div_popup_back" id="date_check_popup">
        <div id="date_check_holder"></div>
    </div>

    <form action="category_status.php" method="post" id="sales_form">
        <input type="hidden" name="actual_sale" id="actual_sale">
    </form>

    <input type="hidden" id="session_date" value="<?php echo $_SESSION["date"]; ?>">
    <input type="hidden" id="date_check" value="<?php echo isset($_SESSION["date_check"]); ?>">

<script type="text/javascript" src="jq/jquery-3.2.1.min.js"></script>
 <?php $page = "home";
       include_once "new_nav.php"; ?>
</body>
</html>
<script src="jq/jquery-ui.min.js"></script>
<script src="https://cdn.rawgit.com/alertifyjs/alertify.js/v1.0.10/dist/js/alertify.js"></script>
<?php if ($_SESSION["date"] <= date('Y-m-d', strtotime("-".$_SESSION["history_limit"]))): ?>
    <script> $("input:not(#search_bar)").prop("readonly", true); </script>
<?php endif ?>
<script>
    function getCategories(obj) {
        var supplierId = $(obj).find("#supplier_id").val();
        var date = document.getElementById("session_date").value;
        $.post("jq_ajax.php", {getSupplierCategory: "", supplierId: supplierId, date: date}, function(data) {
            $(".div_actual_sales").find("#left span").html($(".supplier_list_li.active").find("#category_name").html());
            $("#category_list").html(data);
            $("#div_invoice_list").css("display", "flex");
            setTimeout(function() {
                $("#div_invoice_list").addClass("list_visible");
            }, 10);
            $(".div_list_visible").on("transitionend", function() {
                $(".category_list_li:first").each(function() {
                    getInventory($(this).find("#category_id").val(), function() {
                        $(".search_bar").val("");
                    });
                    $(this).addClass("active");
                    $("#name").html($(this).find("#category_name").html());
                });
                $(this).unbind("transitionend");
            });
        });
    }
    function getInventory(categoryId , callBack) {
        var date = document.getElementById("session_date").value;
        if ($("#item_tbody").html() == "") {
            $.post("jq_ajax.php", {getInventory: "", categoryId: categoryId, date: date}, function(data, status) {
                document.getElementById("item_tbody").innerHTML = data;
                $("#item_tbody").children().hide();
                $("#item_tbody").children().each(function() {
                    if ($(this).find("#cat_id").val() == categoryId) {
                        $(this).show();
                    }
                });
            });
        } else {
            $("#item_tbody").children().hide();
            $("#item_tbody").children().each(function() {
                if ($(this).find("#cat_id").val() == categoryId) {
                    $(this).show();
                }
            });
        }
            typeof callBack === "function" ? callBack() : "";
            if ($(".switch-input").prop("checked")) { checkEmpty(); }
    }

    function updateInventory(obj) {
        var row = document.getElementById("upinven_table").rows[obj.parentNode.parentNode.rowIndex];
        var itemQuantity = row.cells[2].children[0].value;
        if (itemQuantity < 0) {
            row.cells[3].children[0].value = "";
        } else {
            var itemName = row.children[0].innerHTML;
            var itemDate = document.getElementById("session_date").value;
            var itemId = row.children[4].value;
            itemQuantity = itemQuantity == "" ? 'NULL' : itemQuantity;
            var itemNote = row.cells[3].children[0].value;
            $.post("jq_ajax.php", {itemId: itemId, itemDate: itemDate, itemQuantity: itemQuantity, itemNote: itemNote}, function(data, status) {
                if (data) {
                    alertify
                        .delay(2000)
                        .success("Changes Saved");
                    if ($("#name").html() != "search result") {
                        if ($(".switch-input").prop("checked")) { checkEmpty(); }
                    }
                    updateCount(row.children[5].value);
                }
            })
            .fail(function() {
                alertify
                    .maxLogItems(10)
                    .delay(0)
                    .closeLogOnClick(true)
                    .error("Item '"+itemName+"' not saved. Click here to try again", function(event) {
                        updateInventory(obj);
                    });
            });
        }
    }

    function checkEmpty() {
        if ($(".switch-input").prop("checked")) {
            $(".td_quantity").each(function() {
                if ($(this).children().val() >= 0 && $(this).children().val() != "") {
                    $(this).parent().hide();
                }
            });
        } else {
            getInventory($("#category_list .active").find("#category_id").val());
        }
    }

    function updateCount(id) {
        var count = 0;
        var categoryName = "";
        $(".list_category_li").each(function() {
            if ($(this).find("#category_id").val() == id) {
                categoryName = $(this).find("#category_name").html();
            }
        });
        $("#item_tbody #cat_id").each(function() {
            if ($(this).val() == id) {
                if ($(this).parent().find(".td_quantity").children().val() >= "0") {
                    count++;
                }
            }
        });
        document.getElementById(categoryName+"_count").innerHTML = count;
    }

    (function(){
        if ($("#date_check").val() == "") {
            $("#date_check_popup").css("display", "block");
            $(".main").addClass("blur");
            var date_check_holder = document.getElementById("date_check_holder");
            var date_check_title = document.createElement("div");
            var date_check_div = document.createElement("div");
            date_check_title.setAttribute("id", "date_check_title");
            date_check_title.innerHTML = "select date";
            date_check_div.setAttribute("id", "date_check_div");
            date_check_holder.appendChild(date_check_title);
            date_check_holder.appendChild(date_check_div);

            $("#date_check_div").datepicker({
                dateFormat: "yy-mm-dd",
                defaultDate: $("#cal_date").val(),
                dayNamesMin: [ "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" ],
                currentText: "close",
                prevText: "previous",
                onSelect: function(dateText) {
                    $("#cal_date").val(dateText);
                    $("#cal_form").submit();
                }
            });
        }
    })();

    function searchBar(obj) {
        if (obj.value != "") {
            var searchWord = new RegExp(obj.value, "i");
            $("#item_tbody").children().hide();
            $("#item_tbody").find(".item_name").each(function() {
                var val = $(this).html();
                if (val.search(searchWord) > -1) {
                    $(this).parent().show();
                }
            });
            $("#name").html("search result");
        } else {
            getInventory($(".list_category_li.active").find("#category_id").val(), function() {
                $(".search_bar").val("");
                $("#name").html($(".list_category_li.active").find("#category_name").html());
            });
        }
    }

    function createInfogram(last, lastDate, secLast, secLastDate) {
        var divMain = document.createElement("div");
        var divCon = document.createElement("div");
        var divLeft = document.createElement("div");
        var divRight = document.createElement("div");
        var divTopLeft = document.createElement("div");
        var divBottomLeft = document.createElement("div");
        var divTopRight = document.createElement("div");
        var divBottomRight = document.createElement("div");
        divMain.setAttribute("class", "info_div_main");
        divCon.setAttribute("class", "info_div_con");
        divLeft.setAttribute("class", "info_div_left");
        divRight.setAttribute("class", "info_div_right");
        divTopLeft.setAttribute("class", "info_div_top");
        divBottomLeft.setAttribute("class", "info_div_bottom");
        divTopRight.setAttribute("class", "info_div_top");
        divBottomRight.setAttribute("class", "info_div_bottom");

        if (lastDate == "") {
            divTopLeft.innerHTML = "no previous entries";
            divLeft.appendChild(divTopLeft);
            divCon.appendChild(divLeft);
            divMain.appendChild(divCon);

        } else {
            divTopLeft.innerHTML = lastDate;
            divTopRight.innerHTML = secLastDate;
            divBottomLeft.innerHTML = last;
            divBottomRight.innerHTML = secLast;

            divLeft.appendChild(divTopLeft);
            divLeft.appendChild(divBottomLeft);
            divRight.appendChild(divTopRight);
            divRight.appendChild(divBottomRight);

            divCon.appendChild(divLeft);
            divCon.appendChild(divRight);
            divMain.appendChild(divCon);
        }
        return divMain;
    }

    $(document).ready(function() {
        $(".supplier_list_li").click(function() {
            getCategories($(this)[0]);
            $("#supplier_list").children().removeClass("active");
            $(this).addClass("active");
        });

        $(document).on("click", ".category_list_li", function() {
            getInventory($(this).find("#category_id").val(), function() {
                $(".search_bar").val("");
            });
            $("#category_list").children().removeClass("active");
            $(this).addClass("active");
            $("#name").html($(this).find("#category_name").html());
        });

        $(".time_div").click(function() {
            $("#div_cal .ui-datepicker").css("display", "block");
            $("#div_cal").datepicker({
                dateFormat: "yy-mm-dd",
                defaultDate: $("#cal_date").val(),
                dayNamesMin: [ "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" ],
                showButtonPanel: true,
                currentText: "close",
                prevText: "previous",
                onSelect: function(dateText) {
                    $(".ui-datepicker").css("display", "none");
                    $("#cal_date").val(dateText);
                    $("#cal_form").submit();
                }
            });
        });

        $(document).click(function(event) {
            if ($("#date_check").val() != "") {
                if(!$(event.target).closest('.time_div').length && !$(event.target).is("a, span")) {
                    if($('.ui-datepicker').is(":visible")) {
                        $('#div_cal .ui-datepicker').css("display", "none");
                    }
                }
            }
        });

        $(document).on("focus", ".quantity_input", function() {
            var last = $(this).parents("tr").find("#quantity_yesterday").val();
            var lastDate = $(this).parents("tr").find("#last_date").val();
            var secLast = $(this).parents("tr").find("#quantity_day_before").val();
            var secLastDate = $(this).parents("tr").find("#seclast_date").val();
            $(this).parent().append(createInfogram(last, lastDate, secLast, secLastDate));
            $(this).parent().find(".info_div_main").css("opacity");
            $(this).parent().find(".info_div_main").addClass("visible");
        });

        $(document).on("focusout", ".quantity_input", function() {
            $(this).parent().find(".info_div_main").removeClass("visible");
            $(this).parent().find(".info_div_main").on("transitionend", function() {
                $(this).remove();
            });
        });

        $(document).on("keypress", "input[type=number]" , function(event) {
            if ((event.which < 46 || event.which > 57) && (event.which != 8 &&
                event.which != 0 && event.which != 13)) {
                event.preventDefault();
            }
        });

        $("#popup_close").click(function() {
            $("#sales_popup").css("display", "none");
            $("#total_input").val($("#right #amount").html());
        });

        $("#list_back").click(function() {
            $("#div_invoice_list").removeClass("list_visible");
            $("#div_invoice_list").on("transitionend", function() {
                $(".search_bar").val("");
                $("#name").html("");
                $("#item_tbody").children().hide();
                $(".div_actual_sales").find("#left span").html("Suppliers");
                $("#supplier_list").children().removeClass("active");
                $(this).css("display", "none").unbind("transitionend");
            });
        });
    });
</script>
