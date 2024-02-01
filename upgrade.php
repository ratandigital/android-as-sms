<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/vendor/autoload.php";

$db = MysqliDb::getInstance();

/**
 * @param string $query
 * @throws Exception
 */
function queryDb($query)
{
    global $db;
    $db->rawQuery($query);
    if ($db->getLastErrno()) {
        throw new Exception($db->getLastError());
    }
}

try {
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM User LIKE 'reportDelivery'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE User ADD COLUMN reportDelivery tinyint(1) NOT NULL DEFAULT '0'");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Message LIKE 'simSlot'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Message ADD COLUMN simSlot int(11) DEFAULT NULL");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Message LIKE 'resultCode'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Message ADD COLUMN resultCode int(11) DEFAULT NULL");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Message LIKE 'errorCode'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Message ADD COLUMN errorCode int(11) DEFAULT NULL");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Message LIKE 'schedule'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Message ADD COLUMN schedule bigint(20) DEFAULT NULL");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Device LIKE 'androidVersion'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Device ADD COLUMN androidVersion varchar(255) DEFAULT NULL");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Device LIKE 'appVersion'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Device ADD COLUMN appVersion varchar(255) DEFAULT NULL");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM User LIKE 'credits'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE User ADD COLUMN credits int(11) DEFAULT NULL");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM User LIKE 'autoRetry'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE User ADD COLUMN autoRetry tinyint(1) NOT NULL DEFAULT '0'");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM User LIKE 'language'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE User ADD COLUMN language varchar(255) NOT NULL DEFAULT 'english'");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM User LIKE 'expiryDate'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE User ADD COLUMN expiryDate datetime DEFAULT NULL");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Message LIKE 'expiryDate'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Message ADD COLUMN expiryDate datetime DEFAULT NULL");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Message LIKE 'retries'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Message ADD COLUMN retries int(11) NOT NULL DEFAULT '0'");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM User LIKE 'contactsLimit'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE User ADD COLUMN contactsLimit int(11) DEFAULT NULL");
        queryDb("ALTER TABLE User MODIFY devicesLimit int(11) DEFAULT NULL");
        User::where("devicesLimit", 0)->update_all(["devicesLimit" => null]);
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Message LIKE 'attachments'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Message ADD COLUMN attachments text DEFAULT NULL");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Message LIKE 'type'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Message ADD COLUMN type enum('sms','mms') NOT NULL DEFAULT 'sms'");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM User LIKE 'smsToEmail'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE User ADD COLUMN smsToEmail tinyint(1) NOT NULL DEFAULT '0'");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM User LIKE 'receivedSmsEmail'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE User ADD COLUMN receivedSmsEmail varchar(255) DEFAULT NULL");
    }
    queryDb("ALTER TABLE User MODIFY delay varchar(7) NOT NULL DEFAULT '2'");
    try {
        queryDb("ALTER TABLE `Message` ADD INDEX `groupID_index` (`groupID`)");
    } catch (Exception $e) {
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Message LIKE 'prioritize'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Message ADD COLUMN prioritize tinyint(1) NOT NULL DEFAULT '0'");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM User LIKE 'sleepTime'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE User ADD COLUMN `sleepTime` varchar(255) DEFAULT NULL");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Device LIKE 'name'");
    if (mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Device DROP COLUMN name");
        queryDb("ALTER TABLE Device ADD CONSTRAINT UNIQUE KEY androidUserID (androidID, userID)");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM User LIKE 'useProgressiveQueue'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE User ADD COLUMN `useProgressiveQueue` tinyint(1) NOT NULL DEFAULT '1'");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Device LIKE 'sharedToAll'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Device ADD COLUMN `sharedToAll` tinyint(1) NOT NULL DEFAULT '0'");
        queryDb("ALTER TABLE Setting MODIFY value text DEFAULT NULL");
    }
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Device LIKE 'useOwnerSettings'");
    if (!mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Device ADD COLUMN `useOwnerSettings` tinyint(1) NOT NULL DEFAULT '1'");
    }
    queryDb("CREATE TABLE IF NOT EXISTS `Blacklist` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `number` varchar(16) NOT NULL,
  `userID` int(11) NOT NULL,
  FOREIGN KEY (`userID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  UNIQUE KEY `numberUserID` (`number`, `userID`)
) ENGINE=InnoDB;");
    queryDb("CREATE TABLE IF NOT EXISTS `DeviceUser` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) DEFAULT NULL,
  `deviceID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  FOREIGN KEY (`userID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`deviceID`) REFERENCES `Device` (`ID`) ON DELETE CASCADE,
  UNIQUE KEY `deviceUserID` (`deviceID`, `userID`)
) ENGINE=InnoDB;");
    $devices = Device::read_all();
    $deviceUsers = [];
    foreach ($devices as $device) {
        $deviceUsers[] = [
            "deviceID" => $device->getID(),
            "userID" => $device->getUserID()
        ];
    }
    DeviceUser::insertMultiple($deviceUsers, ["active" => 1]);
    queryDb("CREATE TABLE IF NOT EXISTS `Template` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `userID` int(11) NOT NULL,
  FOREIGN KEY (`userID`) REFERENCES `User` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB;");
    queryDb("DROP TABLE IF EXISTS Job");
    queryDb("CREATE TABLE IF NOT EXISTS `Job` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `functionName` varchar(255) NOT NULL,
  `arguments` text NOT NULL,
  `shouldLock` tinyint(1) NOT NULL DEFAULT '0',
  `lockName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB;");

    queryDb("CREATE TABLE IF NOT EXISTS `Ussd` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `request` varchar(255) NOT NULL,
  `response` text DEFAULT NULL,
  `userID` int(11) NOT NULL,
  `deviceID` int(11) DEFAULT NULL,
  `simSlot` int(11) DEFAULT NULL,
  `sentDate` datetime NOT NULL,
  `responseDate` datetime DEFAULT NULL,
  FOREIGN KEY (`userID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`deviceID`) REFERENCES `Device` (`ID`) ON DELETE SET NULL
) ENGINE=InnoDB;");

    queryDb("CREATE TABLE IF NOT EXISTS Sim (ID int(11) AUTO_INCREMENT PRIMARY KEY,name varchar(255) DEFAULT NULL,number varchar(255) DEFAULT NULL,carrier varchar(255) DEFAULT NULL,country varchar(255) DEFAULT NULL,iccID varchar(255) DEFAULT NULL,slot int(11) NOT NULL,enabled tinyint(1) NOT NULL,deviceID int(11) NOT NULL,FOREIGN KEY (deviceID) REFERENCES Device(ID) ON DELETE CASCADE)ENGINE=InnoDB;");
    queryDb("CREATE TABLE IF NOT EXISTS ContactsList (ID int(11) AUTO_INCREMENT PRIMARY KEY,name varchar(255) NOT NULL,userID int(11) NOT NULL,FOREIGN KEY (userID) REFERENCES User(ID) ON DELETE CASCADE)ENGINE=InnoDB;");
    queryDb("CREATE TABLE IF NOT EXISTS Contact (ID int(11) AUTO_INCREMENT PRIMARY KEY,name varchar(255) DEFAULT NULL,number varchar(255) NOT NULL,subscribed tinyint(1) NOT NULL DEFAULT '1',contactsListID int(11) NOT NULL,FOREIGN KEY (contactsListID) REFERENCES ContactsList(ID) ON DELETE CASCADE)ENGINE=InnoDB;");
    queryDb("CREATE TABLE IF NOT EXISTS Setting (ID int(11) AUTO_INCREMENT PRIMARY KEY,name varchar(255) NOT NULL,value text NOT NULL)ENGINE=InnoDB;");
    queryDb("CREATE TABLE IF NOT EXISTS Response (ID int(11) AUTO_INCREMENT PRIMARY KEY,message text NOT NULL,response text NOT NULL,matchType tinyint(1) NOT NULL DEFAULT '0',enabled tinyint(1) NOT NULL DEFAULT '1',userID int(11) NOT NULL,FOREIGN KEY (userID) REFERENCES User(ID) ON DELETE CASCADE)ENGINE=InnoDB;");
    $result = mysqli_query($db->mysqli(), "SHOW COLUMNS FROM Contact LIKE 'token'");
    if (mysqli_num_rows($result)) {
        queryDb("ALTER TABLE Contact DROP COLUMN token");
    }

    queryDb("CREATE TABLE IF NOT EXISTS `Plan` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `credits` int(11) DEFAULT NULL,
  `devices` int(11) DEFAULT NULL,
  `contacts` int(11) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `currency` VARCHAR(255) NOT NULL,
  `frequency` int(11) NOT NULL,
  `frequencyUnit` VARCHAR(255) NOT NULL,
  `totalCycles` int(11) NOT NULL DEFAULT '0',
  `paypalPlanID` VARCHAR(255) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  UNIQUE KEY `paypalPlanID` (`paypalPlanID`)
) ENGINE=InnoDB;");
    queryDb("CREATE TABLE IF NOT EXISTS `Subscription` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `subscribedDate` datetime NOT NULL,
  `expiryDate` datetime NOT NULL,
  `cyclesCompleted` int(11) NOT NULL,
  `status` VARCHAR(255) NOT NULL,
  `paymentMethod` VARCHAR(255) NOT NULL,
  `subscriptionID` VARCHAR(255) DEFAULT NULL,
  `planID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  UNIQUE KEY `subscriptionID` (`subscriptionID`),
  FOREIGN KEY (`planID`) REFERENCES `Plan` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`userID`) REFERENCES `User` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB;");
    queryDb("CREATE TABLE IF NOT EXISTS `Payment` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `status` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL,
  `transactionFee` int(11) NOT NULL,
  `currency` VARCHAR(255) NOT NULL,
  `dateAdded` datetime NOT NULL,
  `userID` int(11) NOT NULL,
  `subscriptionID` int(11) NOT NULL,
  `transactionID` VARCHAR(255) NOT NULL,
  UNIQUE KEY `transactionID` (`transactionID`),
  FOREIGN KEY (`userID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`subscriptionID`) REFERENCES `Subscription` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB;");

    if (Setting::get("smtp_enabled") === "") {
        $config = [
            "smtp_enabled" => defined("SMTP_HOST") ? "1" : "0",
            "smtp_hostname" => defined("SMTP_HOST") ? SMTP_HOST : '',
            "smtp_port" => defined("SMTP_PORT") ? SMTP_PORT : '',
            "smtp_encryption" => defined("SMTP_SECURE") ? SMTP_SECURE : '',
            "smtp_username" => defined("SMTP_USERNAME") ? SMTP_USERNAME : '',
            "smtp_password" => defined("SMTP_PASSWORD") ? SMTP_PASSWORD : '',
            "recaptcha_enabled" => defined("RECAPTCHA_SECRET_KEY") ? "1" : "0",
            "recaptcha_secret_key" => defined("RECAPTCHA_SECRET_KEY") ? RECAPTCHA_SECRET_KEY : '',
            "recaptcha_site_key" => defined("RECAPTCHA_SITE_KEY") ? RECAPTCHA_SITE_KEY : '',
            "registration_enabled" => 1,
            "default_delay" => defined("DELAY") ? DELAY : "2"
        ];

        Setting::apply($config);
    }

    if (!defined("APP_SECRET_KEY")) {
        $secretKey = random_str(24);
        $data = "
define('APP_SECRET_KEY', '{$secretKey}');
define('APP_SESSION_NAME', 'SMS_GATEWAY');";
        if (file_put_contents("config.php", $data, FILE_APPEND) === false) {
            throw new Exception(__("error_updating_config"));
        }
    }
    if (!unlink(__FILE__)) {
        throw new Exception(__("error_removing_upgrade_script", ["type" => "Upgrade"]));
    }
    echo __("success_update", ["version" => __("application_version")]);
} catch (Exception $e) {
    echo $e->getMessage();
}