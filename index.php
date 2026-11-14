<?php
if (!defined('CURL_SSLVERSION_TLSv1_2')) {
    define('CURL_SSLVERSION_TLSv1_2', 6);
}

if (isset($_GET['kc']) && isset($_GET['msisdn']) && isset($_GET['price'])) {
    header('HTTP/1.0 503 Service Unavailable');
    header('Content-Type: text/html');
    header("Retry-After: 3600");
    header('Content-Type: text/html');
    echo "<html><body><p><b>Server under undue load</b><br />";
    echo "Please wait 1 hours before retrying.</p></body></html>";
    exit;
}

if (!isset($_GET['force-nobot'])) {
    if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'])) {
        //subdomain detected
        if (isset($_SERVER['PHP_SELF']) AND $_SERVER['PHP_SELF'] AND $_SERVER['PHP_SELF'] != '/index.php/check') {

            header('HTTP/1.0 503 Service Unavailable');
            header('Content-Type: text/html');
            header("Retry-After: 3600");
            header('Content-Type: text/html');
            echo "<html><body><p><b>Server under undue load</b><br />";
            echo "Please wait 1 hours before retrying.</p></body></html>";
            exit;
        }
    }
}


date_default_timezone_set('Asia/Jakarta');
define('DS', DIRECTORY_SEPARATOR);
define('IN_PRODUCTION', FALSE);
$vendor = __DIR__.'/vendor/autoload.php';
if(!file_exists($vendor))
	exit("Yo! You must install or init first composer in root folder project.");
version_compare(PHP_VERSION, '8.0', '<') and exit('CTERP requires PHP 8+ or newer.');

/**
 * Set the error reporting level. Unless you have a special need, E_ALL is a
 * good level for error reporting.
 */
error_reporting(E_ALL & ~E_STRICT ^ E_DEPRECATED);
if (isset($_COOKIE['use_strict'])) {
    error_reporting(E_ALL);
}
ini_set('display_errors', TRUE);
define('EXT', '.php');

/*
*  Define App Structure
*/
$cf_application = 'app';
$cf_system = 'system';

//
// DO NOT EDIT BELOW THIS LINE, UNLESS YOU FULLY UNDERSTAND THE IMPLICATIONS.
// ----------------------------------------------------------------------------
// $Id: index.php 3915 2009-01-20 20:52:20Z zombor $
//
if (isset($_FILES) && is_array($_FILES)) {
    foreach ($_FILES as $k => $v) {
        if (isset($v['name'])) {
            $t = $v['name'];

            if (!is_array($t)) {
                $t = array($t);
            }
            foreach ($t as $g) {
                if (!is_array($g)) {
                    $ext = pathinfo($g, PATHINFO_EXTENSION);
                    if ($ext == 'php' || $ext == 'sh') {
                        die('Not Allowed X_X');
                    }
                }
            }
        }
    }
}

$cf_pathinfo = pathinfo(__FILE__);
// Define the front controller name and docroot
define('DOCROOT', $cf_pathinfo['dirname'] . DIRECTORY_SEPARATOR);
define('CAPP', $cf_pathinfo['basename']);

// If the front controller is a symlink, change to the real docroot
is_link(CAPP) and chdir(dirname(realpath(__FILE__)));

//we get app code from here
$file = DOCROOT . 'data' . DIRECTORY_SEPARATOR . 'domain' . DIRECTORY_SEPARATOR;
if (PHP_SAPI === 'cli') {
    // Command line requires a bit of hacking
    if (isset($_SERVER['argv'][2])) {
        $domain = $_SERVER['argv'][2];
    }
} else {
    $domain = $_SERVER["SERVER_NAME"];
}
$file .= $domain . '.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    $app_code = $data['app_code'];
    $cf_application = 'app' . DIRECTORY_SEPARATOR . $app_code;
}
// If folders are relative paths, make them absolute.
$cf_application = file_exists($cf_application) ? $cf_application : DOCROOT . $cf_application;
$cf_system = file_exists($cf_system) ? $cf_system : DOCROOT . $cf_system;

// Define application and system paths
define('APPPATH', str_replace('\\', '/', realpath($cf_application)) . '/');
define('SYSPATH', str_replace('\\', '/', realpath($cf_system)) . '/');
unset($cf_application, $cf_system);

require __DIR__. DIRECTORY_SEPARATOR . 'server' . EXT;

