<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../includes/set-language.php";

if (isset($_POST["databaseServer"]) && isset($_POST["databaseName"]) && isset($_POST["databaseUser"]) && isset($_POST["databasePassword"]) && isset($_POST["firebaseServerKey"]) && isset($_POST["firebaseSenderId"])) {
    array_walk($_POST, 'trimByReference');

    $dbServer = $_POST["databaseServer"];
    $dbName = $_POST["databaseName"];
    $dbUser = $_POST["databaseUser"];
    $dbPassword = $_POST["databasePassword"];
    $firebaseServerKey = $_POST["firebaseServerKey"];
    $firebaseSenderId = $_POST["firebaseSenderId"];

    try {
        ob_start();
        $conn = new MysqliDb($dbServer, $dbUser, $dbPassword, $dbName);
        $conn->connect();
        $conn->startTransaction();
        require_once __DIR__ . "/migrations.php";
        $conn->multi_query($query);
        $secreteKey = random_str(24);
        $config = "<?php
define('DB_SERVER', '{$dbServer}');
define('DB_USER', '{$dbUser}');
define('DB_PASS', '{$dbPassword}');
define('DB_NAME', '{$dbName}');
define('SERVER_KEY', '{$firebaseServerKey}');
define('SENDER_ID', '{$firebaseSenderId}');
define('TIMEZONE', '{$_POST["timezone"]}');
define('APP_SECRET_KEY', '{$secreteKey}');
define('APP_SESSION_NAME', 'SMS_GATEWAY');
define('PURCHASE_CODE', '');
";
        if (file_put_contents(__DIR__ . '/../config.php', $config)) {
            date_default_timezone_set($_POST["timezone"]);
            $user = new User();
            $user->setEmail(trim($_POST["email"]));
            $user->setName(trim($_POST["name"]));
            $user->setPassword($_POST["password"]);
            $user->setApiKey(generateAPIKey());
            $user->setDateAdded(date('Y-m-d H:i:s'));
            $user->setIsAdmin(true);
            $user->save();
            if (file_exists(__DIR__ . "/../upgrade.php")) {
                if (!unlink(__DIR__ . "/../upgrade.php")) {
                    throw new Exception(__("error_removing_upgrade_script", ["type" => "Installation"]));
                }
            }
            $conn->commit();
            echo json_encode([
                'result' => __("success_installation")
            ]);
        } else {
            throw new Exception(__("error_creating_config"));
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo json_encode([
            'error' => $e->getMessage()
        ]);
    }
}