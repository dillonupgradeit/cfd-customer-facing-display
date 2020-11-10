<?php
function create_customer($merchant_id,$access_token,$email,$phone,$firstName,$lastName,$api_url){
    $curl = curl_init($api_url.'/v3/merchants/' . $merchant_id . '/customers');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Authorization:Bearer " . $access_token,
        'Content-Type: application/json' ));
    $data = '{
        "emailAddresses":[{
            "emailAddress":"'.$email.'"
        }],
        "phoneNumbers":[{
            "phoneNumber":"'.$phone.'"
        }],
        "firstName":"'.$firstName.'",
        "lastName":"'.$lastName.'"
    }';

    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $auth = curl_exec($curl);
    $info = curl_getinfo($curl);
    echo "<pre>";
    $order_detail = json_decode($auth);
    return $order_detail;

}
function get_merchant_hours($mid){
    $curl = curl_init($api_url.'/v3/merchants/' . $mid . '/opening_hours');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer ".$access_token,
        'accept: application/json' 
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $auth = curl_exec($curl);
    if (curl_errno($curl)) {
        echo curl_error($curl);
    } 
    $info = curl_getinfo($curl);
    $detail=json_decode($auth,true);	
    return $detail;
}
function get_customer($merchant_id,$access_token,$customer_id,$api_url){
    $curl = curl_init($api_url.'/v3/merchants/' . $merchant_id . '/customers/'.$customer_id);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer ".$access_token,
        'accept: application/json' 
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $auth = curl_exec($curl);
    if (curl_errno($curl)) {
        echo curl_error($curl);
    } 
    $info = curl_getinfo($curl);
    $detail=json_decode($auth,true);	
    return $detail;
}
function get_customer_name($merchant_id,$access_token,$customer,$api_url){
    if(!array_key_exists("fistName",$customer) && !array_key_exists("lastName",$customer) && !array_key_exists("fullName",$customer) && array_key_exists("id",$customer)){
        $cust_id = $customer["id"];
        $customer = get_customer($merchant_id,$access_token,$cust_id,$api_url);
    }
    $names = array();
    if(array_key_exists("firstName",$customer) && array_key_exists("lastName",$customer)){
        $names["fullName"] = $customer["firstName"].' '.$customer["lastName"];
        $names["abbrName"] = $customer["firstName"].' '.$customer["lastName"][0].'.';
    } elseif(array_key_exists("fullName",$customer)){
        $names["fullName"] = $customer["fullName"];
        $split_names = $customer["fullName"].split();
        if(count($split_names)>1){
            $names["abbrName"] = $split_names[0].' '.$split_names[1][0].'.';
        } else {
            $names["abbrName"] = $split_names[0];
        }
    } 
    return $names;
}
function get_customer_info($merchant_id,$access_token,$customer,$api_url){
    if(!array_key_exists("fistName",$customer) && !array_key_exists("lastName",$customer) && !array_key_exists("fullName",$customer) && array_key_exists("id",$customer)){
        $cust_id = $customer["id"];
        $customer = get_customer($merchant_id,$access_token,$cust_id,$api_url);
    }
    if(array_key_exists("firstName",$customer) && array_key_exists("lastName",$customer)){
        $customer["fullName"] = $customer["firstName"].' '.$customer["lastName"];
        $customer["abbrName"] = $customer["firstName"].' '.$customer["lastName"][0].'.';
    } elseif(array_key_exists("fullName",$customer)){
        $split_names = $customer["fullName"].split();
        if(count($split_names)>1){
            $customer["abbrName"] = $split_names[0].' '.$split_names[1][0].'.';
        } else {
            $customer["abbrName"] = $split_names[0];
        }
    } 
    return $customer;
}
function get_merchant_meta($mid){
    include("config.php");
    if($stmt = $con->prepare("SELECT id,meta_key,meta_index,meta_value FROM merchant_meta WHERE merchant_id = ?"))
    {
        $stmt->bind_param("s", $mid);
        $stmt->execute();
        $stmt->bind_result($mmid, $keyer,$index,$valuer);
        $collection = array();
        while($stmt->fetch()){
            $tmp = array();
            if($keyer != 'hours_open' && $keyer != 'hours_close'){
                $collection[$keyer] = $valuer;
            } else {
                if(isset($collection[$index])){
                    $tmp[$keyer] = $valuer;
                    $collection[$index] = array_merge($collection[$index],$tmp);
                } else {
                    $collection[$index] = array();
                    $tmp[$keyer] = $valuer;
                    $collection[$index] = array_merge($collection[$index],$tmp);
                }
            }
        }   
        
    } else {
       $collection = 'ERROR_GETTING_COLLECTION';
    }
    return $collection;  
}
// john = PV8G03XE0X1CG
// jane = W8SRZZSS313VJ
//  ash = TNC0MSEPF36H2
// $merchant_id = $_SESSION['mid'];
// $access_token = $_SESSION['token'];
// $email = 'ashketchup@hotmail.com';
// $phone = '4157777777';
// $firstName = 'Ash';
// $lastName = 'Ketchup';
// $add_customer = create_customer($merchant_id,$access_token,$email,$phone,$firstName,$lastName);
// var_dump(print_r($add_customer,TRUE)); 