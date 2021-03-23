<?
/**
 * Author:   Ernest Wojciuk
 * Web Site: www.imap.pl
 * Email:    ernest@moldo.pl
 * Comments: EMAIL TO DB:: EXAMPLE 1
 */

include_once("class.emailtodb.php");

$cfg["db_host"] = '';
$cfg["db_user"] = '';
$cfg["db_pass"] = '';
$cfg["db_name"] = '';

$mysql_pconnect = mysql_pconnect($cfg["db_host"], $cfg["db_user"], $cfg["db_pass"]);
if(!$mysql_pconnect){echo "Connection Failed"; exit; }
$db = mysql_select_db($cfg["db_name"], $mysql_pconnect);
if(!$db){echo"DB Select Failed"; exit;}


$edb = new EMAIL_TO_DB();
$edb->connect('imap.gmail.com', '/imap:993', 'h2udine@gmail.com', 'lanuovapergoogle');
$edb->do_action();

?>