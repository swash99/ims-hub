<?php
session_start();
require_once "database/invoice_table.php";
require_once "mpdf/vendor/autoload.php";

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
    header("Location: login.php");
    exit();
}
if (isset($_POST["table_data"])) {
    $mpdf = new mPDF("", "A4", 0, 'roboto', 0, 0, 0, 0, 0, 0);
    $stylesheet = file_get_contents("css/pdf_styles.css");
    $mpdf->WriteHtml($stylesheet, 1);
    $mpdf->WriteHtml($_POST["table_data"], 2);
    $mpdf->Output($_POST["table_name"]." - ".$_POST["table_date"].".pdf", "D");
}
$_SESSION["last_activity"] = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="overflow_hidden font_open_sans">
    <div class="main overflow_hidden">
        <div class="sidenav" id="invoice_sidenav">
            <div class="heading" id="heading"><h4>Resturants</h4></div>
            <div class="div_list_con">
                <div id="div_invoice_resturants">
                    <ul class="side_nav" id="invoice_resturants">
                        <li>
                            <a onclick="showInvoiceList(this)">
                                <span class="left" id="Waterloo">Waterloo</span>
                                <?php $count = InvoiceTable::get_unread_count("Waterloo") ?>
                                <?php if ($count > 0): ?>
                                    <span class="right counter"><?php echo $count ?></span>
                                <?php endif ?>
                            </a>
                        </li>
                        <li>
                            <a onclick="showInvoiceList(this)">
                                <span class="left" id="Mississauga">Mississauga</span>
                                <?php $count = InvoiceTable::get_unread_count("Mississauga") ?>
                                <?php if ($count > 0): ?>
                                    <span class="right counter"><?php echo $count ?></span>
                                <?php endif ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div id="div_invoice_list">
                    <div class="div_list_visible">
                        <div class="heading" ><h4>Invoices</h4></div>
                        <ul class="side_nav invoice_list" id="Waterloo_list">
                        <?php $result = InvoiceTable::get_tracked_invoices("Waterloo");
                            while ($row = $result->fetch_assoc()) :
                            $date = date_add(date_create($row["date"]), date_interval_create_from_date_string("1 day")); ?>
                            <li>
                                <a class="invoice_date" onclick="showInvoice(this)">
                                    <div id="left">
                                        <span><?php echo date_format($date, "jS"); ?></span>
                                    </div>
                                    <div id="right">
                                        <span id="top"><?php echo date_format($date, "F"); ?></span>
                                        <span id="bottom"><?php echo date_format($date, "D Y"); ?></span>
                                    </div>
                                    <input type="hidden" id="selected_date" value="<?php echo date_format($date, "D, jS M Y") ?>">
                                    <input type="hidden" id="created_date" value="<?php echo date_format(date_create($row["date"]), "jS M Y") ?>">
                                </a>
                                <input type="hidden" value="<?php echo $row["date"] ?>">
                                <input type="hidden" id="database_name" value="Waterloo">
                                <input type="hidden" id="invoice_id" value="<?php echo $row["id"] ?>">
                            </li>
                        <?php endwhile?>
                        </ul>
                        <ul class="side_nav invoice_list" id="Mississauga_list">
                            <?php $result = InvoiceTable::get_tracked_invoices("Mississauga");
                            while ($row = $result->fetch_assoc()) :
                            $date = date_add(date_create($row["date"]), date_interval_create_from_date_string("1 day")); ?>
                            <li>
                                <a class="invoice_date" onclick="showInvoice(this)">
                                    <div id="left">
                                        <span><?php echo date_format($date, "jS"); ?></span>
                                    </div>
                                    <div id="right">
                                        <span id="top"><?php echo date_format($date, "F"); ?></span>
                                        <span id="bottom"><?php echo date_format($date, "D Y"); ?></span>
                                    </div>
                                    <input type="hidden" id="selected_date" value="<?php echo date_format($date, "D, jS M Y") ?>">
                                    <input type="hidden" id="created_date" value="<?php echo date_format(date_create($row["date"]), "jS M Y") ?>">
                                </a>
                                <input type="hidden" value="<?php echo $row["date"] ?>">
                                <input type="hidden" id="database_name" value="Mississauga">
                                <input type="hidden" id="invoice_id" value="<?php echo $row["id"] ?>">
                            </li>
                        <?php endwhile?>
                        </ul>
                        <button class="button_flat" id="list_back">Back</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="main_top_side">
            <div class="toolbar_print"  id="invoice_toolbar">
                <label class="switch">
                    <input class="switch-input" type="checkbox" onclick=checkRequired() />
                    <span class="switch-label" data-on="Required" data-off="All"></span>
                    <span class="switch-handle"></span>
                </label>
                <div class="divider"></div>
                <div class="toolbar_div">
                    <a id="print_share" class="option" onclick=sendPrint()>Share</a>
                </div>
                <div class="divider"></div>
                <div class="toolbar_div">
                    <a id="print_pdf" class="option" onclick=printPdf()>PDF</a>
                </div>
                <div class="toolbar_div float_right" id="totalcost_div">
                    <span id="label">total cost</span>
                    <span id="cost_span"></span>
                </div>
            </div>

            <div class="div_invoice_table">
                <table class="table_view" id="invoice_table">
                    <tr id="print_date" class="row">
                        <th colspan="7">
                            <span id="table_date_span">click on resturant to see invoice list</span>
                            <div class="print_table_date"></div>
                        </th>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="div_popup_back">
        <div class="div_popup popup_share">
            <div class="popup_titlebar">
                <span>New Message</span>
                <span class="popup_close" id="popup_close"></span>
            </div>
            <iframe id="popup_frame" name="popup_frame" src="" frameborder="0"></iframe>
        </div>
    </div>

    <form action="compose_messages.php" method="post" id="print_form" target="popup_frame">
        <input type="hidden" id="print_table_date" name="print_table_date">
        <input type="hidden" id="print_table_name" name="print_table_name">
        <input type="hidden" id="new_print_data" name="new_print_data">
    </form>

    <form action="invoice.php" method="post" id="test_form" name="test_form">
        <input type="hidden" id="table_data" name="table_data">
        <input type="hidden" id="table_date" name="table_date">
        <input type="hidden" id="table_name" name="table_name">
    </form>

    <?php $page = "invoice";
    include_once "new_nav.php" ?>
</body>
</html>

<script type="text/javascript" src="//code.jquery.com/jquery-2.2.0.min.js"></script>
<script src="https://cdn.rawgit.com/alertifyjs/alertify.js/v1.0.10/dist/js/alertify.js"></script>
<script>

    function colorChange(obj) {
        $(".marked_custom").css("background", $(obj).html());
    }

    function showInvoiceList(obj) {
        var database = $(obj).find(".left").html();
        $("#"+database+"_list").css("display", "block");
        $("#heading").children().html(database);
        $("#div_invoice_list").css("display", "flex");
        setTimeout(function() {
            $("#div_invoice_list").addClass("list_visible");
        }, 10);
        $(".div_list_visible").on("transitionend", function() {
            $("#"+database+"_list li:first a").each(function() {
                $("#print_date span").html($(this).find("#selected_date").val());
                $("#print_date .print_table_date").html("created on " + $(this).find("#created_date").val());
                showInvoice($(this)[0]);
                $(this).addClass("active");
            });
            $(this).unbind("transitionend");
        });
    }

    function showInvoice(obj) {
        var date = obj.parentNode.children[1].value;
        var database = obj.parentNode.children[2].value;

        $.post("jq_ajax.php", {getTrackedInvoice: "", date: date, database: database}, function(data, status) {
            $(".print_tbody").remove();
            $("#invoice_table").append(data);
            checkRequired();
            markInvoiceRead(obj);
            totalCost();
        });
    }

    function markInvoiceRead(obj) {
        var id = $(obj).parent().find("#invoice_id").val();
        var status = 1;
        var database = $("#heading").children().html();

        $.post("jq_ajax.php", {markInvoiceRead: "", id: id, status: status, database: database}, function() {
            updateCount(database);
        });
    }

    function updateCount(database) {
        $.post("jq_ajax.php", {getUnreadCount: "", database: database}, function(data) {
            $("#"+database).next().html(data);
            if (data == 0) {
                $("#"+database).next().css("display", "none");
            }
        });
    }

    function updateQuantity(obj) {
        if (obj.value < 0 ) {
            obj.value = "";
        } else {
            var itemName = $(obj).parents("tr").find("#item_name").html();
            var date = $(".invoice_date.active").next().val();
            var itemId = $(obj).parents("tr").find("#item_id").val();
            var quantity = obj.value;
            var database = $("#heading").children().html();
            quantity == "" ? quantity = "NULL" : quantity;

            $.post("jq_ajax.php", {updateQuantityDelivered: "", quantity: quantity, itemId: itemId, date: date, database: database}, function(data) {
                if (data) {
                        alertify
                        .delay(2000)
                        .success("Changes Saved");
                updateCost(itemId, quantity, obj);
                }
            })
            .fail(function() {
                alertify
                    .maxLogItems(10)
                    .delay(0)
                    .closeLogOnClick(true)
                    .error("Changes for Item '"+itemName+"' did not saved. Click here to try again", function(event) {
                        updateQuantity(obj);
                    });
            });
        }
    }

    function markCustom(obj) {
        var num = parseFloat(obj.value).toFixed(2);
        if ($(obj).val() == "") {
            $(obj).parents("tr").find(".row_mark").removeClass("marked_warning");
            $(obj).parents("tr").find(".text").html("not delivered");
            $(obj).parents("tr").find("#quantity_delivered").parent().removeClass("field_warning");
            $(obj).prop("readonly", false);
        } else if (num != $(obj).parents("tr").find("#quantity_required").html()){
            $(obj).parents("tr").find(".row_mark").addClass("marked_warning");
            $(obj).parents("tr").find(".text").html("delivered <br> discrepancy");
            $(obj).parents("tr").find("#quantity_delivered").parent().addClass("field_warning");
            $(obj).prop("readonly", true);
        } else {
            $(obj).parents("tr").find(".row_mark").addClass("marked");
            $(obj).parents("tr").find(".text").html("delivered");
            $(obj).prop("readonly", true);
        }
    }

    function updateCost(itemId, quantity, obj) {
        var date = $(".invoice_date.active").next().val();
        var cost = "";
        var database = $("#heading").children().html();
        if (quantity != "NULL") {
            $.post("jq_ajax.php", {getItemPrice: "", itemId: itemId, database: database}, function(data) {
                var price = data;
                cost = quantity * price;
                obj.parentNode.parentNode.children[5].innerHTML = "$ " + cost;
                totalCost();
                saveCost();
            });
        } else {
            obj.parentNode.parentNode.children[5].innerHTML = "-";
            cost = "NULL";
            totalCost();
        }
        function saveCost() {
            $.post("jq_ajax.php", {updateCostDelivered: "", cost: cost, itemId: itemId, date: date, database});
        }
    }

    function updateNotes(obj) {
        var itemName = $(obj).parents("tr").find("#item_name").html();
        var date = $(".invoice_date.active").next().val();
        var itemId = $(obj).parents("tr").find("#item_id").val();
        var database = $("#heading").children().html();
        var note = obj.value;

        $.post("jq_ajax.php", {updateInvoiceNotes: "", note: note, itemId: itemId, date: date, database: database}, function(data) {
            if (data) {
                alertify
                    .delay(2000)
                    .success("Changes Saved");
            }
        })
         .fail(function() {
            alertify
                .maxLogItems(10)
                .delay(0)
                .closeLogOnClick(true)
                .error("Changes for Item '"+itemName+"' did not saved. Click here to try again", function(event) {
                    updateNotes(obj);
                });
        });
    }

    function checkRequired() {
        if ($(".switch-input").prop("checked")) {
            $(".print_tbody").each(function() {
                var total = $(this).find("tr > input").length;
                var remove = 0;
                $(this).find("tr input").each(function() {
                  if ((this.value <=0 || this.value == "") && $(this).parent().nextAll("#td_notes").children("textarea").val() == ""
                       && (($(this).parent().prev().html() == "-") || $(this).parent().prev().html() <= 0)) {
                    $(this).parent().parent().hide();
                    remove++;
                  }
                });
                if (total - remove == 0) {
                    $(this).hide();
                    $(this).children().hide();
                }
            });
        } else {
            $(".print_tbody").each(function() {
                $(this).show();
                $(this).find("tr").show();
            });
        }
    }

    function sendPrint() {
        createTable(function(table) {
            var database = $("#heading").children().html();
            document.getElementById("print_table_name").value = database+" Invoice";
            document.getElementById("new_print_data").value = table.outerHTML;
            document.getElementById("print_table_date").value = $("#print_date").children().find("#table_date_span").html();
            $(".div_popup_back").css("display", "block");
            $("#print_form").submit();
        });
    }

    function printPdf() {
        createTable(function(table) {
            $("#table_data").val(table.outerHTML);
            var database = $("#heading").children().html();
            document.getElementById("table_name").value = database+" Invoice";
            document.getElementById("table_date").value = $("#print_date").children().find("#table_date_span").html();
            $("#test_form").submit();
        });
    }

    function createTable(callBack) {
        var table = document.createElement("table");
        var database = $("#heading").children().html();
        table.setAttribute("class", "table_view");
        table.innerHTML += "<tr class='row'><th colspan='7' class='heading'>"+database+" Invoice</th></tr>";
        $(".table_view tr").each(function() {
            if($(this).css('display') != 'none') {
                var row = document.createElement("TR");
                var cell = "";
                $(this).children(":lt(7)").each(function() {
                    if ($(this).hasClass("row_mark")) {
                        var td = document.createElement("TD");
                        if ($(this).hasClass("marked")) {
                            td.setAttribute("class", "row_mark marked");
                        } else if ($(this).hasClass("marked_warning")) {
                            td.setAttribute("class", "row_mark marked_warning");
                        } else {
                            td.setAttribute("class", "row_mark");
                        }
                        td.innerHTML = $(this).find(".text").html();
                        cell += td.outerHTML;
                    } else if ($(this).children().attr("id") == "quantity_delivered" || $(this).children("textarea").length > 0) { 
                        var td = document.createElement("TD");
                        if ($(this).hasClass('field_warning')) {
                            td.setAttribute("class", "field_warning");   
                        }
                        td.innerHTML = $(this).children().val();
                        cell += td.outerHTML;
                    } else {
                        cell += this.outerHTML;
                    }
                });
                row.innerHTML = cell;
                table.innerHTML += row.outerHTML;
            }
        });
        var totalCost = $("#cost_span").html();
        table.innerHTML += "<tr><td class='table_heading' colspan='4'><h4>Total Cost</h4></td>"+
                           "<td class='table_heading' colspan='3'><h4>"+totalCost+"</h4></td></tr>";
        callBack(table);
    }

    function totalCost() {
        var totalCost = "";
        $(".cost").each(function() {
            var value = $(this).html() != "-" ? $(this).html() : "";
            totalCost = +totalCost + +value.replace('$ ', "");
        });
        var costSpan = document.getElementById("cost_span");
        totalCost != "" ? costSpan.innerHTML = "$" + totalCost  : costSpan.innerHTML = "-";
    }

    $(document).ready(function() {

        $(document).on("click", ".invoice_list li a" ,function() {
            $(".print_tbody").remove();
            $('.invoice_list li a').removeClass("active");
            $(this).addClass('active');
            $("#print_date span").html($(this).find("#selected_date").val());
            $("#print_date .print_table_date").html("created on " + $(this).find("#created_date").val());
        });

        $("#list_back").click(function() {
            $("#div_invoice_list").removeClass("list_visible");
            $("#div_invoice_list").on("transitionend", function() {
                $(this).css("display", "none").unbind("transitionend");
                $('.invoice_list li a').removeClass("active");
                $(".invoice_list").css("display", "none");
                $("#heading").children().html("Resturants");
                $("#table_date_span").html("click on resturant to see invoice list");
                $(".print_table_date").html("");
                $(".print_tbody").remove();
            });
        });

        $(document).on("click", ".row_mark", function() {
            if ($(this).hasClass("marked") || $(this).hasClass("marked_warning")) {
                $(this).removeClass("marked marked_warning")
                $(this).find(".text").html("not delivered");
                $(this).parent().find("#quantity_delivered").parent().removeClass("field_warning");
                $(this).parent().find("#quantity_delivered").val("").prop("readonly", false);
                $(this).parents("tr").removeClass("status_warning");
                updateQuantity($(this).parent().find("#quantity_delivered")[0]);
            } else if ($(this).parents("tr").find("#quantity_required").html() != "-") {
                $(this).addClass("marked");
                $(this).find(".text").html("delivered");
                if ($(this).parent().find("#quantity_required").html() >= 0 && 
                    $(this).parent().find("#quantity_delivered").val() == "") {
                    $(this).parent().find("#quantity_delivered").val($(this).parent().find("#quantity_required").html());
                }
                $(this).parent().find("#quantity_delivered").prop("readonly", true);
                updateQuantity($(this).parent().find("#quantity_delivered")[0]);
            }
        });

        $("#popup_close").click(function() {
            $(".div_popup_back").fadeOut(190, "linear");
            $(".main_iframe").removeClass("blur");
        });

     });

</script>

