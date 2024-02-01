<?php

$app_specific = [
    'application_title' => 'Bulk SMS Gateway',
    'application_description' => 'Android phone as sms & mms gateway',
    'application_version' => '9.0.1',
    'app_version_code' => 33,
    'company_name' => 'Your Company Name',
    'company_url' => '#',
    'application_url' => 'https://drive.google.com/file/d/1REoXTBUwh8pvKGzCm1L-Dgq47LIVhDLZ/view?usp=sharing',
    'unsubscribe_url' => '%server%/unsubscribe.php',
    'logo_src' => 'logo.png',
    'favicon_src' => 'favicon.png',
    'get_credits_url' => '#',
    'skin' => 'blue',
    'default_language' => 'english',
    'default_use_progressive_queue' => 1,
    'default_credits' => 200,
    'default_devices_limit' => 2,
    'default_contacts_limit' => 200
];

$lang = array_merge($lang, $app_specific);