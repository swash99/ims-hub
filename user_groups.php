<?php
session_start();
require_once "database/user_group_table.php";
require_once "database/user_group_list_table.php";
require_once "database/user_table.php";

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
?>
    <script>
        window.parent.location.href = window.parent.location.href;
    </script>
<?php
exit();
}
$_SESSION["last_activity"] = time();

if (isset($_POST["add_button"]) AND !empty($_POST["group_name"])) {
    try {
        if (!UserGroupTable::add_group($_POST["group_name"])) {
            echo '<div class="error">Recipe already exists</div>';
        }
    } catch (Exception $e) {
        echo '<div class="error">'.$e->getMessage().'</div>';
    }
}
if(isset($_POST["delete_group"])) {
    UserGroupTable::remove_group($_POST["delete_group"]);
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
        <div class="div_category">
            <h4 class="font_roboto">User Groups</h4>
            <div class="div_list_category">
                <ul class="category_list" id="recipe_list">
                <?php $result = UserGroupTable::get_groups() ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li id="<?php echo $row['id']?>" class="list_category_li" onclick=groupSelect(this)>
                        <span><?php echo $row["name"]?></span>
                        <form action="user_groups.php" method="post">
                            <input type="hidden" name="delete_group" value="<?php echo $row['name']?>" >
                        </form>
                    </li>
                <?php endwhile ?>
                </ul>
            </div>
            <input type="hidden" id="category_select">
            <div class="category_add" id="category_add">
                <button class="button_flat entypo-trash float_left" onclick=deleteGroup()>delete</button>
                <button class="button_flat entypo-plus float_right" onclick=slideDrawer()>Add</button>
            </div>
            <div class="category_add_drawer">
                <form action="user_groups.php" method="post" >
                    <input class="category_input" type="text" name="group_name" id="category_name" placeholder="Group Name">
                    <input type="submit" name="add_button" value="Add" class="button">
                </form>
                <button class="button_cancel" onclick=slideDrawer()>cancel</button>
            </div>
        </div>

        <div class="list_container" id="list_container">
            <div class="div_item_list">
                <h4 class="font_roboto">Users Added</h4>
                <div id="div" class="div_list">
                    <ul class="category_list" name="" id="categorized_list" ></ul>
                </div>
            </div>
            <div class="div_item_list">
                <h4 class="font_roboto">Available Users</h4>
                <div class="div_list">
                    <ul class="category_list" >
                    <?php $result = UserTable::get_users(); ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <li class="list_li all_items" id="list_li" user-id="<?php echo $row['user_id'] ?>"><?php echo $row["username"];?></li>
                    <?php endwhile ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<script type="text/javascript" src="//code.jquery.com/jquery-2.2.0.min.js"></script>
<script src="https://cdn.rawgit.com/alertifyjs/alertify.js/v1.0.10/dist/js/alertify.js"></script>
<script
      src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"
      integrity="sha256-xNjb53/rY+WmG+4L6tTl9m6PpqknWZvRt0rO1SRnJzw="
      crossorigin="anonymous"></script>
<script src="touch_punch.js"></script>
<script>

    function groupSelect(obj) {
        var groupId = $(obj).attr("id");

        $.post("jq_ajax.php", {getGroupUsers: "", groupId: groupId}, function(data, status) {
            document.getElementById("categorized_list").innerHTML = data;
            $(".all_items").show();
            $(".grouped_item").each(function() {
                var userName = $(this).attr("item-name");
                $(".all_items").each(function() {
                    if ($(this).html() == userName) {
                        $(this).hide();
                    }
                });
            });
        });
    }

    function addGroupUser(obj) {
        var groupId = $(".list_category_li.active").attr("id");
        var userId = $(obj).attr("user-id");

        $.post("jq_ajax.php", {addGroupUser: "", userId: userId, groupId: groupId});
    }

    function deleteGroupUser(obj) {
        var groupId = $(obj).attr("group-id");
        var userId = $(obj).attr("user-id");

        $.post("jq_ajax.php", {deleteGroupUser: "", userId: userId, groupId: groupId});
    }

    function slideDrawer() {
        $(".category_add_drawer").slideToggle(180, "linear");
    }

    function deleteGroup() {
        alertify.confirm("Delete Group '"+$(".active").children("span").html()+"' ?", function() {
            $(".active").children("form").submit();
        });
    }

    $(document).ready(function() {
        $(".list_category_li:first").each(function() {
            groupSelect($(this)[0]);
           $(this).addClass("active");
        });

        $(".list_category_li").click(function() {
            $(".list_category_li").removeClass("active");
            $(this).addClass("active");
        });

        $(".all_items").click(function() {
            var userId = $(this).attr("user-id");
            var groupId  = $(".active").attr("id");
            var userName = $(this).html();
            var li = document.createElement("li");

            li.className = "list_li grouped_item";
            li.setAttribute("user-id", userId);
            li.setAttribute("group-id", groupId);
            li.setAttribute("item-name", userName);
            li.innerHTML = userName;

            addGroupUser($(this)[0]);
            $(this).hide();
            document.getElementById("categorized_list").append(li);
        });

        $(document).on("click", ".grouped_item", function() {
            var userId = $(this).attr("user-id");
            deleteGroupUser($(this)[0]);
            $(this).remove();
            $(".all_items[user-id='"+userId+"']").show();
        });
    });

</script>
