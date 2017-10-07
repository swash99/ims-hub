<?php
session_start();
include_once "utilities.php";
require_once "database/conversation_table.php";

if (!isset($_SESSION["username"])) {
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

if (isset($_POST["conversation_id"])) {
    if(ConversationTable::update_conversation_status($_SESSION["username"], $_POST["conversation_id"], "deleted")) {
        $date = date_format((date_add(date_create(gmdate("Y-m-d")), date_interval_create_from_date_string("1 week"))), "Y-m-d");
        ConversationTable::set_destroy_date($_SESSION["username"], $_POST["conversation_id"], "'$date'");
    }
}
if (isset($_POST["checkbox"])) {
    ConversationTable::update_multiple_conversation_status($_SESSION["username"], $_POST["checkbox"], "deleted");
    $date = date_format((date_add(date_create(gmdate("Y-m-d")), date_interval_create_from_date_string("1 week"))), "Y-m-d");
    ConversationTable::set_multiple_destroy_date($_SESSION["username"], $_POST["checkbox"], "'$date'");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inbox</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="main_iframe font_open_sans" id="message_iframe">
        <div class="toolbar_conversation">
            <div class="toolbar_div">
                <div class="checkbox">
                    <input title="Select All" id="select_all" type="checkbox">
                    <span class="checkbox_style"></span>
                </div>
                <span id="checked_count">0</span>
                <span class="toolbar_image entypo-mail"></span>
            </div>
            <div class="divider"></div>
            <div class="toolbar_div" id="button_div">
                <form action="received_messages.php" id="multi_delete_form" method="post">
                    <input class="option" type="submit" id="multi_delete" name="multi_delete" value="Delete">
                </form>
                <div class="dropdown_main">
                    <button class="option">Mark</button>
                    <div class="dropdown_div">
                        <a id="read"class="dropdown_content">Read</a>
                        <a id="unread"class="dropdown_content">Unread</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="message_table">
            <?php $result = ConversationTable::get_received_conversations($_SESSION["username"]) ?>
            <?php while ($row = $result->fetch_assoc()): ?>
               <div class="message_row <?php if(($row['sender'] == $_SESSION['username'] AND $row['sender_status'] == 'unread')
                                        OR ($row['receiver'] == $_SESSION['username'] AND $row['receiver_status'] == 'unread')) {
                                        echo 'unread';} ?>">
                    <div class="div_left">
                        <div class="message_cell checkbox">
                            <input type="checkbox" name="checkbox[]" form="multi_delete_form" value="<?php echo $row["id"] ?>">
                            <span class="checkbox_style"></span>
                        </div>
                        <div class="message_cell body"  onclick=openMessage(this)>
                            <div class="message_cell name">
                                <?php echo $row["first_name"]." ".$row["last_name"]; ?>
                            </div>
                            <div class="con_container">
                                <span class="title">
                                    <?php echo $row["title"]; ?>
                                </span>
                                <span class="conversation">
                                    <?php echo $row["mSender"].": ".substr($row["message"], 0, 120); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="div_right">
                        <div class="message_cell date"  onclick=openMessage(this)><?php echo convert_date_timezone($row["timestamp"]); ?></div>
                    </div>
                    <input type="hidden" value="<?php echo $row['sender'] == $_SESSION['username'] ? $row['receiver'] : $row['sender']; ?>">
                    <input type="hidden" name="conversation_id" value="<?php echo $row["id"] ?>"></form>
                </div>
            <?php endwhile ?>
        </div>

        <form action="message_view.php" id="view_message" method="post">
            <input type="hidden" id="conversation_id" name="conversation_id">
            <input type="hidden" id="receiver_name" name="receiver_name">
            <input type="hidden" name="status_to_read">
        </form>
   </div>
</body>
</html>

<script type="text/javascript" src="//code.jquery.com/jquery-2.2.0.min.js"></script>
<script>
    function openMessage(obj){
        obj.parentNode.parentNode.className = "message_row";
        showUnreadCount(function() {
            var id = obj.parentNode.parentNode.children[3].value;
            var receiver = obj.parentNode.parentNode.children[2].value;
            document.getElementById("conversation_id").value = id;
            document.getElementById("receiver_name").value = receiver;
            document.getElementById("view_message").submit();
        });
    }

    $(document).ready(function(){
        $(".message_row").change(function() {
            if($("input[type='checkbox']", this).prop('checked') == true){
                $("#button_div").fadeIn(200, "linear");
                $("#button_div").css("display", "inline-block");
            } else if($("input[type='checkbox']").filter(':checked').length == 0) {
                $("#button_div").fadeOut(200, "linear");
            }
            countChecked();
        });

        $("#select_all").change(function(){
            $("input[type='checkbox']").prop("checked", $(this).prop("checked"));
            if ($("#select_all").prop("checked")) {
                $("#button_div").fadeIn(200, "linear");
                $("#button_div").css("display", "inline-block");
            } else {
                $("#button_div").fadeOut(200, "linear");
            }
            countChecked();
        });

        $(".dropdown_div #read").click(function(){
            $("input[type='checkbox']:checked").parents(".message_row").each(function(){
                $(this).removeClass("unread");
                $(this).prev().html("read");
            });
            var idVal = $(".message_table input[type='checkbox']:checked").map(function(){
                return $(this).val();
            }).get();
            $.post("jq_ajax.php", {checkedId: idVal, newStatus: "read"});
            showUnreadCount();
        });

        $(".dropdown_div #unread").click(function(){
            $("input[type='checkbox']:checked").parents(".message_row").each(function(){
                $(this).addClass("unread");
                $(this).prev().html("unread");
            });
            var idVal = $(".message_table input[type='checkbox']:checked").map(function(){
                return $(this).val();
            }).get();
            $.post("jq_ajax.php", {checkedId: idVal, newStatus: "unread"});
            showUnreadCount();
        });
    });

    function showUnreadCount(callBack){
        var unreadCount = $(".unread").length;
        if (unreadCount > 0) {
            window.parent.document.getElementById("status_view").innerHTML =  unreadCount;
            window.parent.document.getElementById("status_view").style.visibility = "visible";
            window.parent.document.getElementById("status_view").style.transform = "scale(1)";
        } else {
            window.parent.document.getElementById("status_view").style.transform = "scale(0.1)";
            window.parent.document.getElementById("status_view").style.visibility = "hidden";
        }
        if (typeof callBack == "function") {
            callBack();
        }
    }

    function countChecked(){
        var count = $(".message_table input[type='checkbox']:checked").length;
        if(count == 0) {
            $("#checked_count").text("0");
        } else if (count > 1) {
            $("#checked_count").text(count);
        } else {
            $("#checked_count").text(count);
        }
    }
</script>