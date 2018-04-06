<?php
session_start();
require_once "database/user_table.php";
require_once "database/conversation_table.php";

if (isset($_SESSION["username"])) {
    header("Location: category_status.php");
    exit();
}
if (isset($_POST["username"])) {
    try {
        if (UserTable::verify_credentials($_POST["username"], $_POST["password"])) {
            UserTable::set_session_variables($_POST["username"]);
            ConversationTable::set_destroy_status($_SESSION["username"], gmdate("Y-m-d"));
            header("Location: category_status.php");
            exit();
        } else {
            echo '<div class="error">Incorrect username or password</div>';
        }
    } catch (Exception $e) {
            echo '<div class="error">'.$e->getMessage().'</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="body_login">
    <div class="div_login">
        <div class="div_block">
            <h3 style="color: #ccc;">Ims Kitchener</h3>
            <form action="login.php" method="post">
                <input class="userinput_login" type="text" name="username" placeholder="Username" autocapitalize="none" autofocus required>
                <input class="userinput_login" type="password" name="password" placeholder="Password" required>
                <input type="submit" value="Sign In" class="button_login">
            </form>
        </div>
    </div>
    <span class="version">v1.2.5</span>
</body>
</html>
