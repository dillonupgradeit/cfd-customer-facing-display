<?php
if (!isset($_SESSION)) {
    session_start();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/wp-db.php';
if(isset($_SESSION["mid"])){
    $merchant_id = $_SESSION["mid"];
} elseif(isset($_GET['mid'])){
    $merchant_id = $_GET['mid'];
}
if(isset($merchant_id)){
    include 'get-orders.php';
    include 'settings.php';
    $mid = $_SESSION['mid'];
    $settings = get_cfd_settings($mid);
    if(is_array($settings) && array_key_exists('dynamic_wait',$settings) && $settings['dynamic_wait'] == true){
        $merchant_id = $_SESSION["mid"];
        $order_times = get_done_time($merchant_id);
        // var_dump($order_times);
        if(count($order_times)>0){
            $data = array();
            $total = new DateTime('@0');
            $count = 0;
            // $data["ids"] = array();
            // $data["start"] = array();
            // $data["end"] = array();
            foreach($order_times as $order){
                $start = $order["date_created"];
                $end = $order["date_ready"];
                $start_date = new DateTime($start);
                $end_date = new DateTime($end);
                $interval = $start_date->diff($end_date);
                // array_push($data["ids"],$order["id"]);
                // array_push($data["start"],$start_date);
                // array_push($data["end"],$end_date);
                $total->add($interval);
                $count++;
                // array_push($data,$oData);
            }
            $data["estimate"] = round(($total->getTimestamp() / $count) / 60);
            if(array_key_exists('default_range',$settings) && is_numeric($settings['default_range']) && (int)$settings['default_range'] != "0"){
                $bot_range = round(($total->getTimestamp() / $count) / 60);
                $top_range = $bor_range + (int)$settings['default_range'];
                $data["estimate"] = $bot_range.'-'.$top_range;
            }
        }
    }
    if(is_array($settings) && array_key_exists('default_wait',$settings)){
        $data["default"] = $settings["default_wait"];
    } else {
        $data["default"] = 35;
    }
    //TAKE FROM WORDPRESS SETTINGS
    // $MooOptions = (array)get_option('moo_settings');
    // if(isset($MooOptions["order_later_minutes"])){
    //     $data["default"] = $MooOptions["order_later_minutes"];
    // } else {
    //     $data["default"] = 35;
    // }
    echo json_encode($data);
}