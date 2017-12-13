<?php
session_start();
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
$_SESSION["last_activity"] = time();

if(isset($_POST["new_date"])) {
    $_SESSION["date"] = $_POST["new_date"];
    $_SESSION["date_check"] = "checked";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Tasks</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="overflow_hidden">
    <div class="main">
        <ul class="sidenav font_roboto" id="sideNav">
            <li id="heading"><h4>Admin Tasks</h4></li>
            <div class="flex_col flex_1">
                <div class="flex_row">
                    <div class="side_date flex_1 left">
                        <?php $date = date_create($_SESSION["date"]) ?>
                        <span id="date"><?php echo date_format($date, "jS") ?></span>
                        <span id="month"><?php echo date_format($date, "M") ?></span>
                        <span id="year"><?php echo date_format($date, "Y") ?></span>
                        <div id="div_cal"></div>
                    </div>
                    <div class="flex_2 right">
                        <li>
                            <a class="active" href="edit_categories.php" target="task_frame" >
                                <span class="image entypo-archive" ></span>
                                <span class="text">Suppliers</span>
                            </a>
                        </li>
                        <li>
                            <a href="edit_items.php" target="task_frame" >
                                <span class="image entypo-basket"></span>
                                <span class="text">Items</span>
                            </a>
                        </li>
                    </div>
                </div>
                <div class="flex_row flex_1">
                    <div class="flex_1 left" id="dateless">
                        <span>dateless</span>
                    </div>
                    <div class="flex_2">
                        <li>
                            <a href="manage_users.php" target="task_frame">
                                <span class="image entypo-users"></span>
                                <span class="text">Users</span>
                            </a>
                        </li>
                        <li>
                            <a href="user_groups.php" target="task_frame">
                                <span class="image fa-users"></span>
                                <span class="text">User Groups</span>
                            </a>
                        </li>
                        <li>
                            <a href="contacts.php" target="task_frame">
                                <span class="image fa-id-card-o"></span>
                                <span class="text">Contacts</span>
                            </a>
                        </li>
                        <li>
                            <a href="admin_settings.php" target="task_frame">
                                <span class="image fa-cog"></span>
                                <span class="text">Settings</span>
                            </a>
                        </li>
                    </div>
                </div>
            </div>
        </ul>

        <div class="main_top_side">
            <iframe class="iframe" src="edit_categories.php" frameborder="0" name="task_frame" id="task_frame"></iframe>
        </div>
    </div>

    <form action="admin_tasks.php" method="post" id="cal_form">
        <input type="hidden" id="cal_date" name="new_date" value="<?php echo $_SESSION["date"] ?>">
    </form>

<script type="text/javascript" src="jq/jquery-3.2.1.min.js"></script>
    <?php $page = "admin tasks";
    include_once "new_nav.php" ?>
</body>
</html>
<script src="jq/jquery-ui.min.js"></script>
<script>
    $(document).ready(function() {

        $('#sideNav li a').click(function() {
           $('#sideNav li a').removeClass("active");
           $(this).addClass('active');
        });

        $(".side_date").click(function() {
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
                if(!$(event.target).closest('.side_date', window.parent.document).length && !$(event.target).is("a, span")) {
                    if($('.ui-datepicker').is(":visible")) {
                        $('#div_cal .ui-datepicker').css("display", "none");
                    }
                }
            }
        });

        $(document).on("click", ".ui-datepicker-current", function(event) {
            event.preventDefault();
            $('#div_cal .ui-datepicker').css("display", "none");
        });

     });
</script>
