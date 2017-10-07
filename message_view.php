<?php
session_start();
include "utilities.php";
require_once "database/conversation_table.php";
require_once "database/message_table.php";

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}
if (isset($_POST["status_to_read"])) {
    ConversationTable::update_conversation_status($_SESSION["username"], $_POST["conversation_id"], "read");
}
if (isset($_POST["reply"])) {
    if (MessageTable::create_message($_SESSION["username"], $_POST["receiver_name"], $_POST["message"], $_POST["conversation_id"], gmdate("Y-m-d H:i:s"))) {
        ConversationTable::update_conversation_status($_POST["receiver_name"], $_POST["conversation_id"], "unread");
        ConversationTable::set_destroy_date($_POST["receiver_name"], $_POST["conversation_id"], 'NULL');
    }
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
    <div class="main_iframe font_open_sans">
        <div class="messages_div" id="messages_div">
        <?php $result = MessageTable::get_messages($_POST["conversation_id"]); ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div  <?php if($row["username"] == $_SESSION["username"]) {echo "style='text-align:right;'";} ?>>
                <div class="main_message_div ">
                    <div class="message_name" >
                        <span id="name"><?php echo $row["first_name"]." ".$row["last_name"] ?></span>
                    </div>
                    <div class="message">
                        <div id="message_div">
                            <pre id="message"><?php echo $row["message"] ?></pre>
                            <span id="time"><?php echo convert_date_timezone($row["timestamp"]);?></span>
                        </div>
                    <?php if ($row["attachment"] != null): ?>
                        <div class="div_attachment">
                            <span><?php echo $row["attachment_title"] ?></span>
                            <input type="hidden" value='<?php echo $row["attachment"] ?>'>
                        </div>
                    <?php endif ?>
                    </div>
                </div>
            </div>
        <?php endwhile ?>
        </div>
        <div class="reply_div">
            <form action="message_view.php" method="post">
                <textarea name="message" id="reply_text" autofocus></textarea>
                <input type="hidden" name="conversation_id" value="<?php echo $_POST["conversation_id"] ?>">
                <input type="hidden" name="receiver_name" value="<?php echo $_POST["receiver_name"]; ?>">
            <div class="reply_toolbar">
                <input class="button" type="submit" name="reply" value="Reply">
            </div>
            </form>
        </div>
    </div>

    <div class="div_popup_back font_open_sans">
        <div class="div_popup popup_print_table">
            <div class="popup_titlebar">
                <span class="popup_close"></span>
                <span id="title_name"><?php echo $row["attachment_title"] ?></span>
            </div>
            <div id="table_div"></div>
        </div>
    </div>
</body>
</html>

<script type="text/javascript" src="//code.jquery.com/jquery-2.2.0.min.js"></script>
<script>
    document.getElementById("messages_div").scrollIntoView(false);

    $(document).ready(function() {
        $(".div_attachment span").click(function() {
            var data = $(this).next().val();
            $("#title_name").html($(this).html());
            $(".div_popup #table_div").append(data);
            $(".div_popup_back").css("display", "block");
            $(".main_iframe").addClass("blur");
        });

         $(".popup_close").click(function() {
            $(".main_iframe").removeClass("blur");
            $(".div_popup_back").fadeOut(190, "linear");
            $(".div_popup #table_div").html("");
        });
    });
</script>