<html>
<body>
<?php
error_reporting(0);

$hostname_bd_projet = "localhost";
$database_bd_projet = "appowni";
$username_bd_projet = "appowni";
$password_bd_projet = "pheePh3Ienga";
$db = mysql_pconnect($hostname_bd_projet, $username_bd_projet, $password_bd_projet) or trigger_error(mysql_error(), E_USER_ERROR);
mysql_select_db($database_bd_projet);

//QUERIES THE DB
$query = "SELECT  d.localite_id AS id, l.nom_caps as name,  `value`
FROM gz_data as d, gz_localites as l
WHERE (
`annee` =2004
OR  `annee` =2009
)
AND data_type =  'ressources'
AND l.id = d.localite_id
ORDER BY id DESC";

$result = mysql_query($query);
 $localites = array();
$localite_id_prev = "";
while ($row = mysql_fetch_array($result)){
    $localite_id = $row['id'];
    $localite_name = addslashes($row['name']);
    if ($localite_id == $localite_id_prev){
        //2009 data
        $localites[$localite_id]["2009"] = $row['value'];
    }else{
        //new row and 2004 data
        $localites[$localite_id]["2004"] = $row['value'];
        $localites[$localite_id]["id"] = $row['id'];
        $localites[$localite_id]["name"] = addslashes($row['name']);
    }
    $localite_id_prev = $localite_id;
}
//COMPUTES GROWTH RATE
foreach ($localites as $id=>$localite){
	if ($localite["2004"]>0)
    $localites[$id]["growth"] = ($localite["2009"] - $localite["2004"]) / $localite["2004"];
}

//SORTS THE ARRAY BY $growth
function cmp($a, $b)
{
    return strcmp($a["growth"], $b["growth"]);
}

usort($localites, "cmp");

//DISPLAYS THE ARRAY
$i = 0;
echo "<table>";
echo "<tr><td>rank</td><td>NAME</td><td>GROWTH</td><td>2004</td><td>2009</td><td>LINK</td></tr>";
foreach ($localites as $id=>$localite){
    echo "<tr><td>".$i++."</td><td>". $localite["name"] ."</td><td>". round($localite["growth"]*100, 2) ."</td><td>". $localite["2004"] ."</td><td>". $localite["2009"] ."</td><td><a href ='http://app.owni.fr/taxepro/#". $localite["id"] ."'>link</a></td></tr>";
}
echo "</table>";
?>
</body>
</html>