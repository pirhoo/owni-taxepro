<?php
define ('INPHP', '1');
require_once ("load.php");

$modes = array ("compensation", "ressources");

$localite_id = (int)($_GET["localite_id"]);
$mode = (in_array(trim($_GET["mode"]), $modes)?trim($_GET["mode"]):"");

// get info
if (!empty($localite_id) AND !empty($mode)) {
    $localite = $mysql->get_object ("SELECT * FROM `gz_localites` WHERE `id` = $localite_id LIMIT 1");

    if ($mysql->num_rows==1) {
        $feed = json_decode(file_get_contents(BASE_HREF."/feed.php?code=".$localite->id));
        $data = $feed->response;
        $img = imagecreatefromjpeg("img/_fond.jpg");
        
        $filename = ("img/gz-logo.png");
        list ($width, $height) = getimagesize($filename);
        $logo = imagecreatefrompng($filename);
        imagecopy($img, $logo, 4, 623, 0, 0, $width, $height);

        $black = imagecolorallocate($img,0,0,0);
        
        if ($mode == "compensation") {
            $tfilename = ("img/tab_compensation.png");
            $tab = imagecreatefrompng($tfilename);

            // contenu
            $filename = ("img/compensation.png");
            list ($width, $height) = getimagesize($filename);
            $content = imagecreatefrompng($filename);
            imagecopy($img, $content, 120, 140, 0, 0, $width, $height);

            // safes
            $filename = ("img/safe_2.png");
            list ($width, $height) = getimagesize($filename);
            $content = imagecreatefrompng($filename);
            imagecopy($img, $content, 120, 360, 0, 0, $width, $height);

            // texts
            // number_format($number, 2, ',', ' ');
            imagettftext($img, 13, 0, 435, 318, $black, dirname(__FILE__)."/Trebuchet_MS.ttf", utf8_encode(number_format($data->ressFNGIR, 2, ',', ' ')." %"));
            imagettftext($img, 11, 0, 435, 338, $black, dirname(__FILE__)."/Trebuchet_MS.ttf", utf8_encode(number_format($data->fngir, 0, ',', ' ')." ".utf8_encode('&#8364;')));

            imagettftext($img, 13, 0, 605, 278, $black, dirname(__FILE__)."/Trebuchet_MS.ttf", utf8_encode(number_format($data->ressDotation, 2, ',', ' ')." %"));
            imagettftext($img, 11, 0, 605, 298, $black, dirname(__FILE__)."/Trebuchet_MS.ttf", utf8_encode(number_format($data->dotation, 0, ',', ' ')." ".utf8_encode('&#8364;')));

            imagettftext($img, 13, 0, 304, 428, $black, dirname(__FILE__)."/Trebuchet_MS.ttf", utf8_encode(number_format($data->ressources, 0, ',', ' ')." ".utf8_encode('&#8364;')));
            imagettftext($img, 13, 0, 280, 245, $black, dirname(__FILE__)."/Trebuchet_MS.ttf", utf8_encode(number_format($data->ressources_reforme, 0, ',', ' ')." ".utf8_encode('&#8364;')));
        }
        if ($mode == "ressources") {
            $tfilename = ("img/tab_ressources.png");
            $tab = imagecreatefrompng($tfilename);

            // legend
            $filename = ("img/graph_timeline.png");
            list ($width, $height) = getimagesize($filename);
            $content = imagecreatefrompng($filename);
            imagecopy($img, $content, 124, 570, 0, 0, $width, $height);

            $filename = ("img/legend_".(($data->fngir<0)?'2':'1').".png");
            list ($width, $height) = getimagesize($filename);
            $content = imagecreatefrompng($filename);
            imagecopy($img, $content, 0, 75, 0, 0, $width, $height);

            // chart
            imagecopy($img, imagecreatefromstring(base64_decode($data->gchart)), 200, 170, 0, 0, 560, 400);
            
        }
        list ($width, $height) = getimagesize($tfilename);
        imagecopy($img, $tab, 600, 21, 0, 0, $width, $height);
        
        // title
        $title = imagecreatetruecolor(305,25);
        $white = imagecolorallocate($title, 255, 255, 255);
        $grey = imagecolorallocate($title, 76, 76, 76);
        $black = imagecolorallocate($title,0,0,0);
        imagefill($title, 0,0, $grey);
        imagefilledrectangle ($title, 2, 2, 302, 22, $white);
        imagettftext($title, 13, 0, 4, 18, $black, dirname(__FILE__)."/Trebuchet_MS.ttf", utf8_encode($localite->nom_min));
        imagecopy($img, $title, 280, 30, 0, 0, 305, 25);

        // schema

        header('Content-type: image/jpeg');
        header('Content-Disposition: attachment; filename="Reforme-taxe-pro_'.sanitize_string(utf8_encode($localite->nom_min)).'_'.$mode.'.jpg"');
        imagejpeg($img);
    }
}
?>
