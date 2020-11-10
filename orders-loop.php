<?php
if (!isset($_SESSION)) {
    session_start();
}
    include 'get-orders.php';
    include 'twilio.php';
    $mid = $_SESSION['mid'];
    // $access_token = $_GET['token'];
    //  GET ORDERS WITH ORDER_STATE = READY
    $orders = array();
    $ready_orders=get_ready_orders($mid);
    if(is_array($ready_orders)){
        $orders = array_merge($orders,$ready_orders);
    } 
    //  GET ORDERS WITH ORDER_STATE = PREPARING
    $preparing_orders=get_preparing_orders($mid);
    if(is_array($preparing_orders) && count($preparing_orders) > 0){
        $orders = array_merge($orders,$preparing_orders);
    }
    $response = array();
    $response["orders"] = $orders;
    $twilio = check_twilio_integration($mid);
    $response['twilio'] = $twilio;
    echo json_encode($response);
