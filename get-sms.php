<?php
function is_sms_exist($oid){
    include("config.php");
    $stmt = $con->prepare("SELECT order_id FROM sms_messages WHERE order_id = ?");
    $stmt->bind_param("s", $oid);
    $stmt->execute();
    $stmt->store_result();
    $rows = $stmt->num_rows;
    $stmt->close();
    return $rows > 0;     

}
function add_sms($oid,$tid){
    include("config.php");
    if(!$stmt = $con->query("insert into sms_messages(order_id,twilio_id) values('$oid','$tid')")){
        return "Error description: " . mysqli_error($con); 
    } else {
        $data = array();
        $data['id'] = $stmt->insert_id;
        $data['message'] = "save successful";
        return $data;
    }
    
}