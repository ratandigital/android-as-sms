<?php
/**
 * @var int $activePlansCount
 */

require_once __DIR__ . "/includes/login.php";

function appendWhere(string $query ,array $where): string
{
    for ($i = 0; $i < count($where); $i++) {
        if ($i) {
            $query .= " AND ";
        } else {
            $query .= " WHERE ";
        }
        $query .= $where[$i];
    }
    return $query;
}

$title = __("application_title") . ' | ' . __("dashboard");

$query = "SELECT COUNT(IF(Message.status = 'Sent', 1, NULL)) as totalSent, COUNT(IF(Message.status = 'Scheduled', 1, NULL)) as totalScheduled, COUNT(IF(Message.status = 'Delivered', 1, NULL)) as totalDelivered, COUNT(IF(Message.status = 'Failed', 1, NULL)) as totalFailed, COUNT(IF(Message.status = 'Pending', 1, NULL)) as totalPending, COUNT(IF(Message.status = 'Queued', 1, NULL)) as totalQueued, COUNT(IF(Message.status = 'Received', 1, NULL)) as totalReceived FROM Message";

$where = [];

if (!empty($_GET["interval"]) && ctype_digit($_GET["interval"])) {
    $start_date = getDataBaseTime(date("Y-m-d", time() - 86400 * $_GET["interval"]) . "  00:00:00")->format("Y-m-d H:i:s");
    $end_date = getDataBaseTime(date("Y-m-d", time()) . "  23:59:59")->format("Y-m-d H:i:s");
    array_push($where, "Message.sentDate >= '{$start_date}' AND Message.sentDate <= '{$end_date}'");
}

if (!$_SESSION["isAdmin"]) {
    array_push($where, "Message.userID = {$_SESSION["userID"]}");
}
if (isset($_COOKIE["DEVICE_ID"])) {
    array_push($where, "Message.deviceID = {$_COOKIE["DEVICE_ID"]}");
}

$query = appendWhere($query, $where);

$queryString = "";
if (isset($_COOKIE["DEVICE_ID"])) {
    if ($_SESSION["isAdmin"]) {
        $queryString .= "&user={$_SESSION["userID"]}";
    }
    $queryString .= "&device={$_COOKIE["DEVICE_ID"]}";
}

$counts = MysqliDb::getInstance()->rawQueryOne($query);
$pending = $counts["totalPending"];
$scheduled = $counts["totalScheduled"];
$queued = $counts["totalQueued"];
$sent = $counts["totalSent"];
$failed = $counts["totalFailed"];
$received = $counts["totalReceived"];
$delivered = $counts["totalDelivered"];

$ussdQuery = "SELECT COUNT(IF(Ussd.responseDate IS NULL, 1, NULL)) as totalPending, COUNT(IF(Ussd.responseDate IS NOT NULL, 1, NULL)) as totalSent FROM Ussd";
$where = [];
if (isset($start_date) && isset($end_date)) {
    $where[] = "Ussd.sentDate >= '{$start_date}' AND Ussd.sentDate <= '{$end_date}'";
}
if (!$_SESSION["isAdmin"]) {
    $where[] = "Ussd.userID = {$_SESSION["userID"]}";
}
if (isset($_COOKIE["DEVICE_ID"])) {
    $where[] = "Ussd.deviceID = {$_COOKIE["DEVICE_ID"]}";
}
$ussdQuery = appendWhere($ussdQuery, $where);

$ussdCounts = MysqliDb::getInstance()->rawQueryOne($ussdQuery);
$pendingUssd = $ussdCounts["totalPending"];
$sentUssd = $ussdCounts["totalSent"];

/** @var User $logged_in_user */
$credits = is_null($logged_in_user->getCredits()) ? "&infin;" : $logged_in_user->getCredits();
if ($logged_in_user->getExpiryDate() != null) {
    $currentTime = new DateTime("now", new DateTimeZone($_SESSION["timeZone"]));
    $expiryTime = getDisplayTime($logged_in_user->getExpiryDate());
    $expiresAfter = "Expired";
    if ($expiryTime > $currentTime) {
        $interval = $expiryTime->diff($currentTime);
        $day = $interval->format('%a');
        $hour = $interval->format('%h');
        $min = $interval->format('%i');
        $seconds = $interval->format('%s');

        if ($day >= 1) {
            $expiresAfter = $day . " d";
        } else if ($hour >= 1 && $hour <= 24) {
            $expiresAfter = $hour . " hr";
        } else if ($min >= 1 && $min <= 60) {
            $expiresAfter = $min . " min";
        } else if ($seconds >= 1 && $seconds <= 60) {
            $expiresAfter = $seconds . " sec";
        }
    }
}

$activeSubscriptions = 0;
if ($_SESSION["isAdmin"]) {
    $activeSubscriptions = Subscription::where("Subscription.status", "ACTIVE")->count();
    $query = "SELECT SUM(amount) as TotalAmount, SUM(transactionFee) as TotalFee, currency FROM Payment WHERE status = 'COMPLETED'";
    if (isset($start_date)) {
        $query .= " AND Payment.dateAdded >= '{$start_date}' AND Payment.dateAdded <= '{$end_date}'";
    }
    $query .= " GROUP BY currency";
    $data = MysqliDb::getInstance()->rawQueryOne($query);
    $earnings = 0;
    if (isset($data["TotalAmount"])) {
        $earnings = (int)$data["TotalAmount"] - (int)$data["TotalFee"];
        $earnings = "{$earnings} {$data["currency"]}";
    }
} else {
    $activeSubscriptions = Subscription::where("Subscription.status", "ACTIVE")
        ->where("Subscription.userID", $logged_in_user->getID())
        ->count();
}

require_once __DIR__ . "/includes/header.php";
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div>
            <h1>
                <?= __("dashboard") ?>
                <select id="timeIntervalInput"
                        class="form-control pull-right"
                        title="Time Interval"
                        style="margin-top: 5px; width: auto">
                    <option value <?php if (empty($_GET["interval"])) echo 'selected'; ?>><?= __("all_time"); ?></option>
                    <option value="7" <?php if (isset($_GET["interval"]) && $_GET["interval"] == 7) echo 'selected'; ?>>
                        7 <?= __("days"); ?></option>
                    <option value="15" <?php if (isset($_GET["interval"]) && $_GET["interval"] == 15) echo 'selected'; ?>>
                        15 <?= __("days"); ?></option>
                    <option value="30" <?php if (isset($_GET["interval"]) && $_GET["interval"] == 30) echo 'selected'; ?>>
                        30 <?= __("days"); ?></option>
                    <option value="60" <?php if (isset($_GET["interval"]) && $_GET["interval"] == 60) echo 'selected'; ?>>
                        60 <?= __("days"); ?></option>
                    <option value="90" <?php if (isset($_GET["interval"]) && $_GET["interval"] == 90) echo 'selected'; ?>>
                        90 <?= __("days"); ?></option>
                </select>
            </h1>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- Small boxes (Stat box) -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow-gradient">
                    <div class="inner">
                        <h3 id="pending-count"><?= $pending ?></h3>

                        <p><?= __("pending") ?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios-timer"></i>
                    </div>
                    <a href="messages.php?status=Pending<?= $queryString ?>"
                       class="small-box-footer"><?= __("more_info") ?> <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-blue-gradient">
                    <div class="inner">
                        <h3 id="scheduled-count"><?= $scheduled ?></h3>

                        <p><?= __("scheduled") ?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-md-calendar"></i>
                    </div>
                    <a href="messages.php?status=Scheduled<?= $queryString ?>"
                       class="small-box-footer"><?= __("more_info") ?> <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-aqua-gradient">
                    <div class="inner">
                        <h3 id="queued-count"><?= $queued ?></h3>

                        <p><?= __("queued") ?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios-sync"></i>
                    </div>
                    <a href="messages.php?status=Queued<?= $queryString ?>"
                       class="small-box-footer"><?= __("more_info") ?> <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green-gradient">
                    <div class="inner">
                        <h3 id="sent-count"><?= $sent ?></h3>

                        <p><?= __("sent") ?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios-done-all"></i>
                    </div>
                    <a href="messages.php?status=Sent<?= $queryString ?>"
                       class="small-box-footer"><?= __("more_info") ?> <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green-gradient">
                    <div class="inner">
                        <h3 id="delivered-count"><?= $delivered ?></h3>

                        <p><?= __("delivered") ?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-md-done-all"></i>
                    </div>
                    <a href="messages.php?status=Delivered<?= $queryString ?>"
                       class="small-box-footer"><?= __("more_info") ?> <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-red-gradient">
                    <div class="inner">
                        <h3 id="failed-count"><?= $failed ?></h3>

                        <p><?= __("failed") ?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios-remove-circle"></i>
                    </div>
                    <a href="messages.php?status=Failed<?= $queryString ?>"
                       class="small-box-footer"><?= __("more_info") ?> <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-blue-gradient">
                    <div class="inner">
                        <h3 id="received-count"><?= $received ?></h3>

                        <p><?= __("received") ?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios-mail-unread"></i>
                    </div>
                    <a href="messages.php?status=Received<?= $queryString ?>"
                       class="small-box-footer"><?= __("more_info") ?> <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow-gradient">
                    <div class="inner">
                        <h3 id="pending-ussd-count"><?= $pendingUssd ?></h3>

                        <p><?= __("pending_ussd_requests"); ?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios-timer"></i>
                    </div>
                    <a href="ussd.php"
                       class="small-box-footer"><?= __("more_info") ?> <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green-gradient">
                    <div class="inner">
                        <h3 id="sent-ussd-count"><?= $sentUssd ?></h3>

                        <p><?= __("sent_ussd_requests"); ?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios-done-all"></i>
                    </div>
                    <a href="ussd.php"
                       class="small-box-footer"><?= __("more_info") ?> <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-purple-gradient">
                    <div class="inner">
                        <h3><?= $credits ?></h3>

                        <p><?= __("available") ?></p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-credit-card"></i>
                    </div>
                    <?php if ($activeSubscriptions > 0) { ?>
                        <a href="subscriptions.php" target="_blank"
                           class="small-box-footer"><?= __("more_info") ?> <i
                                    class="fa fa-arrow-circle-right"></i></a>
                    <?php } else { ?>
                        <?php if (Setting::get("paypal_enabled") && $activePlansCount > 0) { ?>
                            <a href="subscribe.php" target="_blank"
                               class="small-box-footer"><?= __("get_credits") ?> <i
                                        class="fa fa-arrow-circle-right"></i></a>
                        <?php } else { ?>
                            <a href="<?= __("get_credits_url") ?>" target="_blank"
                               class="small-box-footer"><?= __("get_credits") ?> <i
                                        class="fa fa-arrow-circle-right"></i></a>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
            <!-- ./col -->
            <?php if (isset($expiresAfter)) { ?>
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-purple-gradient">
                        <div class="inner">
                            <h3><?= $expiresAfter ?></h3>

                            <p><?= __("expires_after") ?></p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-ios-time"></i>
                        </div>
                        <?php if ($activeSubscriptions > 0) { ?>
                            <a href="subscriptions.php" target="_blank"
                               class="small-box-footer"><?= __("more_info") ?> <i
                                        class="fa fa-arrow-circle-right"></i></a>
                        <?php } else { ?>
                            <?php if (Setting::get("paypal_enabled") && $activePlansCount > 0) { ?>
                                <a href="subscribe.php" target="_blank"
                                   class="small-box-footer"><?= __("get_credits") ?> <i
                                            class="fa fa-arrow-circle-right"></i></a>
                            <?php } else { ?>
                                <a href="<?= __("get_credits_url") ?>" target="_blank"
                                   class="small-box-footer"><?= __("get_credits") ?> <i
                                            class="fa fa-arrow-circle-right"></i></a>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
                <!-- ./col -->
            <?php } ?>
            <?php if ($_SESSION["isAdmin"]) { ?>
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-maroon-gradient">
                        <div class="inner">
                            <h3><?= $activeSubscriptions ?></h3>

                            <p><?= __("active_subscriptions") ?></p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-newspaper-o"></i>
                        </div>
                        <a href="subscriptions.php"
                           class="small-box-footer"><?= __("more_info") ?> <i
                                    class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-black-gradient">
                        <div class="inner">
                            <h3><?= $earnings ?></h3>

                            <p><?= __("earnings") ?></p>
                        </div>
                        <div class="icon" style="color: white">
                            <i class="fa fa-money"></i>
                        </div>
                        <a href="subscriptions.php"
                           class="small-box-footer"><?= __("more_info") ?> <i
                                    class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
            <?php } ?>
        </div>
        <!-- /.row -->

        <?php
        $showTutorial = isset($_SESSION["showTutorial"]);
        if ($showTutorial) {
            unset($_SESSION["showTutorial"]);
            require_once __DIR__ . "/includes/add-device.php";
        }
        ?>

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php require_once __DIR__ . "/includes/footer.php"; ?>
<?php if (Setting::get("pusher_enabled")) { ?>
<script>
    channel.bind('status-updated', function(data) {
        let totalPending = 0;
        let totalQueued = 0;
        let totalSent = 0;
        let totalDelivered = 0;
        let totalFailed = 0;
        data.forEach(function (item, index, arr) {
            <?php if (isset($_COOKIE["DEVICE_ID"])) { ?>
                if (item["deviceID"] !== <?= $_COOKIE["DEVICE_ID"]; ?>)
                {
                    return;
                }
            <?php } ?>
            totalPending += item["totalPending"];
            totalQueued += item["totalQueued"];
            totalSent += item["totalSent"];
            totalDelivered += item["totalDelivered"];
            totalFailed += item["totalFailed"];
        });
        $('#pending-count').text(totalPending);
        $('#queued-count').text(totalQueued);
        $('#sent-count').text(totalSent);
        $('#delivered-count').text(totalDelivered);
        $('#failed-count').text(totalFailed);
    });

    channel.bind('messages-queued', function (data) {
        let totalScheduled = 0;
        let totalQueued = 0;
        let totalPending = 0;
        data.forEach(function (item, index, arr) {
            <?php if (isset($_COOKIE["DEVICE_ID"])) { ?>
                if (item["deviceID"] !== <?= $_COOKIE["DEVICE_ID"]; ?>)
                {
                    return;
                }
            <?php } ?>
            totalScheduled += item["totalScheduled"];
            totalPending += item["totalPending"];
            totalQueued += item["totalQueued"];
        });
        $('#scheduled-count').text(totalScheduled);
        $('#pending-count').text(totalPending);
        $('#queued-count').text(totalQueued);
    });

    channel.bind('ussd-request', function (data) {
        let totalPending = 0;
        let totalSent= 0;
        data.forEach(function (item, index, arr) {
            <?php if (isset($_COOKIE["DEVICE_ID"])) { ?>
            if (item["deviceID"] !== <?= $_COOKIE["DEVICE_ID"]; ?>)
            {
                return;
            }
            <?php } ?>
            totalPending += item["totalPending"];
            totalSent += item["totalSent"];
        });
        $('#pending-ussd-count').text(totalPending);
        $('#sent-ussd-count').text(totalSent);
    });
</script>
<?php } ?>
<?php if ($showTutorial) { ?>
    <script type="text/javascript">
        $(function () {
            $('#modal-add-device').modal({backdrop: 'static', keyboard: false});
        });
    </script>
<?php } ?>
<script>
    $(function () {
        $('#timeIntervalInput').change(function (event) {
            event.preventDefault();
            window.location.href = location.protocol + '//' + location.host + location.pathname + `?interval=${$(this).val()}`
        });
    })
</script>
</body>
</html>
