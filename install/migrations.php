<?php

$query = "CREATE TABLE IF NOT EXISTS `User` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(70) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `apiKey` char(40) NOT NULL,
  `isAdmin` tinyint(1) NOT NULL DEFAULT '0',
  `primaryDeviceID` int(11) DEFAULT '0',
  `dateAdded` datetime NOT NULL,
  `lastLogin` datetime DEFAULT NULL,
  `delay` varchar(7) NOT NULL DEFAULT '2',
  `webHook` varchar(255) DEFAULT NULL,
  `devicesLimit` int(11) DEFAULT NULL,
  `contactsLimit` int(11) DEFAULT NULL,
  `credits` int(11) DEFAULT NULL,
  `expiryDate` datetime DEFAULT NULL,
  `timeZone` varchar(255) NOT NULL DEFAULT '{$_POST["timezone"]}',
  `reportDelivery` tinyint(1) NOT NULL DEFAULT '0',
  `autoRetry` tinyint(1) NOT NULL DEFAULT '0',
  `smsToEmail` tinyint(1) NOT NULL DEFAULT '0',
  `useProgressiveQueue` tinyint(1) NOT NULL DEFAULT '1',
  `receivedSmsEmail` varchar(255) DEFAULT NULL,
  `sleepTime` varchar(255) DEFAULT NULL,
  `language` varchar(255) NOT NULL DEFAULT 'english',
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `apiKey` (`apiKey`)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `Device` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `token` varchar(255) DEFAULT NULL,
  `model` varchar(255) NOT NULL,
  `androidVersion` varchar(255) DEFAULT NULL,
  `appVersion` varchar(255) DEFAULT NULL,
  `userID` int(11) NOT NULL,
  `androidID` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `sharedToAll` tinyint(1) NOT NULL DEFAULT '0',
  `useOwnerSettings` tinyint(1) NOT NULL DEFAULT '1',
  FOREIGN KEY (`userID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  UNIQUE KEY `androidUserID` (`androidID`, `userID`)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `Sim` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `carrier` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `iccID` varchar(255) DEFAULT NULL,
  `slot` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `deviceID` int(11) NOT NULL,
  FOREIGN KEY (`deviceID`) REFERENCES `Device` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `Message` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `number` varchar(16) NOT NULL,
  `message` text NOT NULL,
  `schedule` bigint(20) DEFAULT NULL,
  `sentDate` datetime NOT NULL,
  `deliveredDate` datetime DEFAULT NULL,
  `expiryDate` datetime DEFAULT NULL,
  `status` varchar(10) NOT NULL,
  `resultCode` int(11) DEFAULT NULL,
  `errorCode` int(11) DEFAULT NULL,
  `retries` int(11) NOT NULL DEFAULT '0',
  `userID` int(11) NOT NULL,
  `deviceID` int(11) DEFAULT NULL,
  `simSlot` int(11) DEFAULT NULL,
  `groupID` varchar(255) DEFAULT NULL,
  `type` enum('sms','mms') NOT NULL DEFAULT 'sms',
  `attachments` text DEFAULT NULL,
  `prioritize` tinyint(1) NOT NULL DEFAULT '0',
  FOREIGN KEY (`userID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`deviceID`) REFERENCES `Device` (`ID`) ON DELETE SET NULL,
  INDEX `groupID_index` (`groupID`)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `ContactsList` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `userID` int(11) NOT NULL,
  FOREIGN KEY (`userID`) REFERENCES `User` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `Contact` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) DEFAULT NULL,
  `number` varchar(255) NOT NULL,
  `subscribed` tinyint(1) NOT NULL DEFAULT '1',
  `contactsListID` int(11) NOT NULL,
  FOREIGN KEY (`contactsListID`) REFERENCES `ContactsList` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `Setting` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `value` text DEFAULT NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `Response` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `message` text NOT NULL,
  `response` text NOT NULL,
  `matchType` tinyint(1) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `userID` int(11) NOT NULL,
  FOREIGN KEY (`userID`) REFERENCES `User` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `Template` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `userID` int(11) NOT NULL,
  FOREIGN KEY (`userID`) REFERENCES `User` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `Job` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `functionName` varchar(255) NOT NULL,
  `arguments` text NOT NULL,
  `lockName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `Plan` (
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
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `Subscription` (
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
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `Payment` (
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
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `Ussd` (
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
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `DeviceUser` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) DEFAULT NULL,
  `deviceID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  FOREIGN KEY (`userID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`deviceID`) REFERENCES `Device` (`ID`) ON DELETE CASCADE,
  UNIQUE KEY `deviceUserID` (`deviceID`, `userID`)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `Blacklist` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `number` varchar(16) NOT NULL,
  `userID` int(11) NOT NULL,
  FOREIGN KEY (`userID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  UNIQUE KEY `numberUserID` (`number`, `userID`)
) ENGINE=InnoDB;";
