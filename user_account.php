<?php
session_start();
require_once "database/user_table.php";

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

if (isset($_POST["new_password"])) {
    UserTable::update_user_password($_POST["user_name"], $_POST["new_password"]);
}
if (isset($_POST["update_user"])) {
    $tz = $_SESSION["timezone"];
    if (!empty($_POST["city_select"])) {
        $tz = ($_POST["region_select"]. "/" .$_POST["city_select"]);
        $_SESSION["timezone"] = $tz;
    }
    if (UserTable::update_user_details($_SESSION["username"], $_POST["update_user"], $_POST["update_first"], $_POST["update_last"], $tz, $_POST["update_timeout"])) {
        $_SESSION["username"] = $_POST["update_user"];
        $_SESSION["time_out"] = $_POST["update_timeout"];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="overflow_hidden">
    <div class="main font_open_sans overflow_hidden">
        <?php $result = UserTable::get_user_details($_SESSION["username"]);
              $row = $result->fetch_assoc(); ?>
        <div class="user_div_container left">
            <div class="div_card info">
                <div><span class="entypo-user avatar_big"></span></div>
                <div>
                    <span id="display_user_name"><?php echo $row["username"] ?></span>
                    <span id="display_full_name"><?php echo $row["first_name"]." ".$row["last_name"] ?></span>
                    <span id="display_user_role"><?php echo $row["role"] ?></span>
                </div>
            </div>
            <div class="div_card settings">
                <ul>
                    <li class="fa-cogs" id="heading">settings</li>
                    <li><a class="entypo-card active" onclick=getSettings(this) >account details</a></li>
                    <li><a class="entypo-bell" onclick=getSettings(this) >notifications</a></li>
                </ul>
            </div>
            <span class="version_dark">v1.2.4</span>
        </div>
        <div class="user_div_container right"></div>
    </div>
    <?php $page= "user account";
    include_once "new_nav.php" ?>
</body>
</html>

<script type="text/javascript" src="//code.jquery.com/jquery-2.2.0.min.js"></script>
<script>

    function getSettings(obj) {
        switch (obj.innerHTML) {
            case 'account details':
                $(".right").load("account_details.php");
                break;
            case 'notifications':
                $(".right").load("manage_notifications.php");
                break;
        }
        $("a").removeClass("active");
        $(obj).addClass("active");
    }

    $(document).ready(function() {

        $(".right").load("account_details.php");
    });
</script>