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
?>
    <script>
        window.parent.location.href = window.parent.location.href;
    </script>
<?php
exit();
}
$_SESSION["last_activity"] = time();

$result = UserTable::get_user_details($_SESSION["username"]);
$row = $result->fetch_assoc();
?>

<div class="div_card edit">
    <h4>Edit Credentials</h4>
    <form action="user_account.php" method="post">
        <div>
            <label id="label_user_name">User Name<input class="userinput" name="update_user" type="text" value="<?php echo $row["username"] ?>" placeholder="User Name"></label>
            <label>First Name<input class="userinput" name="update_first" type="text" value="<?php echo $row["first_name"] ?>" placeholder="First Name"></label>
            <label>Last Name<input class="userinput" name="update_last" type="text" value="<?php echo $row["last_name"] ?>" placeholder="Last Name"></label>
        </div>
        <div>
            <label>Time zone<input class="userinput" type="text" value="<?php echo $_SESSION['timezone'] ?>" readonly>
            </label>
            <div class="inline none">
                <select class="user_select" name="region_select" id="region_select" onchange=onTimeZoneSelect(this)>
                    <?php $oldregion = ""; ?>
                    <?php foreach (timezone_identifiers_list() as $tz): ?>
                        <?php $region = explode("/", $tz); ?>
                        <?php $removetz = array('Pacific', 'Antarctica', 'Arctic', 'UTC', 'Indian', 'Atlantic', $oldregion); ?>
                        <?php if (!in_array($region[0], $removetz, true)): ?>
                                <option value="<?php echo $region[0] ?>"> <?php echo $region[0] ?></option>
                        <?php endif ?>
                    <?php $oldregion= $region[0]; endforeach ?>
                </select>
                <select class="user_select" name="city_select" id="city_select"></select>
            </div>
        </div>
        <div>
            <label for="update_timeout">Session Timeout</label>
            <select class="user_select" name="update_timeout" >
                <option value="15" <?php echo $row["time_out"] == 15? "selected" : "" ?>>15 mins</option>
                <option value="30" <?php echo $row["time_out"] == 30? "selected" : "" ?>>30 mins</option>
                <option value="60" <?php echo $row["time_out"] == 60? "selected" : "" ?>>60 mins</option>
                <option value="120" <?php echo $row["time_out"] == 120? "selected" : "" ?>>120 mins</option>
                <option value="150" <?php echo $row["time_out"] == 150? "selected" : "" ?>>150 mins</option>
                <option value="300" <?php echo $row["time_out"] == 300? "selected" : "" ?>>300 mins</option>
            </select>
        </div>
        <div>
            <input class="button" type="submit" value="Save">
        </div>
    </form>
</div>

<div class="div_card">
    <h4>Edit Password</h4>
    <div>
        <form action="user_account.php" method="post">
            <label>Current Password<input class="userinput password_view" type="password" id="current_password" name="current_password" oninput= verifyCurrentPassword() required ></label><br/>
            <label>New Password<input class="userinput password_view" type="password" id="new_password" name="new_password" oninput= verifyNewPassword() required></label><br/>
            <label>Retype Password<input class="userinput password_view" type="password" id="retype_password" name="retype_password" oninput= verifyNewPassword() required></label><br/>
            <div>
                <input class="button" type="submit" id="submit_password" name="submit_password" value="save" disabled>
            </div>
            <input type="hidden" id="value_user_name" name="user_name" value="<?php echo $_SESSION['username'] ?>">
        </form>
    </div>
</div>

<script type="text/javascript" src="//code.jquery.com/jquery-2.2.0.min.js"></script>
<script>
    var verified;
    var newPass;
    var submit_pass = document.getElementById("submit_password");

    function verifyCurrentPassword(){
        var current_pass = document.getElementById("current_password");
        var userName = document.getElementById("value_user_name").value;

        $(function(){
            $.post("jq_ajax.php", {userName: userName, password: current_pass.value}, function(data,status){
               verified = data;
               style(data);
            });
        });
        function style(ver){
        if (ver == "true") {
            current_pass.style.backgroundColor= "PaleGreen";
        } else if (ver == "false") {
            current_pass.style.backgroundColor= "Tomato";
        }
        if (current_pass.value == "") {current_pass.style.backgroundColor= "white";}

        if (ver == "true" && newPass == "true") {
            submit_pass.disabled = false;
        } else {submit_pass.disabled = true;}}
    }

    function verifyNewPassword(){
        var new_pass = document.getElementById("new_password");
        var retype_pass = document.getElementById("retype_password");

        if (new_pass.value != retype_pass.value && retype_pass.value != "" && new_pass.value != "") {
            new_pass.style.backgroundColor = "Tomato ";
            retype_pass.style.backgroundColor = "Tomato ";
            newPass = "false";
        } else if (new_pass.value == retype_pass.value && new_pass.value != "") {
            new_pass.style.backgroundColor = "PaleGreen";
            retype_pass.style.backgroundColor = "PaleGreen";
            newPass = "true";
        } else {
            new_pass.style.backgroundColor = "white";
            retype_pass.style.backgroundColor = "white";
        }
        if (verified == "true" && newPass == "true") {
            submit_pass.disabled = false;
        } else {submit_pass.disabled = true;}
    }

     function onTimeZoneSelect(obj){
        var region = obj.value;

         $(function(){
            $.post("jq_ajax.php", {timeZoneRegion: region}, function(data,status){
                 document.getElementById("city_select").innerHTML = data;
            });
        });
    }

</script>