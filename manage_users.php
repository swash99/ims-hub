<?php
session_start();
require_once "database/user_table.php";
require_once "database/user_role_table.php";

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

if (isset($_POST["new_username"])) {
    try {
        if (!UserTable::add_new_user($_POST['new_username'], $_POST["new_firstname"], $_POST["new_lastname"], $_POST['new_password'], $_POST['userrole'])) {
            echo '<div class="error">Username already exists</div>';
        }
    } catch (Exception $e) {
        echo '<div class="error">'.$e->getMessage().'</div>';
    }
}

if(isset($_POST["delete_username"])){
    UserTable::delete_user($_POST["delete_username"]);
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
    <div id="add_div_main" class="none">
        <div id="add_div" class="add_div">
        <div>
            <h4>Add New User</h4>
            <form action="manage_users.php" method="post">
            <div class="inline">
                <label for="new_username">User name</label>
                <input class="userinput" type="text" name="new_username" placeholder="Username" required>
                <label for="userrole">User role</label><select name="userrole" class="none role_select">
                <?php $result = UserRoleTable::get_roles(); ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <option   value="<?php echo $row["role"]?>" > <?php echo $row["role"] ?> </option>
                <?php endwhile ?>
                </select>
            </div>
            <div class="inline">
                <label for="new_firstname">First name</label>
                <input class="userinput" type="text" name="new_firstname" placeholder="First Name" required>
                <label for="new_lastname">Last name</label>
                <input class="userinput" type="text" name="new_lastname" placeholder="Last Name" required>
            </div>
            <div class="inline">
                <label for="new_password">Password</label>
                <input class="userinput" type="password" name="new_password" placeholder="Password" required>
                <input type="submit" value="Add" class="button button_add_drawer">
            </div>
            </form>
        </div>
        </div>
        <button id="drawer_tag" class="drawer_tag">Add</button>
    </div>
    <div class="div_fade"></div>
    <div class="user_table_div">
        <table class="user_table" id="table" >
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Role</th>
                <th>Session timeout</th>
                <th id="th_delete">Delete</th>
            </tr>
            <?php $result = UserTable::get_users(); ?>
            <?php while ($userdata = $result->fetch_assoc()): ?>
                <tr>
                    <td> <?php echo $userdata["first_name"]." ".$userdata["last_name"]; ?></td>
                    <td id="name"><?php echo $userdata["username"]; ?></td>
                    <td id="role">
                        <select onchange=updateRole(this) id=""class="none" <?php if ($userdata["username"] == $_SESSION["username"]) {echo "disabled";} ?>>
                            <?php $result2 = UserRoleTable::get_roles(); ?>
                            <?php while ($row = $result2->fetch_assoc()): ?>
                                <option  value="<?php echo $row["role"]?>" <?php if ($userdata["role"] == $row["role"]) {echo "selected";}?> > <?php echo $row["role"] ?> </option>
                            <?php endwhile ?>
                        </select>
                    </td>
                    <td id="timeout"> <?php echo $userdata["time_out"]; ?>
                    </td>
                    <td id="delete">
                        <form action="manage_users.php" method="post" onclick=deleteUser(this)>
                            <input type="hidden" id="delete_username" name="delete_username" value="<?php echo $userdata["username"] ?>">
                            <span class="entypo-trash" <?php if ($userdata["username"] == $_SESSION["username"]) { echo "style='display: none;'";} ?>></span>
                        </form>
                    </td>
                </tr>
            <?php endwhile ?>
        </table>
    </div>
    </div>
</body>
</html>

<script type="text/javascript" src="//code.jquery.com/jquery-2.2.0.min.js"></script>
<script src="https://cdn.rawgit.com/alertifyjs/alertify.js/v1.0.10/dist/js/alertify.js"></script>
<script>
     function updateRole(obj) {
        var role = obj.value;
        var rowIndex = obj.parentNode.parentNode.rowIndex;
        var roleUserName = document.getElementById("table").rows[rowIndex].cells[1].innerHTML;

        $.post("jq_ajax.php", {newRole: role, roleUserName: roleUserName});
    }

    function deleteUser(obj) {
        var name = obj.children[0].value;
        alertify.confirm("Delete '"+name+"' ?", function () {
            obj.submit();
        });
    }

    $(document).ready(function() {
        $("#drawer_tag").click(function() {
            $("#add_div").slideToggle(200, "linear", function() {
                if($("#add_div").css("display") == "none") {
                    $(".div_fade").css("display", "none");
                    $("#drawer_tag").removeClass("drawer_tag_open");
                    $("#drawer_tag").text("Add");
                } else {
                    $(".div_fade").css("display", "block");
                    $("#drawer_tag").addClass("drawer_tag_open");
                    $("#drawer_tag").text("Close");
                }
            });
        });
        $(".div_fade").click(function(){
            $("#add_div").slideToggle(200, "linear");
            $(".div_fade").css("display", "none")
            $("#drawer_tag").removeClass("drawer_tag_open");
            $("#drawer_tag").text("Add");
        });
    });
</script>
