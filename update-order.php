<?php
if (!isset($_SESSION)) {
    session_start();
}
if(isset($_GET['oid']) && isset($_GET['state']) && isset($_SESSION['mid']) && isset($_SESSION['token'])){
    include 'get-orders.php';
     //include 'send-text.php';
    $oid = $_GET['oid'];
    $dater = gmdate("Y-m-d H:i:s");
    if($_GET['state'] == 'done'){
        $state = 'DONE';
        $result = update_order_pickup($oid,$state,$dater);
        //echo $result;
    } elseif($_GET['state'] == 'ready'){
        $state = 'READY';
        $result = update_order_ready($oid,$state,$dater);
    } else {
        
    }
} else {
}
header("Location: {$redirect_uri}controller.php");