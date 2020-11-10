<?php
if (!isset($_SESSION)) {
    session_start();
}
if(!isset($_SESSION['token']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in']){
    header('Location: login.php');
}
require __DIR__ . '/vendor/autoload.php';
use Twilio\Rest\Client;
function check_and_text($oid){
    include 'config.php';
    include 'twilio-config.php';
    if($twilio){
        include 'get-sms.php';
        if(!is_sms_exist($oid)){
            include 'get-orders.php';
            $order = get_order($oid);
            $phone = $order["phone"];
            $title = $order["title"];
            if(strpos($title,'SOO-') !== false && $phone != ''){
                $name = $order["abbr_name"];
                $payment_state = $order["payment_state"];
                if($payment_state == "PAID"){
                    $message = $name.", your Vegan Mob order is ready at the pickup window. Please give us your first and last name when you arrive.";
                } elseif($payment_state == "OPEN") {
                    $message = $name.", your Vegan Mob order is ready to be picked up. Don't forget to head to the order counter and pay for the order.";
                }
                try{
                    $client = new Client( $twilio['twilio_sid'],  $twilio['twilio_secret']);
                    $message = $client->messages->create(
                        $phone,
                        array(
                            'from' => $twilio['twilio_number'],
                            'body' => $message
                        )
                    );
                } catch (Exception $e) {
                    $_SESSION['error'] = true;
                    $_SESSION['e_message'] = "Text Unsuccessful ". $e->getMessage();
                    
                } 
                if(isset($message->sid) && isset($message->dateSent)){
                    $tid = $message->sid;
                    $result = add_sms($oid,$tid);
                    if(is_array($result)){
                        //$_SESSION['error'] = false;
                        //$_SESSION['e_message'] = "Text Sent!";
                    } else {
                        $_SESSION['error'] = true;
                        $_SESSION['e_message'] = "Text Unsuccessful ". $result;
                    }
                }else {
                    $_SESSION['error'] = true;
                    //$_SESSION['e_message'] = "Text Unsuccessful #1";
                }
            }else {
                $_SESSION['error'] = true;
                $_SESSION['e_message'] = "Text Unsuccessful #2";
            }
        } else {
            $_SESSION['error'] = true;
            $_SESSION['e_message'] = "Text Unsuccessful - Already Sent a Text";
                    
        }
    } else {
        $_SESSION['error'] = true;
        $_SESSION['e_message'] = "Twilio Not Setup";
    }
    header("Location: controller.php");
}
if(isset($_GET['oid'])){
    $oid = $_GET['oid'];
    check_and_text($oid);
}