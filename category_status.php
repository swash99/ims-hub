<?php
session_start();
require_once "database/category_table.php";
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
        <div class="div_category " id="home_list">
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
            <ul class="category_list home_category_list font_roboto" >
            <?php $result = CategoryTable::get_categories($_SESSION["date"]);
                 while ($row = $result->fetch_assoc()): ?>
                 <li class="list_category_li home_category_list_li">
                    <div class="list_li_div_left">
                        <span id="category_name"><?php echo $row["name"]; ?></span>
                    </div>
                    <div class="list_li_div_right">
                        <span class="count_span_filled" id="<?php echo $row['name'].'_count' ?>">
                        <?php echo ItemTable::get_updated_items_count($row['id'], $_SESSION["date"]) ?></span>
                        <span class="count_span_total"><?php echo ItemTable::get_total_items($row['id'], $_SESSION['date']) ?></span>
                        <input type="hidden" id="category_id" name="category_id" value="<?php echo $row['id'] ?>">
                    </div>
                 </li>
            <?php endwhile ?>
            </ul>
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
     <?php $page = "home";
           include_once "new_nav.php"; ?>
</body>
</html>

<script type="text/javascript" src="//code.jquery.com/jquery-2.2.0.min.js"></script>
<script src="https://cdn.rawgit.com/alertifyjs/alertify.js/v1.0.10/dist/js/alertify.js"></script>
<script
      src="http://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
      integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
      crossorigin="anonymous"></script>
<?php if ($_SESSION["date"] <= date('Y-m-d', strtotime("-".$_SESSION["history_limit"]))): ?>
    <script> $("input:not(#search_bar)").prop("readonly", true); </script>
<?php endif ?>
<script>
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
                    updateCount(row.children[6].value);
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
            getInventory($(".list_category_li.active").find("#category_id").val());
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

    function createInfogram(yesterday, dayBefore) {
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

        divTopLeft.innerHTML = "yesterday";
        divTopRight.innerHTML = "day before";
        divBottomLeft.innerHTML = yesterday;
        divBottomRight.innerHTML = dayBefore;

        divLeft.appendChild(divTopLeft);
        divLeft.appendChild(divBottomLeft);
        divRight.appendChild(divTopRight);
        divRight.appendChild(divBottomRight);

        divCon.appendChild(divLeft);
        divCon.appendChild(divRight);
        divMain.appendChild(divCon);
        return divMain;
    }

    $(document).ready(function() {
        $(".list_category_li:first").each(function() {
            getInventory($(this).find("#category_id").val());
            $(this).addClass("active");
            $("#name").html($(this).find("#category_name").html());
        });

        $(".list_category_li").click(function() {
            getInventory($(this).find("#category_id").val(), function() {
                $(".search_bar").val("");
            });
            $(".list_category_li").removeClass("active");
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
            var yesterday = $(this).parents("tr").find("#quantity_yesterday").val();
            var dayBefore = $(this).parents("tr").find("#quantity_day_before").val();
            $(this).parent().append(createInfogram(yesterday, dayBefore));
            $(this).parent().find(".info_div_main").css("opacity");
            $(this).parent().find(".info_div_main").addClass("visible");
        });

        $(document).on("focusout", ".quantity_input", function() {
            $(this).parent().find(".info_div_main").removeClass("visible");
            $(this).parent().find(".info_div_main").on("transitionend", function() {
                $(this).remove();
            });
        });

        $("input[type=number]").on("keypress" , function(event) {
            if ((event.which < 46 || event.which > 57) && (event.which != 8 &&
                event.which != 0 && event.which != 13)) {
                event.preventDefault();
            }
        });

        $("#popup_close").click(function() {
            $("#sales_popup").css("display", "none");
            $("#total_input").val($("#right #amount").html());
        });
    });
</script>
