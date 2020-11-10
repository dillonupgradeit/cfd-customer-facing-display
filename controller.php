<?php
if (!isset($_SESSION)) {
    session_start();
}
error_log('controller.php');
if(!isset($_SESSION['token']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in']){
    header('Location: login.php');
}
if(isset($_SESSION['mid'])){
    include 'settings.php';
    $mid = $_SESSION['mid'];
    $settings = get_cfd_settings($mid);
    //var_dump($settings);
    
    if(array_key_exists('logo',$settings)){
        $set_logo = $settings['logo'];
    } else {
        $set_logo = 'default.png';
    }
    if(array_key_exists('bg_image',$settings)){
        $set_bgimage = $settings['bg_image'];
    } else {
        $set_bgimage = 'default-bg.jpg';
    }

    if(array_key_exists('color_text',$settings)){
        $color_text = $settings['color_text'];
    } else {
        $color_text = '#000000';
    }
    if(array_key_exists('color_hl_text',$settings)){
        $color_hl_text = $settings['color_hl_text'];
    } else {
        $color_hl_text = '#ffffff';
    }
    if(array_key_exists('color_hl_bg',$settings)){
        $color_hl_bg = $settings['color_hl_bg'];
    } else {
        $color_hl_bg = '#9ed637';
    }
    if(array_key_exists('title_display',$settings)){
        $title_display = $settings['title_display'];
    } else {
        $title_display = 'Current Orders';
    }
    if(array_key_exists('default_wait',$settings)){
        $default_wait = $settings['default_wait'];
    } else {
        $default_wait = '30';
    }
    
}
$site_title = 'CFD Controller';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
    <head>
    <title><?php echo $site_title; ?></title>
          <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
          <link rel="icon" href="images/favicon.ico" type="image/x-icon">

          <link rel="stylesheet" href="resources/css/style.css?d=<?php echo time(); ?>" type="text/css" media="screen and (min-device-width : 641px)" />
          <link href="resources/css/style.css?d=<?php echo time(); ?>" rel="stylesheet" type="text/css" media="(orientation:portrait)" />
          <link href="resources/css/style.css?d=<?php echo time(); ?>" rel="stylesheet" type="text/css" media="(orientation:landscape)" />
          <meta name="viewport" content="width=device-width, initial-scale=1.0" />
          <style>
            @font-face {
                font-family: "Lato";
                src: url("/resources/font/Lato-Black.ttf") format("truetype");
            }
            .wrapper {
                background-image:url('uploads/images/<?php echo $set_bgimage; ?>');
            }
            .cfd-header,.cell-hl-bg{
                background:<?php echo $color_hl_bg; ?>;
                -moz-box-shadow: inset 7px -2px 3px -7px #000000;
                -webkit-box-shadow: inset 7px -2px 3px -7px #000000;
                box-shadow: inset 7px -2px 3px -7px #000000;
            }
            .cfd-header,.cell-hl-bg span {
                color:<?php echo $color_hl_text; ?>;
            }
            .cfd-cell {
                color:<?php echo $color_text; ?>;
            }
            .btn-ready{
                background:<?php echo $color_hl_bg; ?>;
            }
        </style>   
    <head>
    <body>
    <?php  
            if(isset($_SESSION['error'])){
                if($_SESSION['error'] == true){
                    $class = ' alert-red';
                } else {
                    $class=" alert-green";
                }
                echo '<div class="alert'.$class.'" id="alert" style=display:block;>';
                    echo '<span class="closebtn" onclick=closeAlertBox()>&times;</span>';
                    echo '<strong>'.$_SESSION["e_message"].'</strong>';
                echo '</div>';
                $_SESSION['e_message'] = '';
                unset($_SESSION['error']);
            } ?>
        <div class="wrapper">
           
            <?php
            echo "<div class='cfd-container-cont'>";
                echo "<div class='cfd-content-full' id='iframe'>";
                echo "</div>";
            echo "</div>";
          ?>
        </div>
    </body>
    <script>


var still_fetching = false;
var lastOrders = new Array();
// fetch data every 3 seconds (3000)
setInterval(function(){ 
     if (still_fetching) {
         return;
     }
     still_fetching = true;
     loadOrders();
}, 3000);
loadOrders()
//need to update a bit this function
function loadOrders(){
        var myHeaders = new Headers();
        myHeaders.append('pragma', 'no-cache');
        myHeaders.append('cache-control', 'no-cache');

        var myInit = {
            method: 'GET',
            headers: myHeaders,
        };
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'https://veganmob.biz/cfd/orders-loop.php?d='+Date.now(), myInit);

        xhr.onload = function(){
            if(this.status == 200){
                // console.log(this.responseText);
                var orders = JSON.parse(this.responseText).orders;
                var twilio = JSON.parse(this.responseText).twilio;
                console.log(this.responseText)
                if(orders.length != 0){
                    if(lastOrders.length != orders.length){
                        var output = document.createElement('div');
                        for(var i in orders){
                            var classer = "";
                            var div = document.createElement('div');
                            div.classList.add("cfd-cell");
                            div.classList.add("controller-cell");
                            var title = '';
                            if(orders[i].title.includes('SOO-')){
                                title = orders[i].full_name+" - "+orders[i].title;
                            } else {
                                title = orders[i].title;
                            }
                            if(orders[i].payment_state == "OPEN"){
                                div.classList.add("cell-red");
                                title += " (Payment Needed)"
                            }else if(orders[i].order_state == 'READY'){
                                div.classList.add("cell-hl-bg");
                            }
                            var span = document.createElement('span');
                            var spanText = document.createTextNode(title);
                            span.appendChild(spanText);
                            div.appendChild(span);
                            // output += "<div class='cfd-cell"+classer+"'><span>" + title + "</span>";
                            if(orders[i].order_state == 'PREPARING'){
                                var a = document.createElement('a');
                                var linkText = document.createTextNode("Ready");
                                a.appendChild(linkText);
                                a.classList.add('btn-control');
                                a.classList.add('btn-ready');
                                a.title = "Ready";
                                a.href = "update-order.php?state=ready&oid="+orders[i].id;
                                div.appendChild(a);
                                // output += "<a href='update-ready.php?oid="+orders[i].id+"' class='btn-control btn-ready'>Ready</a>";
                            } else if(orders[i].order_state == 'READY'){
                                // SEND TEXT 
                                // console.log('mis',orders[i].title,orders[i].message_id)
                                if(twilio == true && orders[i].has_phone == true){
                                    if(orders[i].message_id == null ){
                                        var a1 = document.createElement('a');
                                        var linkText = document.createTextNode("Text");
                                        a1.appendChild(linkText);
                                        a1.classList.add('btn-control');
                                        a1.classList.add('btn-done');
                                        a1.title = "Text";
                                        a1.href = "send-text.php?oid="+orders[i].id;
                                        div.appendChild(a1);
                                    } else {
                                        var a1 = document.createElement('div');
                                        var linkText = document.createTextNode("Text Sent!");
                                        a1.appendChild(linkText);
                                        a1.classList.add('btn-control');
                                        a1.classList.add('btn-sent');
                                        a1.title = "Text Sent!";
                                        div.appendChild(a1);
                                    }
                                }
                                var a = document.createElement('a');
                                var linkText = document.createTextNode("Done");
                                a.appendChild(linkText);
                                a.classList.add('btn-control');
                                a.classList.add('btn-done');
                                a.title = "Done";
                                a.href = "update-order.php?state=done&oid="+orders[i].id;
                                div.appendChild(a);
                            }
                            // output = "</div>";
                            output.appendChild(div);
                            // document.getElementById('iframe').appendChild(div);
                        }
                        document.getElementById('iframe').innerHTML = output.innerHTML;
                    }
                } else {
                    var output = document.getElementById('iframe');
                    var div = document.createElement('div');
                    output.innerHTML = ""
                    div.classList.add("cfd-cell","controller-cell");
                    var span = document.createElement('span');
                    var spanText = document.createTextNode("No Orders");
                    span.appendChild(spanText);
                    div.appendChild(span);
                    output.appendChild(div);
                }
                still_fetching = false;
            }
        }
        xhr.send();
  } 
    </script>
</html>
