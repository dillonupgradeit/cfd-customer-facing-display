<?php
function add_update_twilio($mid,$sid,$num,$sec){
    include("config.php");
    if(!$stmt = $con->prepare("INSERT INTO twilio (merchant_id,twilio_sid,twilio_number,twilio_secret) VALUES(?,?,?,?) on duplicate key update twilio_sid=VALUES(twilio_sid),twilio_number=VALUES(twilio_number),twilio_secret=VALUES(twilio_secret),disabled=0")){
        return "Error description: " . mysqli_error($con); 
    } else {
        $stmt->bind_param("ssss", $mid,$sid,$num,$sec);
        $stmt->execute();
        $data = array();
        $data['id'] = $stmt->insert_id;
        $data['message'] = "save successful";
        return $data;
    }
}

function check_twilio_integration($mid){
    include("config.php");
    $stmt = $con->prepare("SELECT twilio_sid FROM twilio WHERE merchant_id = ? AND disabled = 0");
    $stmt->bind_param("s", $mid);
    $stmt->execute();
    $stmt->store_result();
    $rows = $stmt->num_rows;
    $stmt->close();
    return $rows > 0;  
    
}
function get_twilio($mid){
    include("config.php");
    if($stmt = $con->prepare("SELECT twilio_sid,twilio_number,twilio_secret FROM twilio WHERE merchant_id = ? AND disabled = 0")){
        $stmt->bind_param("s", $mid);
        $stmt->execute();
        $stmt->bind_result($sid,$num,$sec);
        $stmt->fetch();
        $tmp = array();
        $tmp['twilio_sid'] = $sid;
        $tmp['twilio_number'] = $num;
        $tmp['twilio_secret'] = $sec;

        $stmt->close();
        return $tmp;  
    } else {
        return 'error'.$stmt->error;
    }   
}
function disable_twilio($mid){
    include("config.php");
    if(!$stmt = $con->prepare("UPDATE twilio SET disabled = 1 WHERE merchant_id = ?")){
        return "Error description: " . mysqli_error($con); 
    } else {
        $stmt->bind_param("s", $mid);
        $stmt->execute();
        $data = array();
        $data['id'] = $stmt->insert_id;
        $data['message'] = "Twilio Disabled";
        return $data;
    }
}