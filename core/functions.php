<?php

/**
 * Файловый лог для отладки
 *
 */

//function debugLog($msg, $array = null, $file = null)
//{
//
//    if (is_null($file) or empty($file)) {
//        $file = 'debug.log';
//    }
//
////    $logfilePath = $this->_config->get('root_path', 'core') . 'log' . DS . $file;
//    $logfilePath = __DIR__ . $file;
//    $logTime = date('d.m.y H:i');
//    $msg = $logTime . ': ' . $msg . PHP_EOL;
//    toFile($logfilePath, $msg, 'a');
//}


function debugLog($msg, $file = null)
{

    if (is_null($file) or empty($file)) {
        $file = 'debug.log';
    }
//    $logfilePath = $this->_config->get('root_path', 'core') . 'log' . DS . $file;
    $logfilePath = $file;
    $logTime = date('d.m.y H:i');
    if (is_array($msg) || is_object($msg)) {
        // $logTime . ': ' . $msg . PHP_EOL;
        toFile($logfilePath, $logTime . ': ', 'a');
        // ob_flush();
        // ob_start();
        // var_dump($msg);
        // toFile($logfilePath, ob_get_flush(), 'a');

        toFile($logfilePath, json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT), 'a');

    } else {
        $msg = $logTime . ': ' . $msg . PHP_EOL;
        toFile($logfilePath, $msg, 'a');
    }
}


function toFile($file, $content, $mode = 'w+', $attr = 0755, $has_protect = true)
{
    $disallow_extensions = array(
        'php',
        'pl',
        'exe',
        'dll',
        'so',
        'pu',
        'sql',
        'js'
    );

    if ($has_protect && in_array(getExt($file), $disallow_extensions)) {
        return false;
    }
    $fx = fopen($file, $mode, $attr);
    fputs($fx, $content);
    fclose($fx);
}

function ext($file_name)
{
    $parts = explode('.', $file_name);
    return $parts[count($parts) - 1];
}

function getExt($file_name)
{
    return ext($file_name);
}


function round_up($number, $precision = 2)
{
    $fig = (int) str_pad('1', $precision, '0');
    return (ceil($number * $fig) / $fig);
}
