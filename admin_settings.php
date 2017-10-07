<?php 
session_start();
require_once "database/variables_table.php";

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
if (isset($_POST["history_select"])) {
    VariablesTable::update_history_edit($_POST["history_select"]);
    $_SESSION["history_limit"] = $_POST["history_select"].' days';
}
$_SESSION["last_activity"] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="main font_open_sans">
        <div class="div_card">
            <h4>Admin Settings</h4>
            <form action="admin_settings.php" method="post">
                <div>
                    <label>History Edit</label>
                    <select class="user_select" name="history_select" id="" onchange=this.form.submit()>
                    <?php $result = VariablesTable::get_history_edit() ?>
                        <option value="7" <?php echo $result == 7 ? "selected" : ""?>>1 week</option>
                        <option value="30" <?php echo $result == 30 ? "selected" : ""?>>1 month</option>
                        <option value="90" <?php echo $result == 90 ? "selected" : ""?>>3 months</option>
                    </select>
                </div>
            </form>
        </div>
    </div>
</body>
</html>