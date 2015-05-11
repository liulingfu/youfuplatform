<?php
function init_checker()
{
    $domain_array = array(
        base64_encode(base64_encode('localhost')),
        base64_encode(base64_encode('127.0.0.1')),
        base64_encode(base64_encode('*.0581.info')),
        );
    $str = base64_encode(base64_encode(serialize($domain_array)) . "|" . serialize($domain_array));
    $arr = explode("|", base64_decode($str));
    $arr = unserialize($arr[1]);
    foreach ($arr as $k => $v) {
        $arr[$k] = base64_decode(base64_decode($v));
    }
    $host = $_SERVER['HTTP_HOST'];
    $host = explode(":", $host);
    $host = $host[0];
    $passed = false;
    foreach ($arr as $k => $v) {
        if (substr($v, 0, 2) == '*.') {
            $preg_str = substr($v, 2);
            if (preg_match("/" . $preg_str . "$/", $host) > 0) {
                $passed = true;
                break;
            }
        }
    }
    if (!$passed) {
        if (!in_array($host, $arr)) {
            return false;
        }
    }
    return true;
}
$checker = init_checker();
if (!$checker)
    die("domain not authorized");
$sys_config = require APP_ROOT_PATH . 'system/config.php';
function app_conf($name)
{
    return stripslashes($GLOBALS['sys_config'][$name]);
}
require APP_ROOT_PATH . 'system/cache/Cache.php';
$cache = CacheService::getInstance();
require_once APP_ROOT_PATH . "system/cache/CacheFileService.php";
$fcache = new CacheFileService();
$fcache->set_dir(APP_ROOT_PATH . "public/runtime/data/");
require APP_ROOT_PATH . 'system/db/db.php';
define('DB_PREFIX', app_conf('DB_PREFIX'));
if (!file_exists(APP_ROOT_PATH . 'public/runtime/app/db_caches/'))
    mkdir(APP_ROOT_PATH . 'public/runtime/app/db_caches/', 0777);
$pconnect = false;
$db = new mysql_db(app_conf('DB_HOST') . ":" . app_conf('DB_PORT'), app_conf('DB_USER'),
    app_conf('DB_PWD'), app_conf('DB_NAME'), 'utf8', $pconnect);
require APP_ROOT_PATH . 'system/template/template.php';
if (!file_exists(APP_ROOT_PATH . 'public/runtime/app/tpl_caches/'))
    mkdir(APP_ROOT_PATH . 'public/runtime/app/tpl_caches/', 0777);
if (!file_exists(APP_ROOT_PATH . 'public/runtime/app/tpl_compiled/'))
    mkdir(APP_ROOT_PATH . 'public/runtime/app/tpl_compiled/', 0777);
$tmpl = new AppTemplate;
$_REQUEST = array_merge($_GET, $_POST);
filter_request($_REQUEST);
$lang = require APP_ROOT_PATH . '/app/Lang/' . app_conf("SHOP_LANG") .
    '/lang.php'; ?>