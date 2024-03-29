<?php
/**
 * @var User $logged_in_user
 */

use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\SheetInterface;

try {
    require_once __DIR__ . "/../includes/ajax_protect.php";
    require_once __DIR__ . "/../includes/login.php";

    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
        if (isset($_POST["devices"]) && is_array($_POST["devices"])) {
            require_once __DIR__ . "/../includes/read-excel.php";
            /** @var string $tempFilePath */
            $messages = array();
            $requiredColumns = 1;
            $attachments = $logged_in_user->upload("attachments");
            $attachments = count($attachments) > 0 ? implode(',', $attachments) : null;
            if (empty($_POST["message"])) {
                $requiredColumns = 2;
            }
            $reader = ReaderEntityFactory::createReaderFromFile($_FILES['file']['name']);
            $reader->open($tempFilePath);
            /** @var SheetInterface $sheet */
            $headers = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                /** @var Row $row */
                foreach ($sheet->getRowIterator() as $row) {
                    $columns = $row->toArray();
                    if (empty($headers)) {
                        $headers = $columns;
                        array_walk_recursive($headers, 'sanitizeByReference');
                    }
                    if (count($columns) >= $requiredColumns) {
                        $number = sanitize($columns[0]);
                        if (isValidMobileNumber($number)) {
                            if (empty($_POST["message"])) {
                                if (trim($columns[1]) !== '') {
                                    array_push($messages, array("number" => $number, "message" => $columns[1], "attachments" => $attachments, 'type' => $_POST["type"]));
                                }
                            } else {
                                $message = $_POST["message"];
                                for ($i = 0; $i < count($columns); $i++) {
                                    $currentColumn = sanitize($columns[$i]);
                                    $columnNumber = $i + 1;
                                    $header = $headers[$i];
                                    if (empty($currentColumn)) {
                                        $message = str_ireplace("%{$header}%", "", $message);
                                        $message = str_ireplace("%col-{$columnNumber}%", "", $message);
                                    } else {
                                        $message = str_ireplace("%{$header}%", $currentColumn, $message);
                                        $message = str_ireplace("%col-{$columnNumber}%", $currentColumn, $message);
                                    }
                                }
                                array_push($messages, array("number" => $number, "message" => $message, "attachments" => $attachments, 'type' => $_POST["type"]));
                            }
                        }
                    }
                    unset($columns);
                }
            }
            $reader->close();
            $messagesCount = count($messages);
            if ($messagesCount > 0) {
                $schedule = null;
                if (isset($_POST["schedule"])) {
                    $schedule = new DateTime($_POST["schedule"], new DateTimeZone($_SESSION["timeZone"]));
                    $schedule = $schedule->getTimestamp();
                }
                Message::sendMessages($messages, $logged_in_user, $_POST["devices"], $schedule);
                if (is_null($schedule)) {
                    $success = $messagesCount > 1 ? __("success_messages_sent", ["count" => $messagesCount]) : __("success_message_sent", ["count" => $messagesCount]);
                } else {
                    $success = $messagesCount > 1 ? __("success_messages_scheduled", ["count" => $messagesCount]) : __("success_message_scheduled", ["count" => $messagesCount]);
                }
                echo json_encode(array(
                    'result' => $success
                ));
            } else {
                throw new Exception(__("error_no_messages_found"));
            }
        } else {
            throw new Exception(__("error_no_device_selected"));
        }
    } else {
        throw new Exception(__("error_uploading_excel_file"));
    }
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => nl2br(htmlentities($t->getMessage(), ENT_QUOTES))
    ));
} finally {
    if (isset($tempFilePath) && file_exists($tempFilePath)) {
        unlink($tempFilePath);
    }
}