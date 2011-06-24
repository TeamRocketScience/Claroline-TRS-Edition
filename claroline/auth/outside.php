<?php

require '../inc/claro_init_global.inc.php';
require_once '../inc/lib/course.lib.inc.php';

if(isset($_REQUEST['by']) && isset($_REQUEST['token']))
{
    $by = $_REQUEST['by'];
    $token = $_REQUEST['token'];

    if(is_file('tokens.json'))
    {
        $tokens = json_decode(file_get_contents('tokens.json'), true);
        if(isset($tokens[$token]))
        {
            $allowedActions = isset($tokens[$token]['actions']) ? $tokens[$token]['actions'] : array();

            if(isset($_REQUEST['do']) && isset($allowedActions[$_REQUEST['do']]))
            {
                $functionName = 'claro_' . $_REQUEST['do'];
                if(function_exists($functionName))
                {
                    $args = isset($_REQUEST['args']) ? json_decode($_REQUEST['args']) : null;
                 //   header('Content-Type: text/json');
                    echo json_encode(call_user_func_array($functionName, array($args)));
                }
                else
                    echo 'Unknown action "' . $functionName . '"';
            }
            else
                echo 'Access denied for the action "' . $_REQUEST['do'] . '"';
        }
        else
            echo 'Unknown token "' . $token . '"';
    }
    else
        echo 'Missed token file. Contact an Administrator';
}
else
    echo 'Missed "by" or "token" field';