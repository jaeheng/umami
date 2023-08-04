<?php
!defined('EMLOG_ROOT') && exit('access denied!');

if (!class_exists('Umami', false)) {
    include __DIR__ . '/umami_class.php';
}

if ($_POST['route']) {
    include __DIR__ . '/umami_api.php';
}

function plugin_setting_view() {
    include __DIR__ . '/umami_view.php';
}