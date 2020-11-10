<?php
function add_update_setting($mid,$key,$value){
    include("config.php");
    $response = array();
    if(!is_setting_exist($mid,$key)){
        $response = add_setting($mid,$key,$value);
    } else {
        $response = update_setting($mid,$key,$value);
    }
    return $response;
}
function add_setting($mid,$key,$value){
    include("config.php");
    if(!is_setting_exist($mid,$key)){
        if(!$stmt = $con->prepare("INSERT INTO settings (merchant_id,meta_key,meta_value) VALUES(?,?,?)")){
            return "Error description: " . mysqli_error($con); 
        } else {
            $stmt->bind_param("sss", $mid,$key,$value);
            $stmt->execute();
            $data = array();
            $data['id'] = $stmt->insert_id;
            $data['message'] = "save successful";
            return $data;
        }
    } else {
        return 'setting doesnt exist.';
    }
    
}
function update_setting($mid,$key,$value){
    include("config.php");
    $stmt = $con->prepare("UPDATE settings SET meta_value = ? WHERE merchant_id = ? AND meta_key = ?");
    if ($stmt->errno) {
        return "Error description: " . $stmt->errno; 
    } else {
        $stmt->bind_param("sss",$value, $mid,$key);
        $stmt->execute();
        $data = array();
        $data['id'] = $stmt->insert_id;
        $data['message'] = "update successful";
        return $data;
    }
}
function is_setting_exist($mid,$key){
    include("config.php");
    $stmt = $con->prepare("SELECT id FROM settings WHERE merchant_id = ? AND meta_key = ?");
    
    $stmt->bind_param("ss", $mid,$key);
    $stmt->execute();
    $stmt->store_result();
    $rows = $stmt->num_rows;
    $stmt->close();
    return $rows > 0;  
     
}
function get_cfd_settings($mid){
    include("config.php");
    if($stmt = $con->prepare("SELECT meta_key,meta_value FROM settings WHERE merchant_id = ? ORDER BY id")){
        $stmt->bind_param("s", $mid);
        $stmt->execute();
        $stmt->bind_result($keyer,$valuer);
        $settings = array();
        while($stmt->fetch()){
            $tmp = array();
            $tmp[$keyer] = $valuer;
            $settings = array_merge($settings,$tmp);
        }
        $stmt->close();
        return $settings;  
    } else {
        return 'error'.$stmt->error;
    }   
}