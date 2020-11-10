<?php
if (!isset($_SESSION)) {
    session_start();
} 
error_log('display.php');
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
    if(array_key_exists('display_title',$settings)){
        $display_title = $settings['display_title'];
    } else {
        $display_title = 'Current Orders';
    }
    if(array_key_exists('default_wait',$settings)){
        $default_wait = $settings['default_wait'];
    } else {
        $default_wait = '30';
    }
    
}
$site_title = "CFD Display";
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
            .wrapper {
                background-image:url('uploads/images/<?php echo $set_bgimage; ?>');
            }
            .cfd-header-logo {
                background-image:url('uploads/images/<?php echo $set_logo; ?>');
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
        </style>    
    <head>
    <body>
        <div class="wrapper">
            <?php
            echo "<div class='cfd-container'>";
                echo "<div class='cfd-header'>";
                    echo "<div class='cfd-header-content'>";
                        echo "<div class='cfd-header-logo'></div>";
                        echo "<div class='cfd-header-title'>";
                            echo $display_title;
                        echo "</div>";
                        echo "<div class='cfd-header-estimate' id='lead-time'>Wait Time: ".$default_wait." Mins.</div>";
                    echo "</div>";
                echo "</div>";
                echo "<div class='cfd-content' id='iframe'>";
                echo "</div>";
            echo "</div>";
          ?>
        </div>
    </body>
    <script>
        var queryDict = {}
        var lastOrders = new Array();
        location.search.substr(1).split("&").forEach(function(item) {queryDict[item.split("=")[0]] = item.split("=")[1]})
        // START RELOAD DATA
        var still_fetching = false;
        setInterval(function(){ 
            if (still_fetching) {
                return;
            }
            still_fetching = true;
            loadOrders();
            
        }, 3000);
        loadOrders()
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
                    //console.log(this.responseText);
                    var orders = JSON.parse(this.responseText).orders;
                    if(orders.length != 0){
                        if(lastOrders.length != orders.length){
                            var output = document.createElement('div');
                            for(var i in orders){
                                var classer = "";
                                var div = document.createElement('div');
                                div.classList.add("cfd-cell");
                                div.classList.add("display-cell");
                                var title = '';
                                if(orders[i].title.includes('SOO-')){
                                    title = orders[i].abbr_name;
                                } else {
                                    title = orders[i].title;
                                }
                                // IF PAYMENT NEEDED DO NOT SHOW AS GREEN
                                if(orders[i].payment_state == "OPEN"){
                                    title += " (See Cashier)"
                                }else if(orders[i].order_state == 'READY'){
                                    div.classList.add("cell-hl-bg");
                                }
                                var span = document.createElement('span');
                                var spanText = document.createTextNode(title);
                                span.appendChild(spanText);
                                div.appendChild(span);
                                output.appendChild(div);
                            }
                            document.getElementById('iframe').innerHTML = output.innerHTML;
                            lastOrders = orders
                        }
                    } else {
                        var output = document.getElementById('iframe');
                        var div = document.createElement('div');
                        output.innerHTML = ""
                        div.classList.add("cfd-cell","display-cell");
                        var span = document.createElement('span');
                        var spanText = document.createTextNode("No Current Orders");
                        span.appendChild(spanText);
                        div.appendChild(span);
                        output.appendChild(div);
                    }
                    still_fetching = false;
                }
            }
            xhr.send();
        } 
        //START LEADTIME
        var fetching_leadtime = false
        setInterval(function(){ 
            if (fetching_leadtime) {
                return;
            }
            fetching_leadtime = true;
            loadLeadTime();
        }, 3000);
        loadLeadTime();
        function loadLeadTime(){
            var myHeaders = new Headers();
            myHeaders.append('pragma', 'no-cache');
            myHeaders.append('cache-control', 'no-cache');
            var myInit = {
                method: 'GET',
                headers: myHeaders,
            };
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'https://veganmob.biz/cfd/lead-time.php?d='+Date.now(), myInit);
            xhr.onload = function(){
                if(this.status == 200){
                    console.log(this.responseText);
                    let data = JSON.parse(this.responseText);
                    var output = document.createElement('div');
                    let estimate = data.estimate;
                    let defaulter = data.default;
                    if(estimate){
                        let spanText = document.createTextNode('Wait Time:  '+estimate+' mins.');
                        output.appendChild(spanText);
                    } else {
                        let spanText = document.createTextNode('Wait Time:  '+defaulter+' mins.');
                        output.appendChild(spanText);
                    }

                    document.getElementById('lead-time').innerHTML = output.innerHTML;
                    fetching_leadtime = false;
                }
            }
            xhr.send();
        }
        //START UPDATE DATA
        var still_updating = false;
        setInterval(function(){ 
            if (still_updating) {
                return;
            }
            still_updating = true;
            updateDBOrders();
        }, 60000);
        updateDBOrders()
        //need to update a bit this function
        function updateDBOrders(){
            var myHeaders = new Headers();
            myHeaders.append('pragma', 'no-cache');
            myHeaders.append('cache-control', 'no-cache');

            var myInit = {
                method: 'GET',
                headers: myHeaders,
            };
            var xhr = new XMLHttpRequest();

            xhr.open('GET', 'https://veganmob.biz/cfd/orders-to-db.php?d='+Date.now(), myInit);

            xhr.onload = function(){
                if(this.status == 200){
                    //console.log(this.responseText);
                    still_updating = false; 
                }
            }
            xhr.send();
        } 
    </script>
</html>
