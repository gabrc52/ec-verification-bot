<?php

/// Debug (TODO: remove)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/// Constants
require "constants.php";

/// Utilities (sending POST requests for now)
require "util.php";

/// Hardcoded for now
$server = '1049418013323055124';
$role = '1050870099667603466';

if (isset($_SERVER['SSL_CLIENT_S_DN_Email'])) {
    print('You have a kerb: kerb verification will be added on May 1 for 2027s. If you are not a 2027, you should get verified by the admins instead.');
}

// SQL stuff
$connection = mysqli_connect(SQL_HOST, SQL_USERNAME, SQL_PASSWORD, SQL_DB);

require_once "php-discord-sdk/support/sdk-discord.php";
$discord = new DiscordSDK();
$discord->SetAccessInfo("Bot", TOKEN);

?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="verify.css">
</head>
<body>
    <div id="main">

<?php

/// Authenticate Discord member (make sure they came from clicking the link, and therefore own the account)
$member = intval($_REQUEST['id']);
$toHash = PEPPER.":$member";
$hash = hash('sha256', $toHash);
if (!isset($_REQUEST['auth'])) {
	die('Internal error: No auth!');
}
$expectedHash = $_REQUEST['auth'];
if ($hash !== $expectedHash) {
	die('Internal error: Could not verify that you own the Discord account you\'re trying to verify!');
}

?>
        <h1>One more thing!</h1>
        <p>Is the name we have on file correct?</p>
        <h2 id="usr_name">Tim A Beaver</h2> <!--TODO: make dynamic-->
        <div id="buttons">
            <a class="button" href="" id="btn_yes">Yes!</a></li> <!--TODO: add link-->
            <a class="button" href="" id="btn_no">No, let me correct it</a> <!--TODO: add link-->
        </div>
    </div>
</body>
</html>


