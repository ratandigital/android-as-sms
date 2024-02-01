<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../vendor/autoload.php";

date_default_timezone_set(TIMEZONE);

if (isset($_POST["groupId"])) {
    try {
        MysqliDb::getInstance()->startTransaction();
        if (empty($_POST["limit"])) {
            $messages = Message::where("groupId", $_POST["groupId"])->where("status", "Pending")->read_all(false);
            Message::where("groupId", $_POST["groupId"])->where("status", "Pending")->update_all(["status" => "Queued", "deliveredDate" => date("Y-m-d H:i:s")]);
        } else {
            Message::setPageLimit($_POST["limit"]);
            $messages = Message::where("groupId", $_POST["groupId"])->where("status", "Pending")->read_all(false, 1);
            $totalCount = Message::getTotalCount();
            $ids = [];
            foreach ($messages as $message) {
                $ids[] = $message->getID();
            }
            if ($ids) {
                Message::where("ID", $ids, "IN")->update_all(["status" => "Queued", "deliveredDate" => date("Y-m-d H:i:s")]);
            }
        }
        MysqliDb::getInstance()->commit();
        if (!empty($messages) && Setting::get("pusher_enabled")) {
            $user = new User();
            $user->setID($messages[0]->getUserID());
            if ($user->read()) {
                try {
                    $where = $user->getIsAdmin() ? "" : " WHERE Message.userID = {$user->getID()}";
                    $query = "SELECT COUNT(IF(Message.status = 'Scheduled', 1, NULL)) as totalScheduled, COUNT(IF(Message.status = 'Pending', 1, NULL)) as totalPending, COUNT(IF(Message.status = 'Queued', 1, NULL)) as totalQueued, deviceID FROM Message" . $where . " GROUP BY deviceID";
                    $counts = MysqliDb::getInstance()->rawQuery($query);
                    sendPusherNotification("user-{$user->getApiKey()}", "messages-queued", $counts);
                } catch (Exception $e) {
                    error_log($e->getMessage());
                }
            }
        }
        $response =
            [
                "success" => true,
                "data" => [
                    "messages" => $messages,
                    "totalCount" => $totalCount ?? count($messages)
                ],
                "error" => null
            ];
        echo json_encode($response);
    } catch (Throwable $t) {
        $response = ["success" => false, "data" => null, "error" => ["code" => 500, "message" => $t->getMessage()]];
    }
}