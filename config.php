<?php
if (!defined('INPHP')) {die("This file cannot be accessed directly.");}

// SERVER configs
$config = array();

/*
* PROD
*/

$config["prod"]["status"] = "prod";
$config["prod"]["server_name"] = "app.owni.fr";
$config["prod"]["basehref"] = "http://".$config["prod"]["server_name"]."/".APP_URI;
$config["prod"]["email"] = "rmazon@lagazettedescommunes.com";
$config["prod"]["email_sender"] = $config["prod"]["server_name"];
$config["prod"]["admin_email"] = $config["prod"]["email"];

// Parametres mySQL
$config["prod"]["sql"] = array();
$config["prod"]["sql"]["server"] = "localhost"; // Serveur mySQL
$config["prod"]["sql"]["base"] = "appowni"; // Base de donnees mySQL
$config["prod"]["sql"]["login"] = "appowni"; // Login de connection a mySQL
$config["prod"]["sql"]["password"] = "pheePh3Ienga"; // Mot de passe pour mySQL
$config["prod"]['smtp_host'] = 'localhost';
$config["prod"]['smtp_port'] = 25;
$config["prod"]['smtp_username'] = '';
$config["prod"]['smtp_password'] = '';


/*
* DEV
*/

$config["dev"]["status"] = "dev";
$config["dev"]["server_name"] = $_SERVER["HTTP_HOST"];
$config["dev"]["basehref"] = "http://".$config["dev"]["server_name"]."/cust_".APP_URI;
$config["dev"]["email"] = "jerome@owni.fr";
$config["dev"]["email_sender"] = $config["dev"]["server_name"];
$config["dev"]["admin_email"] = $config["dev"]["email"];

// Parametres mySQL
$config["dev"]["sql"] = array();
$config["dev"]["sql"]["server"] = "localhost"; // Serveur mySQL
$config["dev"]["sql"]["base"] = "appowni"; // Base de donnees mySQL
$config["dev"]["sql"]["login"] = "root"; // Login de connection a mySQL
$config["dev"]["sql"]["password"] = "k1387069"; // Mot de passe pour mySQL

// SMTP
$config["dev"]['smtp_host'] = 'smtp.free.fr';
$config["dev"]['smtp_port'] = 587;
$config["dev"]['smtp_username'] = '';
$config["dev"]['smtp_password'] = '';

// Where are we?
if (!defined('CONFIG_STATUS')) {
	foreach ($config as $status => $conf) {
		$http_host = ($_SERVER["HTTP_HOST"])?$_SERVER["HTTP_HOST"]:$_SERVER['SERVER_NAME'];
		if (stristr($http_host,$conf["server_name"])) {
			define('CONFIG_STATUS', $status);
			break;
		}
	}
}
$config = $config[CONFIG_STATUS];
if (CONFIG_STATUS=="prod") error_reporting(0);
?>
