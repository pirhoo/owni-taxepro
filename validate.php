<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
<body>
<?php
define ('INPHP', '1');
require_once ("load.php");

$uid = trim($_GET["uid"]);

if (!empty($uid)) {
    if ($mysql->query ("UPDATE `gz_comments` SET `validated` = CURRENT_TIMESTAMP WHERE `uid` = '".input2query($uid)."' LIMIT 1")) {
        if (!empty($mysql->affected_rows)) {
            echo "Commentaire validé avec succès.";
        }
        else {
            echo "Commentaire déjà validé ou inexistant.";
        }
    }
    else {
        echo "ERREUR de requête: ".$mysql->error;
    }
}
?>
</body>
</html>