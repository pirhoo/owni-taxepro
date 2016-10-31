<?php
if (!defined("INPHP")) die ("Not here");

define ("INC_DIR", "includes/");
define ("APP_NAME", "Réforme de la taxe professionnelle");
define ("APP_URI", "taxepro");

//// SMTP MAIL
require_once ("config.php");
require_once (INC_DIR . "essentials.php");
require_once (INC_DIR . "mysql.php");
require_once (INC_DIR . "smtp.php");
$mysql = new Mysql();

define("BASE_HREF", $config["basehref"]);
define("DOC_URL", BASE_HREF);
define("DOC_TITLE", "[application] ". APP_NAME);
define("DOC_TWUSER", "rfi");
define('THEME_DIR', '');

$localite_types = array ("commune"=>"commune", "dpt"=>"département", "epci"=>"epci", "region"=> "région");
?>
