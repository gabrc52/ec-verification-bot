<?php

/// Debug (TODO: remove)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/// Constants
require "constants.php";

/// Hardcoded for now
$baseurl = 'https://ec.mit.edu/';
$server = '1141927141248880640';
$role = '1141929891948925038';
$role_resident = '1141929476154990732';

/// Move GET parameters to a cookie.
/// A better alternative would be to use `state` but I'm lazy and copying from Swolen't Tim as is
if (!isset($_SERVER['SSL_CLIENT_S_DN_Email']) && (isset($_GET['id']) || isset($_GET['auth']))) {
    /// If authenticating using oidc, don't use 444 port so it matches the oauth redirect URL
    if ($_SERVER['SERVER_PORT'] == 444) {
        header("Location: $baseurl$_SERVER[REQUEST_URI]");
        die();
    }
    /// I'm checking for cert here so using cert authentication doesn't require having cookies enabled
    if (isset($_GET['id'])) {
        setcookie('id', $_GET['id']);
    }
    if (isset($_GET['auth'])) {
        setcookie('auth', $_GET['auth']);
    }
    header("Location: $baseurl".INSTANCE.".php");
    die();
}

// SQL stuff
$connection = mysqli_connect(SQL_HOST, SQL_USERNAME, SQL_PASSWORD, SQL_DB);

/// Utility functions
require "util.php";

require_once "php-discord-sdk/support/sdk_discord.php";
$discord = new DiscordSDK();
$discord->SetAccessInfo("Bot", TOKEN);

if (isset($_REQUEST['email'])) {
    global $email;
    $email = $_REQUEST['email'];
}

/// Get user email
if (isset($_SERVER['SSL_CLIENT_S_DN_Email'])) {
    /// Pure cert authentication (preferred)
    $email = $_SERVER['SSL_CLIENT_S_DN_Email'];

} else if (isset($_GET['code'])) {
    /// OAuth authentication
    $tokenstuff = post('https://oidc.mit.edu/token', array(
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => '$baseurl'.INSTANCE.'.php',
        'client_id' => OAUTH_ID,
        'client_secret' => OAUTH_SECRET
    ));
    if (!$tokenstuff) {
        /// If unable to get a token, try again
        header("Location: $baseurl".INSTANCE.".php");
        die();
    }
    $tokenstuff = json_decode($tokenstuff, true);
    $token = $tokenstuff['access_token'];
    // https://openid.net/specs/openid-connect-basic-1_0.html#UserInfoRequest
    $userinfo = post('https://oidc.mit.edu/userinfo', array(
        'access_token' => $token
    ));
    $userinfo = json_decode($userinfo, true);
    $email = $userinfo['email'];

} else {
    /// If cert doesn't work, fallback to OAuth
    header("Location: $baseurlredirect.php?instance=".INSTANCE);
    die();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Discord verification</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans&amp;display=swap" rel="stylesheet">
    <!-- Credit to jt for the styling -->
    <link rel="stylesheet" href="verify.css">
</head>

<body>
    <div id="main">

<?php
/// Extract kerb from email
$email = strtolower($email);
if (substr($email, -8) != "@mit.edu") {
    die("Given email is $email, which is not an MIT email! If you need help, contact Discord staff.");
} 
$kerb = substr($email, 0, -8);

/// Authenticate Discord member (make sure they came from clicking the link, and therefore own the account)
authenticate(intval($_REQUEST['id']), $_REQUEST['auth'], 'Discord');

$id = $_REQUEST['id'];
if (hasDiscordAccount($connection, $email) != $id) {
    die('You already have a Discord account associated with this email address. Please contact Discord staff at ec-discord@mit.edu.');
}
updateRecord($connection, $email, $_REQUEST['name'], $id);
giveRole($server, $id, $role);
echo "<p>You have been successfully verified and you can now use the server!</p>";

if (isMemberOfList($kerb, 'ec-residents')) {
    giveRole($server, $id, $role);
    echo "<p>You have also been granted the resident role!</p>";
}

?>
</div>
</body>
</html>