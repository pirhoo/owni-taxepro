<style type="text/css">

    .sharing { position:relative;height:17px; top:0px; float:right; }
    
    .sharing .inputEmbed span {
        color:black;
        font-weight:bold;
        text-decoration:underline;
        cursor:pointer;
    }

    .sharing a, .sharing .share{
       font-size:12px;
       color:gray;
       text-decoration:none;
       float:right;
       margin-right:5px;
    }

    .sharing .bg-white {
        background:white;
        padding:1px 2px;
        padding-bottom:0px;
        height:17px;
        border-radius:3px;
        -webkit-border-radius:3px;
        -moz-border-radius:3px;
    }

    .sharing a img{
       border:0px;
       margin:0px;
       padding:0px;
    }
</style>

<script type="text/javascript">
    function getEmbed() {
        $(".sharing .share").hide();
        $(".sharing .inputEmbed").show();
        return false;
    }

    function dropEmbed() {
        $(".sharing .share").show();
        $(".sharing .inputEmbed").hide();
    }
    
</script>

<div class="sharing">
    
    <span class="share inputEmbed" style="display:none; margin-top:-4px;" >
        <input value='<a href="<?php echo (DOC_URL); ?>" target="_blank"><img src="<?php echo (BASE_HREF); ?>/img/embed.jpg" alt="<?php echo (DOC_TITLE);  ?>" /></a>' />
        <span onclick="dropEmbed();">Fermer</span>
    </span>

    <a class="share mini-embed bg-white " href="#" onclick="return getEmbed()">
        &lt;integrer&gt;
    </a>

    <a class="share mini-share-mail bg-white" 
       target="_blank"
       href='http://www.addtoany.com/email?linkurl=<?php echo  rawurlencode(DOC_URL);  ?>&linkname=<?php echo   rawurlencode(DOC_TITLE);  ?>&t=<?php echo rawurldecode(DOC_TITLE); ?>'>
        <img alt="share mail" src="<?php echo THEME_DIR."img/mini-email.png"; ?>" /> email
    </a>

    <a class="share facebook" name="fb_share" type="button" share_url="<?php echo (DOC_URL);  ?>" href="http://www.facebook.com/sharer.php">Partager</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>

    <span class="share twitter">
        <a href="http://twitter.com/share" class="twitter-share-button" data-url="<?php echo (DOC_URL); ?>" data-text="<?php echo (DOC_TITLE); ?>" data-count="horizontal" data-via="<?php echo (DOC_TWUSER); ?>">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
    </span>
    
</div>