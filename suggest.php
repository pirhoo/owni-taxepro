<?php
define ('INPHP', '1');
require_once ("load.php");

$output = new stdClass();
$output->status = "200 OK";
$output->message = "";
$output->error = false;

$output->arg = utf8_decode(trim($_POST["q"]));
$output->html = "";
$output->checkId = trim($_POST["controlerId"]);

$first = array();
$second = array();

if (strlen($output->arg)>2) {
    if ($mysql->query ("SELECT `id`, `nom_min`, `type`, `code_dpt` FROM `gz_localites` WHERE `nom_min` LIKE '%".input2query(str_replace(" ", "-", $output->arg))."%' OR `nom_min` LIKE '%".input2query($output->arg)."%' ORDER BY `nom_min` ASC ")) {
        $output->results = $mysql->num_rows;
        if (empty($mysql->num_rows)) $output->html = "aucun résultat";
        else {
            foreach ($mysql->result as $result){
                $selected = (empty($output->html)?'class="selected"':'');
                $result->nom_min = utf8_decode($result->nom_min);
                $item = (preg_replace(array('/('.(str_replace(" ", "-", htmlentities($output->arg))).')/i', '/('.(htmlentities($output->arg)).')/i'), '<b>$1</b>', htmlentities($result->nom_min))).' ('.htmlspecialchars($localite_types[$result->type]).''.((!empty($result->code_dpt) AND $result->type != "dpt")?' du département '.$result->code_dpt:'').')';
                if ($mysql->num_rows < 20 OR (stripos($result->nom_min, $output->arg) == 0 AND strlen($output->arg) == strlen($result->nom_min))) {
                    $var =  (stripos($result->nom_min, $output->arg) == 0)?"first":"second";
                    // show only exact match
                    ${$var}[] .= '<li id="'.$result->code_commune.'" '.$selected.' onClick="selectData(\''.$result->id.'\', \''.addslashes(htmlentities($result->nom_min)).'\')">'.$item.'</li>';
                }
            }
            $output->html = $mysql->num_rows." résultat".($mysql->num_rows>0?'s':'')."<ul>".implode("",$first).implode("",$second)."</ul>";
        }
    }
    else {
        $output->error = true;
        $output->message = $mysql->error;
    }
}
echo json_encode($output);
?>
