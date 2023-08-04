<?php
/*
Plugin Name: umami访问统计
Version: 1.0
Plugin URL:
Description: umami接口实现 https://umami.is/docs/api
Author: jaeheng
Author URL: https://blog.phpat.com
*/

!defined('EMLOG_ROOT') && exit('access denied!');

function umami_side_menu()
{
    echo '<a class="collapse-item" id="umami" href="plugin.php?plugin=umami">umami访问统计</a>';
}

addAction('adm_menu_ext', 'umami_side_menu');
