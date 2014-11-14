<?php
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', 'production');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */

require_once 'Zend/Application.php';

require_once 'Zend/Cache.php';
$cache = Zend_Cache::factory('Core', 'File',
    array('caching' => true, 'automatic_serialization' => true),
    array('cache_dir' => APPLICATION_PATH . '/../data/cache'));

require_once 'Zend/Config.php';
require_once 'Zend/Config/Ini.php';
$options = $cache->load('app_options_' . APPLICATION_ENV);
if(is_array($options) && count($options)) {
    $config = new Zend_Config($options, APPLICATION_ENV);
} else {
    $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
}

$application = new Zend_Application(
    APPLICATION_ENV, $config
);

require_once 'MF/Service/ServiceBroker.php';
$application->getBootstrap()->setContainer(MF_Service_ServiceBroker::getInstance());
$application->bootstrap()->run();
