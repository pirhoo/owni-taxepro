<?php

@header('Content-Type: text/html; charset=UTF-8');

define ('INPHP', '1');
require_once ("load.php");

$data = new stdClass();

$id = (int)($_GET["code"]);
$gcharturl = "http://chart.apis.google.com/chart?&cht=bvo&chs=650x400&chd=t:";

$data->status = "200 OK";
$data->error = false;
$data->message = "";

$data->response = new stdClass();

if (!empty($id)) {
    if ($mysql->query ("SELECT * FROM `gz_localites` as l WHERE l.`id` = '".$id."' LIMIT 1")) {

        if ($mysql->num_rows > 0) {
            $data->response = $mysql->result[0];
            $data->response->nom_min = ($data->response->nom_min);
            $mysql->query ("SELECT * FROM `gz_data` as d WHERE d.`localite_id` = {$data->response->id} ORDER BY `annee` ASC");
            $data->response->data = $mysql->result;
            
            $rgchdata = array();
            $max=false;

            for ($i=0; $i<$mysql->num_rows; $i++) {
                $rdata = $data->response->data[$i];
                $rgchdata[$rdata->annee][$rdata->data_type] = $rdata->value;
            }
            
            $gchart = array();
            $chxl = "0:";
            for ($year = 2004; $year<=2015; $year++) {
                $rdata = $rgchdata[$year];
                $chxl .= "|".$year;
                // < 2010
                $gchart[0][$year] = $rdata["TP"]+0;
                $gchart[1][$year] = $rdata["ressources"]+0;
                // = 2010
                $gchart[2][$year] = ($year==2010)?$rdata["ressources"]+0:0;
                $gchart[3][$year] = ($year>=2010)?$rgchdata[2010]["dotation"]+0:0;
                
                if ($rgchdata[2010]["fngir"]>0) {
                    $gchart[4][$year] = ($year>=2010)?$rgchdata[2010]["fngir"]+0:0;
                    $gchart[5][$year] = ($year>2010)?$rdata["ressources_reforme"]:0;
                    $gchart[6][$year] = 0;
                }
                else {
                    $gchart[4][$year] = 0;
                    $gchart[5][$year] = ($year>2010)?$rdata["ressources_reforme"]+0:0;
                    $gchart[6][$year] = ($year>2010)?$gchart[5][$year]-$rgchdata[2010]["fngir"]+0:0;
                    $gchart[6][$year] = ($year==2010)?$gchart[2][$year]-$rgchdata[2010]["fngir"]+0:$gchart[6][$year];
                }
            }
            $data_chart = array();

            foreach ($gchart as $set=>$rdata) {
                foreach ($rdata as $v) {if (!$max OR $v>$max) $max = $v;}
                $data_chart[$set] = implode(",", $rdata);
            }
            $lmin = "";
            $lmax = "";
            if ($max>1E6)
            $chxl = "0:|0|".round(($max)/4/1E6,1)." M EUR|".round($max/2/1E6,1)." M EUR|".round(($max)/4/1E6*3,1)." M EUR|".round($max/1E6,1)." M EUR";
            else
            $chxl = "0:|0|".round(($max)/4/1E3,1)." k EUR|".round($max/2/1E3,1)." k EUR|".round(($max)/4/1E3*3,1)." k EUR|".round($max/1E3,1)." k EUR";
            
            $data->response->gchart = base64_encode(getGoogleChart($data_chart, 0, $max, $chxl));
            $data->response->fngir = $rgchdata[2010]["fngir"];
            $data->response->ressources_reforme = $rgchdata[2010]["ressources_reforme"];
            $data->response->ressources = $rgchdata[2010]["ressources"];
            $data->response->dotation = $rgchdata[2010]["dotation"];
            $data->response->ressDotation = round($data->response->dotation*10000/$data->response->ressources)/100;
            $data->response->ressFNGIR = round($data->response->fngir*10000/$data->response->ressources)/100;
            
            $data->response->sr = ($data->response->ressources>0)?round($data->response->ressources*100/$data->response->ressources_reforme):0;
            if ($data->response->sr<20) $data->response->sr = 20;
            if ($data->response->sr>100) $data->response->sr = 110;
        }
        else {
            $data->error = true;
            $data->message = "aucun résultat";
        }
    }
    else {
        $data->error = true;
        $data->message = $mysql->error;
    }
}
echo json_encode($data);


function getGoogleChart($data, $min, $max, $chxl) {
    $output = "";
    
    // Create some random text-encoded data for a line chart.
    $url = 'http://chart.apis.google.com/chart?chid=' . md5(uniqid(rand(), true));
    $chd = 't:'.implode("|",$data);

    // Add data, chart type, chart size, and scale to params.
    $chart = array(
    'cht' => 'bvo',
    'chs' => '560x400',
    'chds' => $min.','.$max,
    'chxt' => 'y',
        'chxl' => $chxl,
    //'chxr' => '0,'.$min.','.$max,
        'chf' => 'bg,s,ffffff00',
        'chbh' => '29,6',
    //'chdl' => 'Taxe professionnelle|TH+bati|Ressources|Dotation|FNGIR+|Ressources après réforme|FNGIR-',
    'chco' => "009d9f,8e3961,f15a37,497692,d8916b,f15a37,dac4ad",
    'chd' => $chd);

    // Send the request, and print out the returned bytes.
    $context = stream_context_create(
        array('http' => array(
        'method' => 'POST',
        'content' => http_build_query($chart))));

    $handle = fopen($url, 'r', false, $context);
    $string = stream_get_contents($handle);
    
    global $id;
    $tofile = fopen("charts/".$id.".png", "w");
    fwrite($tofile, $string);
    fclose ($handle);
    /*header("content-type: image/png");
    fpassthru($handle);*/
    return $string;
}
?>
