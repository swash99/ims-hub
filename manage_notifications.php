<?php
session_start();
require_once "database/user_table.php";
require_once "database/notification_list_table.php";
require_once "database/sub_notification_list_table.php";
require_once "database/notification_status_table.php";
require_once "database/sub_notification_status_table.php";

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

if (isset($_POST["user_email"])) {
    UserTable::update_user_email($_SESSION["username"], $_POST["user_email"]);
}
?>

<div class="div_manage font_open_sans">
    <?php $result = NotificationListTable::get_notification_list();
    while ($noti_list = $result->fetch_assoc()): ?>
        <div class="div_main_option">
            <div class="container">
                <span class="noti_name"><?php echo $noti_list["name"] ?></span>
                <?php $noti_status = NotificationStatusTable::get_notification_status($_SESSION["username"], $noti_list["id"])->fetch_assoc(); ?>
                <label class="switch">
                    <input class="switch-input" type="checkbox" <?php echo $noti_status["status"] == 1 ? "checked" : "" ?>/>
                    <span class="switch-label" data-on="on" data-off="off"></span>
                    <span class="switch-handle"></span>
                </label>
                <?php if ($noti_list["name"] == "notify by email"): ?>
                    <form action="user_account.php" method="post">
                    <?php  $email = UserTable::get_user_details($_SESSION["username"])->fetch_assoc()["email"];?>
                        <input type="email" name="user_email" value="<?php echo $email ?>" onchange=setUserEmail(this) placeholder="Add Email">
                    </form>
                <?php endif ?>
                <input type="hidden" id="noti_id" value="<?php echo $noti_list["id"] ?>">
            </div>

            <div class="div_sub">
                <?php $rows = SubNotificationListTable::get_notification_list();
                while ($sub_noti_list = $rows->fetch_assoc()): ?>
                <?php if ($noti_list["name"] == "notify by message" AND $sub_noti_list["name"] == "received messages"){
                    continue;
                } ?>
                    <div class="div_sub_option">
                        <span class="noti_name"><?php echo $sub_noti_list["name"] ?></span>
                        <span class="entypo-info"></span>
                        <?php $sub_noti_status = SubNotificationStatusTable::get_notification_status($_SESSION["username"], $sub_noti_list["id"], $noti_list["id"])->fetch_assoc(); ?>
                         <label class="switch">
                            <input class="switch-input" type="checkbox" <?php echo $sub_noti_status["status"] == 1 ? "checked" : "" ?>/>
                            <span class="switch-label" data-on="on" data-off="off"></span>
                            <span class="switch-handle"></span>
                        </label>
                        <?php switch ($sub_noti_list["name"]) {
                            case 'daily deviation report':
                                echo '<span class="info_span">report showing items with deviation warnings from the previous day</span>';
                                break;

                            case 'incomplete inventory alert':
                                echo '<span class="info_span">alert sent when item quantities have
                                        not been entered by 12:30AM on the following day</span>';
                                break;

                            case 'received messages':
                                echo '<span class="info_span">messages received from other users</span>';
                                break;
                        } ?>
                        <input type="hidden" id="sub_noti_id" value="<?php echo $sub_noti_list["id"] ?>">
                        <input type="hidden" id="parent_noti_id" value="<?php echo $noti_list["id"] ?>">
                    </div>
                <?php endwhile ?>
            </div>
        </div>
    <?php endwhile ?>
</div>
<input type="hidden" id="user_name" value="<?php echo $_SESSION["username"] ?>">

<script type="text/javascript" src="//code.jquery.com/jquery-2.2.0.min.js"></script>
<script>

    function setNotiStatus(obj) {
        var notiId = $(obj).parents(".container").children("#noti_id").val();
        var userName = document.getElementById("user_name").value;
        var status = obj.checked;
        status = status == true ? 1 : 0;
        $.post("jq_ajax.php", {setNotiStatus: "", user_name: userName, notification_id: notiId, status: status});
    }

    function setSubNotiStatus(obj) {
        var notiId = obj.parentNode.parentNode.children[4].value;
        var parentNotiId = obj.parentNode.parentNode.children[5].value;
        var userName = document.getElementById("user_name").value;
        var status = obj.checked;
        status = status == true ? 1 : 0;
        $.post("jq_ajax.php", {setSubNotiStatus: "", user_name: userName, notification_id: notiId, status: status, parent_noti_id: parentNotiId});
    }

    function setUserEmail(obj) {
        var email = obj.value;
        var userName = document.getElementById("user_name").value;

        $.post("jq_ajax.php", {setUserEmail: "", userName: userName, email: email});

    }

    // function showSubOption(obj) {
    //     if (obj.checked) {
    //         $(obj).parent().parent().parent().children(".div_sub").show();
    //     } else {
    //         $(obj).parent().parent().parent().children(".div_sub").hide();
    //     }
    // }

    $(document).ready(function() {
        // $(".container .switch-input").each(function() {
        //     if ($(this).prop("checked")) {
        //         $(this).parent().parent().next().show();
        //     }
        // });


        $(".container .switch-input").click(function(event) {
            if ($(this).parents(".container").children("form").length > 0) {
                if ($(this).parents(".container").find("input[type=email]").val() != "") {
                    setNotiStatus($(this)[0]);
                    var value = $(this).prop("checked");
                    $(this).parents(".div_main_option").find(".div_sub .switch-input").each(function() {
                        $(this).prop("checked", value);
                        setSubNotiStatus($(this)[0]);
                    });
                } else {
                    event.preventDefault();
                    $(this).parents(".container").find("input[type=email]").focus();
                }

            } else {
                setNotiStatus($(this)[0]);
                    var value = $(this).prop("checked");
                    $(this).parents(".div_main_option").find(".div_sub .switch-input").each(function() {
                        $(this).prop("checked", value);
                        setSubNotiStatus($(this)[0]);
                    });
            }
        });

        $(".div_sub .switch-input").click(function() {
            if ($(this).parents(".div_sub").prev().find("input[type=email]").val() != "") {
                if ($(this).parents(".div_sub").prev().find("input[type=checkbox]").prop("checked") == false) {
                    $(this).parents(".div_sub").prev().find("input[type=checkbox]").prop("checked", true);
                    setNotiStatus($(this).parents(".div_sub").prev().find("input[type=checkbox]")[0]);
                }
                if($(this).parents(".div_sub").find("input[type=checkbox]:checked").length == 0){
                    $(this).parents(".div_sub").prev().find("input[type=checkbox]").prop("checked", false);
                    setNotiStatus($(this).parents(".div_sub").prev().find("input[type=checkbox]")[0]);
                }
                setSubNotiStatus($(this)[0]);
            } else {
                event.preventDefault();
                $(this).parents(".div_sub").prev().find("input[type=email]").focus();
            }
        });

        $(".entypo-info").click(function() {
            if ($(this).siblings(".info_span").css("opacity") == "0") {
                $(this).siblings(".info_span").animate({opacity: '1'}, 250);
                $(this).addClass("active");
            } else{
                $(this).siblings(".info_span").animate({opacity: '0'}, 250);
                $(this).removeClass("active");
            }
        });
    });
</script>
