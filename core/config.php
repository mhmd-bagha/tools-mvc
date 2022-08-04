<?php
session_start();


define('SERVERDB', 'server_db');
define('USERNAMEDB', 'username_db');
define('PASSWORDDB', 'pass_db');
define('DBNAMEDB', 'name_db');
define('DOMAIN', 'root_domain');

function PhpError($error_code, $error_msg, $error_file, $error_line)
{
    echo "<br/><b>Error Message: </b>{$error_msg}<br/>";
    echo "<smal>{$error_file} <b>In The Line: {$error_line}</b></smal><br/><br/>";
    if ($error_code == E_USER_ERROR): die(); endif;
}

set_error_handler("PhpError");