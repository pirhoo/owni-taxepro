<?php
@header('Content-Type: text/html; charset=UTF-8');

define ('INPHP', '1');
require_once ("load.php");

$localite_id = (int)$_GET["localite_id"];

$output = new StdClass;
$output->status = "200 OK";
$output->error = false;
$output->message = "";
$output->response = array();
$output->localite_id = 0;

if (!empty($localite_id)) {
    if ($mysql->query("SET NAMES utf8") AND $mysql->query ("SELECT * FROM `gz_comments` WHERE `localite_id` = $localite_id AND `validated` > 0 ORDER BY `created` DESC")) {
        $output->localite_id = $localite_id;
        $output->response = $mysql->result;
        for ($i=0; $i<$mysql->num_rows; $i++) {
            $output->response[$i]->nom = nl2br(prep4json($output->response[$i]->nom));
            $output->response[$i]->ville = nl2br(prep4json($output->response[$i]->ville));
            $output->response[$i]->fonction = nl2br(prep4json($output->response[$i]->fonction));
            $output->response[$i]->comment = nl2br(prep4json($output->response[$i]->comment));
        }
    }
    else {
        $output->error = true;
        $output->message = $mysql->error;
    }
}
echo json_encode($output);
?>
