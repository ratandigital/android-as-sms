<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../vendor/autoload.php";

date_default_timezone_set(TIMEZONE);

try {
    if (isset($_POST["messages"])) {
        $messages = json_decode($_POST["messages"], true);
        if (is_array($messages) && count($messages) > 0) {
            MysqliDb::getInstance()->startTransaction();
            foreach ($messages as $message) {
                if (isset($message["ID"]) && isset($message["status"])) {
                    $obj = new Message();
                    $obj->setID($message["ID"]);
                    $obj->setStatus($message["status"]);
                    if (isset($message["deliveredDate"])) {
                        $time = new DateTime($message["deliveredDate"]);
                        $time->setTimezone(new DateTimeZone(TIMEZONE));
                        $obj->setDeliveredDate($time->format("Y-m-d H:i:s"));
                    }
                    if (isset($message["resultCode"])) {
                        $obj->setResultCode($message["resultCode"]);
                    }
                    if (isset($message["errorCode"])) {
                        $obj->setErrorCode($message["errorCode"]);
                    }
                    if (array_key_exists("simSlot", $message)) {
                        $obj->setSimSlot($message["simSlot"]);
                    }
                    $obj->save();
                } else {
                    throw new Exception(__("error_invalid_request_format"));
                }
            }
            MysqliDb::getInstance()->commit();

            if (Setting::get("pusher_enabled")) {
                $message = new Message();
                $message->setID($messages[0]["ID"]);
                if ($message->read()) {
                    $user = new User();
                    $user->setID($message->getUserID());
                    if ($user->read()) {
                        try {
                            $where = $user->getIsAdmin() ? "" : " WHERE Message.userID = {$user->getID()}";
                            $query = "SELECT  COUNT(IF(Message.status = 'Pending', 1, NULL)) as totalPending, COUNT(IF(Message.status = 'Sent', 1, NULL)) as totalSent, COUNT(IF(Message.status = 'Delivered', 1, NULL)) as totalDelivered, COUNT(IF(Message.status = 'Failed', 1, NULL)) as totalFailed, COUNT(IF(Message.status = 'Queued', 1, NULL)) as totalQueued, deviceID FROM Message" . $where . " GROUP BY deviceID";
                            $counts = MysqliDb::getInstance()->rawQuery($query);
                            sendPusherNotification("user-{$user->getApiKey()}", "status-updated", $counts);
                        } catch (Exception $e) {
                            error_log($e->getMessage());
                        }
                    }
                }
            }
        } else {
            throw new Exception(__("error_invalid_request_format"));
        }
        echo json_encode(["success" => true, "data" => null, "error" => null]);
    }
} catch (Throwable $t) {
    echo json_encode(["success" => false, "data" => null, "error" => ["code" => 500, "message" => $t->getMessage()]]);
}