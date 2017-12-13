<?php
session_start();
require_once "database/contacts_table.php";

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

if (isset($_POST["new_name"])) {
    try {
        ContactsTable::add_new_contact($_POST['new_name'], $_POST['new_email']);
    } catch (Exception $e) {
        echo '<div class="error">'.$e->getMessage().'</div>';
    }
}

if(isset($_POST["delete_contact"])){
    ContactsTable::delete_contact($_POST["delete_contact"]);
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
            <h4>Add New Contact</h4>
            <form action="contacts.php" method="post">
                <div class="inline">
                    <label for="new_name">Name</label>
                    <input class="userinput" type="text" name="new_name" placeholder="Name" required>
                    <label for="new_email">Email</label>
                    <input class="userinput" type="email" name="new_email" placeholder="Email" required>
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
                <th>Email</th>
                <th id="th_edit">Edit</th>
                <th id="th_delete">Delete</th>
            </tr>
            <?php $result = ContactsTable::get_contacts(); ?>
            <?php while ($contact = $result->fetch_assoc()): ?>
                <tr>
                    <td ><input type="text" id="name" value="<?php echo $contact['name']; ?>" readonly></td>
                    <td ><input type="email" id="email" value="<?php echo $contact['email']; ?>" readonly></td>
                    <td id="edit"><span class="fa-edit"></span></td>
                    <td id="delete">
                        <form action="contacts.php" method="post" onclick=deleteContact(this)>
                            <input type="hidden" id="contact_id" name="delete_contact" value="<?php echo $contact["id"] ?>">
                            <span class="entypo-trash"></span>
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
    function deleteContact(obj) {
        var name = $(obj).parents("tr").find("#name").val();
        alertify.confirm("Delete '"+name+"' ?", function () {
            obj.submit();
        });
    }

    function updateContact(obj) {
        var id = $(obj).parents("tr").find("#contact_id").val();
        var name = $(obj).parents("tr").find("#name").val();
        var email = $(obj).parents("tr").find("#email").val();

        $.post("jq_ajax.php", {updateContactDetails: "", id : id, name: name, email: email}, function(data){
            if (data) {
                 alertify
                    .delay(2000)
                    .success("Changes Saved");
            }
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
        
        $(".fa-edit").click(function() {
            if ($(this).parents("tr").find("input").attr("readonly") == "readonly") {
                $(this).parents("tr").find("input").attr("readonly", false);
                $(this).parents("tr").find("input:first").focus();
                $(this).removeClass("fa-edit");
                $(this).addClass("fa-save selected");
            } else {
                $(this).parents("tr").find("input").attr("readonly", true);
                $(this).removeClass("fa-save selected");
                $(this).addClass("fa-edit");
                updateContact($(this)[0]);
            }
        });
    });
</script>
