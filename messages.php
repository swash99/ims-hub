<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION["last_activity"]) && $_SESSION["last_activity"] + $_SESSION["time_out"] * 60 < time()) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
$_SESSION["last_activity"] = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages</title>
    <meta id="vp" name="viewport" content="width=device-width">
    <script>
        if (screen.width < 700)
        {
            var vp = document.getElementById('vp');
            vp.setAttribute('content','width=780');
        }
    </script>
    <link rel="stylesheet" href="styles.css?2">
</head>
<body>
    <div class="main">
        <ul class="sidenav font_roboto" id="message_sidenav">
            <li id="compose_li"><button class="button_flat fa-edit" onclick=composeButton()>Compose</button></li>
            <li>
                <a id="inbox" class="active" href="received_messages.php" target="message_frame" >
                    <span class="text">Inbox</span>
                    <span id="status_view"></span>
                </a>
            </li>
            <li><a id="deleted" href="deleted_messages.php" target="message_frame"><span class="text">Deleted</span></a></li>
        </ul>

        <div class="main_top_side font_open_sans">
            <iframe class="iframe" src="received_messages.php" frameborder="0" name="message_frame" id="message_frame" onload="showUnreadCount(this);" ></iframe>
        </div>

         <div class="div_popup_back font_open_sans">
            <div class="div_popup popup_share">
                <div class="popup_titlebar">
                    <span>New Message</span>
                    <span class="popup_close" id="popup_close"></span>
                </div>
                <iframe id="popup_frame" name="popup_frame" frameborder="0"></iframe>
            </div>
        </div>
    </div>
    <form id="popup_form" action="compose_messages.php" method="post" target="popup_frame"></form>
    <input type="hidden" id="session_name" value="<?php echo $_SESSION['username']; ?>">
    <?php $page = "messages";
          include_once "new_nav.php" ?>
</body>
</html>

<script type="text/javascript" src="//code.jquery.com/jquery-2.2.0.min.js"></script>
<script>
    function composeButton() {
        $("#popup_form").submit();
        $(".div_popup_back").css("display", "block");
    }

    function showUnreadCount(iframe){
        if (iframe.contentDocument.title == "Inbox") {
            var unreadCount = $("#message_frame").contents().find(".unread").length;
            if (unreadCount > 0) {
                document.getElementById("status_view").innerHTML =  unreadCount;
                document.getElementById("status_view").style.visibility = "visible";
                document.getElementById("status_view").style.transform = "scale(1)";
            } else {
                document.getElementById("status_view").style.transform = "scale(0.1)";
                document.getElementById("status_view").style.visibility = "hidden";
            }
        }
    }

    $(document).ready(function() {
        $("#popup_close").click(function() {
            $(".div_popup_back").fadeOut(190, "linear");
        });

        $("a").click(function() {
            $("a").removeClass("active");
            $(this).addClass("active");
        });
    });
</script>
