<?php
!defined('EMLOG_ROOT') && exit('access denied!');

if (!class_exists('Umami', false)) {
    include __DIR__ . '/umami_class.php';
}

$route = Input::postStrVar('route');

$available_routes = [
    'login', // 登录接口
    'save_umami_domain', // 保存umami系统地址
];
if (in_array($route, $available_routes)) {
    if ($route === 'login') {
        $username = Input::postStrVar('username');
        $password = Input::postStrVar('password');
        Umami::getInstance()->login($username, $password);
    }
    if ($route === 'save_umami_domain') {
        $domain = Input::postStrVar('umami_domain');
        Umami::getInstance()->saveDomain($domain);
    }
}

die();