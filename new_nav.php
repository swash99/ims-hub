<?php
require_once "database/conversation_table.php";

if (isset($_POST["logout"])) {
    session_start();
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
if(isset($_POST["dateview"])) {
    $_SESSION["date"] = $_POST["dateview"];
}
if(isset($_SESSION["username"])) {
    $unread_count = ConversationTable::count_unread_conversations($_SESSION["username"]);
}
?>

<nav class="nav_bar font_open_sans">
    <ul class="clearfix">
        <li class=<?php if (isset($page) AND ($page == "home")) {echo "active";} ?>>
            <a href="category_status.php">
                <span class="entypo-home"></span>
                <span class="home" id="text">Home</span>
            </a>
        </li>
        <li class=<?php if (isset($page) AND ($page == "messages")) {echo "active";} ?> id="messages">
            <a href="messages.php">
                <span class="entypo-mail"></span>
                <span class="home" id="text">Message</span>
                <div class="noti_dot">
                    <span><?php echo $unread_count?></span>
                </div>
            </a>
        </li>
    </ul>
    <ul id="nav_right">
        <li id="userinfo" >
            <div id="username">
                <div>
                    <span class="entypo-user avatar"></span>
                </div>
                <div class=<?php if (isset($page) AND ($page == "user account")) {echo "active";} ?>>
                    <span id="user_name"><?php echo $_SESSION["username"]; ?></span>
                    <span id="user_role"><?php echo $_SESSION["userrole"] ?></span>
                </div>
                <a href="user_account.php"></a>
            </div>
        </li>
        <?php if ($_SESSION["userrole"] == "admin"): ?>
        <li class=<?php if (isset($page) AND ($page == "admin tasks")) {echo "active";} ?>>
            <a href="admin_tasks.php">
                <span class="entypo-tools"></span>
                <span class="home" id="text">Admin Tasks</span>
            </a>
        </li>
        <?php endif ?>
        <li id="logout">
            <form action="new_nav.php" method="post" onclick="this.submit()">
                <span class="fa-power-off logout_image"></span>
                <span class="logout_text">logout</span>
                <input type="hidden" name="logout">
            </form>
        </li>
    </ul>
</nav>

<script type="text/javascript" src="//code.jquery.com/jquery-2.2.0.min.js"></script>
<script>
    $(document).ready(function(){
        $("nav ul li").hover(function(){
            $("ul", this).slideDown(150, "linear");
        }, function(){
            $("ul", this).slideUp(150, "linear");
        });

        if (!$("#messages").hasClass("active")) {
            if($(".noti_dot span").html() > 0) {
                $(".noti_dot").addClass("show");
            }
        }

    });
</script>
