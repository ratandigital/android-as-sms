<?php
if (file_exists(__DIR__ . "/../config.php")) {
    header("location:../index.php");
    die;
}

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../includes/set-language.php";

$title = __("application_title") . " | " . __("installation");

$requirements = [
    "PHP 7.3 or above" => PHP_VERSION_ID > 70300,
    "PHP cURL extension" => extension_loaded("curl"),
    "PHP GD extension" => extension_loaded("gd") && function_exists('gd_info'),
    "PHP Multibyte String extension" => extension_loaded("mbstring"),
    "PHP Zip extension" => extension_loaded("zip"),
    "PHP XML extension" => extension_loaded("xml"),
    "PHP JSON extension" => extension_loaded("json")
];
$requirementsFulfilled = true;

array_walk($requirements, function ($value, $key) {
    if (!$value) {
        global $requirementsFulfilled;
        $requirementsFulfilled = false;
    }
});

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo htmlentities($title, ENT_QUOTES); ?></title>
    <meta name="description" content="<?= htmlentities(__('application_description'), ENT_QUOTES) ?>">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="../components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="../components/ionicons/dist/css/ionicons.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="../components/select2/dist/css/select2.min.css">
    <!-- toastr -->
    <link rel="stylesheet" href="../components/toastr/build/toastr.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="../components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="../components/datatables.net-responsive-bs/css/responsive.bootstrap.min.css">
    <!-- Dropzone -->
    <link rel="stylesheet" href="../components/dropzone/dist/min/dropzone.min.css">
    <!-- Pace -->
    <link rel="stylesheet" href="../components/pace-js/themes/blue/pace-theme-corner-indicator.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="../css/skins/skin-blue.min.css">
    <!-- Custom style -->
    <link rel="stylesheet" href="../css/custom.css">
    <?php if (isset($_COOKIE["DEVICE_ID"])) { ?>
        <!-- Android webview specific style -->
        <link rel="stylesheet" href="../css/webview.css">
    <?php } ?>

    <link rel="shortcut icon" href="../favicon.png" type="image/x-icon">
    <link rel="icon" href="../favicon.png" type="image/x-icon">

    <script>
        window.paceOptions = {
            startOnPageLoad: false,
            ajax: {
                trackMethods: ['GET', 'POST', 'PUT', 'DELETE', 'REMOVE']
            }
        };
    </script>
    <!-- Pace -->
    <script src="../components/pace-js/pace.min.js"></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- Google Font -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <style>
        @media only screen and (max-width: 768px) {
            .register-box {
                position: relative;
                top: 75px;
            }
        }
    </style>
</head>
<body class="hold-transition register-page">

<?php require_once __DIR__ . "/../includes/language-form.php"; ?>

<div class="register-box" id="installationBox">
    <div class="register-logo">
        <img src="../logo.png" style="width: 64px; height: 64px" alt="logo">
        <a href="index.php"><?= __("application_title"); ?></a>
    </div>

    <div class="register-box-body">
        <p class="login-box-msg"><?= __("installation_of_app", ["app" => __("application_title")]); ?></p>

        <div id="ajaxResult">
        </div>

        <div id="step-1">
            <?php foreach ($requirements as $requirement => $fulfilled) { ?>
                <div class="row">
                    <div class="col-xs-10">
                        <p><?= $requirement ?></p>
                    </div>
                    <div class="col-xs-2">
                        <?php if ($fulfilled) { ?>
                            <i class="fa fa-check"></i>
                        <?php } else { ?>
                            <i class="fa fa-times"></i>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>

            <div class="row">
                <div class="col-xs-4 pull-right">
                    <button type="button" id="nextButton"
                            class="btn btn-warning btn-block btn-flat"><?= __("next"); ?></button>
                </div>
            </div>
        </div>

        <div id="step-2" hidden>
            <form id="install" method="post">
                <div class="form-group has-feedback">
                    <input type="text" name="name" class="form-control"
                           placeholder="<?= __("name") ?>" required="required">
                    <span class="glyphicon glyphicon-user form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="email" name="email" class="form-control"
                           placeholder="<?= __("email") ?>" required="required">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="password" name="password" minlength="8" id="passwordInput" class="form-control"
                           placeholder="<?= __("password") ?>" required="required">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback" id="confirmPasswordBox">
                    <input type="password" class="form-control" name="confirmPassword" id="confirmPasswordInput"
                           placeholder="<?= __("confirm_password") ?>" required="required">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="text" name="databaseServer" class="form-control"
                           placeholder="<?= __("database_server") ?>" required="required">
                    <span class="fa fa-server form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="text" name="databaseName" class="form-control"
                           placeholder="<?= __("database_name") ?>" required="required">
                    <span class="fa fa-database form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="text" name="databaseUser" class="form-control" placeholder="<?= __("database_user") ?>"
                           required="required">
                    <span class="fa fa-user form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="password" name="databasePassword" class="form-control"
                           placeholder="<?= __("database_password") ?>">
                    <span class="fa fa-lock form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="text" name="firebaseServerKey" class="form-control"
                           placeholder="<?= __("firebase_server_key") ?>" required="required">
                    <span class="fa fa-cloud form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="text" name="firebaseSenderId" class="form-control"
                           placeholder="<?= __("firebase_sender_id") ?>" required="required">
                    <span class="fa fa-cloud form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <select class="form-control select2" name="timezone" style="width: 100%;" required="required">
                        <?php
                        $timezones = generate_timezone_list();
                        $setDefault = isset($_POST["timezone"]);
                        foreach ($timezones as $timezone => $timezone_value) {
                            echo "<option value='$timezone' ";
                            if ($timezone == date_default_timezone_get()) {
                                echo "selected='selected'";
                            }
                            echo ">{$timezone_value}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="row">
                    <!-- /.col -->
                    <div class="col-xs-4 pull-right">
                        <button type="submit" name="install" id="installButton"
                                class="btn btn-warning btn-block btn-flat"><?= __("install"); ?></button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
        </div>

        <div id="step-3" hidden>
            <div>
                <h3><?= __("cron_job"); ?></h3>
                <div>
                    <p><?= __("cron_job_instructions"); ?></p>
                    <blockquote>crontab -e * * * * * php -q "<?= realpath('../cron.php'); ?>" >/dev/null 2>&1
                    </blockquote>
                </div>
                <h3><?= __("faqs"); ?></h3>
                <div>
                    <p><?= __("faqs_link"); ?></p>
                </div>
                <h3><?= __("support") ?></h3>
                <div>
                    <p><?= __("support_link") ?></p>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-4 pull-right">
                    <a href="../index.php" class="btn btn-warning btn-block btn-flat"><?= __("sign_in"); ?></a>
                </div>
            </div>
        </div>
    </div>
    <!-- /.form-box -->
</div>
<!-- /.register-box -->

<!-- jQuery 3 -->
<script src="../components/jquery/dist/jquery.min.js"></script>
<!-- jQuery Validation Plugin -->
<script src="../components/jquery-validation/dist/jquery.validate.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="../components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Select2 -->
<script src="../components/select2/dist/js/select2.full.min.js"></script>
<!-- Common Functionality -->
<script src="../js/common.js"></script>
<script>
    $(function () {
        const installForm = $('#install');
        const installButton = $('#installButton');
        const nextButton = $('#nextButton');
        const step1 = $('#step-1');
        const step2 = $('#step-2');
        const step3 = $('#step-3');

        $(".select2").select2();

        <?php if ($requirementsFulfilled) { ?>
            nextButton.click(function (event) {
               event.preventDefault();
               step1.prop('hidden', true);
               step2.prop('hidden', false);
            });
        <?php } else { ?>
            nextButton.prop('disabled', true);
            $('#ajaxResult').html(
                `<div class="alert alert-danger alert-dismissible" id="alertDanger">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                                &times;
                            </button>
                            <h4><i class="icon fa fa-ban"></i>&nbsp;<?= __("error_dialog_title"); ?></h4>
                            <?= __("error_server_requirements_not_met"); ?>
                        </div>`
            );
        <?php } ?>

        installForm.validate({
            rules: {
                password: "required",
                confirmPassword: {
                    equalTo: "#passwordInput"
                }
            },
            submitHandler: function (form) {
                installButton.prop('disabled', true);
                ajaxRequest("install.php", installForm.serialize()).then((result) => {
                    $('#ajaxResult').html(
                        `<div class="alert alert-success alert-dismissible" id="alertSuccess">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                            &times;
                        </button>
                        <h4><i class="icon fa fa-check"></i>&nbsp;<?= __("success_dialog_title"); ?></h4>
                        <a href="${result}" target="_blank">${result}</a>
                    </div>`
                    )
                    $('#installationBox').css('width', '800px');
                    step2.prop('hidden', true);
                    step3.prop('hidden', false);
                }).catch(reason => {
                    $('#ajaxResult').html(
                        `<div class="alert alert-danger alert-dismissible" id="alertDanger">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                            &times;
                        </button>
                        <h4><i class="icon fa fa-ban"></i>&nbsp;<?= __("error_dialog_title"); ?></h4>
                        ${reason}
                    </div>`
                    );
                    $('#passwordInput').empty();
                    $('#confirmPasswordInput').empty();
                }).finally(() => {
                    installButton.prop('disabled', false);
                });
                return false;
            }
        });
    });
</script>
</body>
</html>