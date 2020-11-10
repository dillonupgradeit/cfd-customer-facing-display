<?php
if (!isset($_SESSION)) {
    session_start();
}
if(isset($_SESSION['mid'])){
    include 'twilio.php';
    $mid = $_SESSION['mid'];
    $twilio = false;
    if(check_twilio_integration($mid)){
        $result = get_twilio($mid);
        if(is_array($result) && array_key_exists('twilio_sid',$result) && $result['twilio_sid'] != ''){
            $twilio = $result;
        } 
    }
   
}