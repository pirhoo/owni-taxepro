<?php

@header('Content-Type: text/html; charset=UTF-8');

define ('INPHP', '1');
require_once ("load.php");

// encaisse les données
// enregistre en db
// envoie lien de validation par mail

$comment = trim(strip_tags($_POST["comment"]));
$fonction = trim(strip_tags($_POST["fonction"]));
$nom = trim(strip_tags($_POST["nom"]));
$ville = trim(strip_tags($_POST["ville"]));
$email = trim(strip_tags($_POST["email"]));
$localite_id = (int)$_POST["localite_id"];

$output = new StdClass;
$output->status = "200 OK";
$output->error = false;
$output->message = "";

if (!empty($comment) AND !empty($fonction) AND !empty($ville) AND !empty($nom)) {
    $uid = generate_uid(12);
    if ($mysql->query("INSERT INTO `gz_comments` (`uid`, `localite_id`, `nom`, `fonction`, `ville`, `comment`, `email`) VALUES ('".input2query($uid)."','".input2query($localite_id)."','".input2query(utf8_decode($nom))."','".input2query(utf8_decode($fonction))."','".input2query(utf8_decode($ville))."', '".input2query(utf8_decode($comment))."', '".input2query($email)."')")) {
        if (!empty($mysql->insert_id)) {
             $mailbody = 'IP: '.$_SERVER["REMOTE_ADDR"]."\n"
            .'nom: '.utf8_decode($nom)."\n"
            .'fonction: '.utf8_decode($fonction)."\n"
            .'ville: '.utf8_decode($ville)."\n"
            .'commentaire: '."\n"
            .utf8_decode($comment)
            ."\n\n"
            .'Pour valider ce commentaire, cliquez sur le lien ci-dessous:'."\n"
            .BASE_HREF.'/validate.php?uid='.$uid;

            $headers = "From: \"".DOC_TITLE."\" <{$config["admin_email"]}>\r\nX-originating-IP: {$_SERVER["REMOTE_ADDR"]}\r\nContent-Type: text/plain;";

            if (!smtpmail( $config["admin_email"] ,  DOC_TITLE  , $mailbody, $headers )) {
                $output->message = "Sorry! There was an error sending your contribution.";
                $output->error = true;
            }
            else 
                $output->message = "Comment registered and notification sent.";
        }
        else {
            $output->error = true;
            $output->message = "FATAL ERROR: no id returned";            
        }
    }
    else {
        $output->error = true;
        $output->message = $mysql->error;
    }
}
else {
    $output->error = true;
    $output->message = "Some fields are missing.";
}

echo (json_encode($output));
?>
