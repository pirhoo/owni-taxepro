<?php
define ('INPHP', '1');
require_once ("load.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script src="jquery.validate.js" type="text/javascript"></script>
<link href="styles.css" rel="stylesheet" type="text/css" />
<!--[if IE]>
<link href="styles-ie.css" rel="stylesheet" type="text/css" />
<![endif]-->

<title>La Gazette des Communes - Réforme de la taxe professionnelle</title>
<script type="text/javascript">
var hash = document.location.hash;
var controlerId = 0;
var checkerId = 0;
var dataSet;
var localite = 0;
var safe1 = [146, 144];
var safe2 = [166, 163];

$(document).ready (function () {
    $("#feedbackForm").submit (function () {
        return false;
    });
    $("#feedbackForm").validate({errorLabelContainer: $("#error"), rules:{
        nom: {required: true},
        fonction: {required: true},
        ville: {required: true},
        fonction: {required: true},
        comment: {required: true},
        email: {required: true,email: true},
        errorContainer: $('label.error')},
        submitHandler: function (form) {return feedbackSend()}
    });

    if (hash.length>1) {
        hashVal = hash.substring(1, hash.length);
        if (hashVal != "") {
            loadData (hashVal);
        }
    }
    // listener to input
    $("#search").keyup(function () {
        if ( $("#search").val().length > 2) {
            var queryString = $("#search").val();
            controlerId++;
            $.post('suggest.php',{q: queryString, controlerId: controlerId}, function(data) {
                if (data.checkId == $('#controler').text()) {
                    $('#suggest').html(data.html);
                    $('#suggest').slideDown('fast');
                }
            }, "json");
            $('#controler').html(controlerId);
        }
        else {$('#suggest').slideUp('fast');}
    });
    $("#tab1").click(function() {
        if (localite>0) {showTab1();}
    });
    $("#tab2").click(function() {
        if (localite>0) {showTab2();}
    });
    $("#apropos").click(function () {
        showHide('#help', true);
    });
    $("#commentc2a").click(function () {
        showHide('#feedback', true);
    });
    $("div.close").click(function() {
        $(this).parent().css("display", "none");
    });
});

function showTab1 () {
        $("#tab1").css("background-image", 'url(img/tab_compensation.png)');
        $("#tab2").css("background-image", 'url(img/tab_compensation.png)');
        $("#content2").hide(function (){
            $(this).css('display', 'none');
            showHide ("#content2", false);
            $("#content1").show();
        });
        $("#export a").attr('href', 'dl.php?localite_id='+localite+'&mode=compensation');
}
function showTab2 () {
        $("#tab2").css("background-image", 'url(img/tab_ressources.png)');
        $("#tab1").css("background-image", 'url(img/tab_ressources.png)');
        $("#content1").hide(function () {
            $("#content2").show();
        });
        $("#export a").attr('href', 'dl.php?localite_id='+localite+'&mode=ressources');
}
function selectData (codeCommune, nomCommune) {
    $('#suggest').hide();
    $('#suggest').html('');
    document.location = "#"+codeCommune;
    loadData (codeCommune);
}

function loadData (codeCommune) {
    $.getJSON("feed.php",{code: codeCommune}, function (feed) {
        // populate dropdown menu
        var fngir = 0;
        var dotation = 0;
        var ressources = 0;
        var ressources_reforme = 0;
        
        if (feed.status == "200 OK") {
            showHide ("#help", false);
            var dataSet = feed.response;
            $("#cityname span").html(dataSet.nom_min);
            $("#search").val(dataSet.nom_min);
            localite = codeCommune;
            // blank all previously loaded data
            $("#data").fadeOut('slow', function () {
                // reset visible layers
                $("#content1").show('fast', function() {
                    $("#content2").hide();
                });
                for (var i in dataSet.data) {
                    thisSet = dataSet.data[i];
                    if (thisSet.annee == 2010){
                        if (thisSet.data_type == "ressources") {
                            $("#ress2010reforme").html(formatNumbers(thisSet.value)+" &euro;");
                            ressources = thisSet.value;
                        }
                        if (thisSet.data_type == "ressources_reforme") {
                            $("#ress2010").html(formatNumbers(thisSet.value)+" &euro;");
                            ressources_reforme = thisSet.value;
                        }
                        if (thisSet.data_type == "fngir") {
                            fngir = thisSet.value;
                        }
                        if (thisSet.data_type == "dotation") {
                            dotation = thisSet.value
                        }
                    }
                }
                $("#ressDotation").html((Math.round(dotation*10000/ressources)/100)+" %<br /><span>"+formatNumbers(dotation)+" &euro;</span>");
                $("#ressFNGIR").html((Math.round(fngir*10000/ressources)/100)+" %<br /><span>"+formatNumbers(fngir)+" &euro;</span>");

                lindex = (dataSet.fngir<0)?'2':'1';
                $("#legend").css('background-image', "url('img/legend_"+lindex+".png')");
            });

            //get comments
            $("#comments").fadeOut('slow', function() {
                $.getJSON("comments.php",{localite_id: dataSet.id}, function (comments) {
                   var content = "";
                   if (comments.response.length > 0) {
                       for (var i in comments.response) {content += '<li>'
                       + '<p class="auteur">' + comments.response[i].nom + '</p>'
                       + '<p class="auteur"><span>'+ comments.response[i].fonction +'</span>, <span>'+ comments.response[i].ville +'</span></p>'
                       + '<p class="comment">' + comments.response[i].comment + '</p>'
                       + '</li>';}
                   }
                   else {content += '<p>pas de commentaire pour l\'instant</p>';}
                   $("#comments").html('<ul>'+content+'</ul>');
                });
            });

            // set image size
            var sr = dataSet.sr;
            if (sr>0) {
                $("#safe2").animate(
                    {
                    "width": (safe2[0]*(sr>20?sr:20)/100)+"px",
                    "height": (safe2[1]*(sr>20?sr:20)/100)+"px",
                    "margin-top": ((safe2[1]-(safe2[1]*(sr>20?sr:20)/100))/2)+"px"
                    },
                    "slow");
            }

           // establish google chart
            $("#gchart").attr('src', 'charts/'+localite+'.png');
            $("#data").fadeIn('slow');
            $("#comments").fadeIn("slow");
            showTab1();
        }
    });
}

function feedbackSend () {
    // check form (validate)
    // send AJAX
    $.post('feedback.php',{email: $('#email').val(), localite_id: localite, nom: $('#nom').val(), ville: $('#ville').val(), comment: $('#comment').val(), fonction: $('#fonction').val()}, function(data) {
        if (data.status == '200 OK') {
            if (data.error) {showAlert('Error: '+data.message);}
            else {
                $(".field").val('');
                showAlert('Votre commentaire a bien été envoyé. Il sera validé ultérieurement. Merci.');
                showHide ('#feedback', false);
            }
        }
        else {
            showAlert('System error: could not communicate with server');
        }
    }, "json");
    $(".field").val('');
    return false;
}

function showAlert (msg) {
    $('#alertmessage').html(msg);
    showHide ('#alert', true);
    return true;
}
function showHide (elmt, show) {
    if (show) {
        $(elmt).show('fast');
    }
    else {
        $(elmt).hide('fast');
    }
    return false;
}
function formatNumbers(nStr) {
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? ',' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ' ' + '$2');
	}
	return x1 + x2;
}
</script>
</head>
<body>
    <div id="container">
            <div id="header">
                <div id="title">
                    <div id="apropos"></div>
                </div>

                <div id="searchpad">
                    Recherche par nom de collectivité<br />
                    <input type="text" name="search" id="search" onfocus="this.select()"></div>
                


                <div id="cityname">
                    <span>saisissez une collectivité ci-dessus</span>
                    <div id="pointer"></div>
                </div>
            </div>


            <div id="content">
                <div id="content1">
                    <div class="picto" id="picto2"><img src="img/safe_2.png" id="safe2" alt="Ressources après réforme"  /></div>
                    <div id="data">
                        <div id="ress2010"></div>
                        <div id="ress2010reforme"></div>
                        <div id="ressFNGIR"></div>
                        <div id="ressDotation"></div>
                    </div>
                </div>
                <div id="content2">
                    <!-- chart -->
                    <div id="legend"></div>
                    <img id="gchart" alt="Chart" alt="chart" src=""/>
                    <div id="timeline"></div>
                </div>
                <div id="export"><a href=""><img src="img/exportr.png" /></a></div>
            </div>
            
            <div id="commentsbar">
                <div id="middle">
                    <div id="comments"></div>
                </div>
            </div>
            <div id="commentc2a">
                Partagez-vous ces simulations de Bercy ?<br />
                Quelles sont vos propres simulations ?<br />
                Quelle analyse votre collectivité fait-elle des conséquences de cette réforme ?
            </div>
            <div id="footer">
                <img src="img/gz-logo.png" width="143" height="43" alt="Gazette des Communes" id="logo"/>
                <div id="social">
                    <div id="left"></div>
                    <div id="middle">
                            <!-- Les outils pour partager l'APP (Facebook, Twitter, etc) -->
                            <?php include(INC_DIR."inc.share.php"); ?>
                    </div>
                    <div id="right"></div>
                </div>
            </div>
            <div id="tabs">
                <div id="tab1"></div>
                <div id="tab2"></div>
            </div>
            <div id="suggest"><ul id="slist"></ul></div>
            <!-- les popups... -->
            <div id="feedback" class="popup">
                <div id="commentaire_titre"></div>
                <form id="feedbackForm" action="" method="post">
                    <div class="formrow">
                        <div class="formlabel"><span>votre nom</span></div>
                        <div class="forminput"><input name="nom" id="nom" value="" type="text" class="field" /></div>
                    </div>
                    <div class="formrow">
                        <div class="formlabel"><span>votre fonction</span></div>
                        <div class="forminput"><input name="fonction" id="fonction" value="" type="text" class="field" /></div>
                    </div>
                    <div class="formrow">
                        <div class="formlabel"><span>votre ville</span></div>
                        <div class="forminput"><input name="ville" id="ville" value="" type="text" class="field" /></div>
                    </div>
                    <div class="formrow">
                        <div class="formlabel"><span>votre adresse email</span></div>
                        <div class="forminput"><input name="email" id="email" value="" type="text" class="field" /></div>
                    </div>
                    <div class="formrow">
                        <div class="formlabel"><span>votre commentaire</span></div>
                        <div class="forminput"><textarea name="comment" id="comment" class="field"></textarea></div>
                    </div>
                    <div class="formrow">
                        <div class="formlabel"><label id="error">Tous les champs sont obligatoires<br /></label></div>
                        <div class="forminput"><input type="submit" name="envoyer" id="envoyer" value="envoyer" class="submit" /></div>
                    </div>
                </form>
                <div class="close"></div>
            </div>

            <div id="alert" class="popup">
                <div id="alertmessage" class="content"></div>
                <div class="close"></div>
            </div>

            <div id="help" class="popup">
                <div class="content">
                    <h2>Comprendre l'application</h2>
                    <div>
                        <div>L’application utilise les simulations de la réforme de la TP établies par le ministère de l’Economie en juillet 2010 ainsi que les données individuelles disponibles pour les ressources de fiscalité directe.<br></div>
                        <div>Cependant, les données concernant les communes pour les années 2004-2009 doivent être relativisées dans la mesure où il n’est pas encore possible de les reconstituer avec celles de l’EPCI auquel elles adhèrent.<br></div>
                        <div>L’application ne prend en compte que les « quatre vieilles » parmi les ressources de fiscalité directe : taxe d’habitation, foncier bâti, foncier non bâti et taxe professionnelle.<br></div>
                        <div>Enfin, concernant les départements, régions et EPCI, les données individuelles de Bercy sur leurs ressources de fiscalité directe ne sont pas disponibles pour les années 2004-2009. Apparait donc seulement la projection des simulations de Bercy. <br></div>
                        <div>Notre application évoluera dès que ces données seront disponibles.</div>
                        <div><br></div>
                        <div style="font-weight: bold">Attention : l’Etat a garanti aux collectivités qu’elles percevront en replacement de la TP, en 2010, le même montant que celui de 2009. L’infographie ci-dessous décompose la nouvelle ressource de remplacement (fiscalité et dotation) en 2010. Les deux « coffres » n’illustrent donc pas une perte ou un gain suite à la réforme de la TP, mais montrent la part des compensations mises en place en cas de perte, ou de gain (1er "coffre") dûs à la réforme. Le deuxième onglet permet de comparer la dynamique fiscale avant et après la réforme, jusqu'en 2015.</div>
                        <div><br></div>
                        <div><strong>Pratique :</strong> L’application reprend les intitulés de collectivités figurant dans les fichiers de Bercy. Il est parfois nécessaire de taper, pour les EPCI, le nom de la ville centre ou bien le nom exact de la collectivité. </div>
                        <div><br></div>
                        <div>Pour comprendre la réforme de la taxe professionnelle, lire notre <a href="http://infos.lagazettedescommunes.com/1323/lessentiel-la-taxe-professionnelle-en-debats/" target="_blank">dossier</a> et consulter nos <a href="http://infos.lagazettedescommunes.com/1328/comprendre-le-remplacement-de-la-taxe-professionnelle-infographie-interactive/" target="_blank">infographies</a></div>
                    </div>
                </div>
                <div class="close"></div>
            </div>
    </div>
    <div id="controler"></div>
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-18463169-3']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
</body>
</html>