<?php
error_reporting(0);
include ("config.inc.php");
$db = mysql_connect($db_host, $db_user, $db_password);

if (!$db) {
    exit('<p> Mancata connessione al server sql-<p>');
}

if (!@mysql_select_db($db_name)) {
    exit('<p> Database   non trovato<p>');
}

?>