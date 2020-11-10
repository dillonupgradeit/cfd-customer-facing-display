<?php
if (!isset($_SESSION)) {
    session_start();
}
// OPEN | PAID | REFUNDED | CREDITED | PARTIALLY PAID | PARTIALLY REFUNDED | MANUALLY REFUNDED
include 'config.php';
include 'get-orders.php';
include 'customers.php';
if (isset($_SESSION['token']))
{
    $mid = $_SESSION['mid'];
    $access_token = $_SESSION['token'];
    $result = get_orders($mid,$access_token,$api_url);
    // var_dump($result);
    if(is_array($result) && array_key_exists('elements',$result) && !is_null($result['elements'])){
        $elems = $result['elements'];
        if(is_array($elems) && count($elems)>0){
            foreach($elems as $order){
                $oid = $order["id"];
                $payment_state = $order["paymentState"];
                $title = $order["title"];
                $fullName = '';
                $abbrName = '';
                $phone = '';
                $cid = '';
                if(strpos($title,'SOO-') !== false){
                    $origin = 'WEBSITE';
                } else {
                    $origin = 'INSTORE';
                }
                if(strpos($title,'SOO-') !== false && array_key_exists("customers",$order)){
                    $customers = $order["customers"]["elements"];
                    foreach($customers as $cust){
                        $cid = $cust['id'];
                        $customer = get_customer_info($mid,$access_token,$cust,$api_url);
                        // echo json_encode($names);
                        if(is_array($customer) && array_key_exists('fullName',$customer) && array_key_exists('abbrName',$customer)){
                            $fullName = $customer["fullName"];
                            $abbrName = $customer["abbrName"];
                        } 
                        //GET FIRST NUMBER IN CUSTOMER INFO
                        if(is_array($customer) && array_key_exists('phoneNumbers',$customer)){
                            $tmp_arr = $customer["phoneNumbers"];
                            if(is_array($tmp_arr) && array_key_exists('elements',$tmp_arr)){
                                $numbers_arr = $tmp_arr["elements"];
                                // var_dump($numbers_arr);
                                if(is_array($numbers_arr) && count($numbers_arr)>0){
                                    $phone_info = $numbers_arr[0];
                                    if(is_array($phone_info) && array_key_exists('phoneNumber',$phone_info)){
                                        $phone_raw = $phone_info['phoneNumber'];
                                        $phone_tmp = trim(preg_replace('/[^\dxX]/', '', $phone_raw));
                                        if(is_numeric($phone_tmp)){
                                            $digits = preg_match_all( "/[0-9]/", $phone_tmp );
                                            if($digits == 10){
                                                if(strlen($phone_tmp) == $digits){
                                                    $phone = '+1'.$phone_tmp;
                                                }else {
                                                    // echo 'phone weird '.$phone_tmp;
                                                    echo '';
                                                }
                                            }elseif($digits == 11){
                                                if($phone_tmp[0] == "1"){
                                                    $phone = "+".$phone_tmp;
                                                } else {
                                                    // echo 'phone weird '.$phone_tmp;
                                                    echo '';
                                                }
                                            } else {
                                                // echo 'phone weird '.$phone_tmp;
                                                echo '';
                                            }
                                        }
                                    }
                                }
                            }
                        
                        } 
                    }
                } else {
                    // echo 'no soo';
                    echo '';
                }
                // var_dump($order);
                $date_string = $order["createdTime"];
                $dater = gmdate("Y-m-d H:i:s", $date_string/1000);
                $result = add_update_order($oid,$mid,$cid,$title,$fullName,$abbrName,$phone,$origin,$payment_state,$dater);
                if(is_array($result) && array_key_exists('id',$result) && is_numeric($result['id'])){
                    echo $result['id'].' success<br>';
                } else {
                    // var_dump($result);
                    echo '';
                }
            }
        } else {
            // echo json_encode('no orders');
            echo '';
        }
    } else {
        // echo json_encode('no order elements');
        echo '';
    }

} else {
    // echo json_encode('no accesstoken');
    echo '';
}

// if (!isset($_SESSION)) {
//     session_start();
// }
// // OPEN | PAID | REFUNDED | CREDITED | PARTIALLY PAID | PARTIALLY REFUNDED | MANUALLY REFUNDED
// include 'config.php';
// include 'get-orders.php';
// include 'customers.php';
// if (isset($_SESSION['token']))
// {
//     $merchant_id = $_SESSION['mid'];
//     $access_token = $_SESSION['token'];
//     $result = get_orders($merchant_id,$access_token,$api_url);
//     // var_dump($result);
//     foreach($result['elements'] as $order){
//         $oid = $order["id"];
//         $payment_state = $order["paymentState"];
//         $title = $order["title"];
//         $fullName = '';
//         $abbrName = '';
//         if(strpos($title,'SOO-') !== false && array_key_exists("customers",$order)){
//             $customers = $order["customers"]["elements"];
//             foreach($customers as $cust){
//                 $names = get_customer_name($merchant_id,$access_token,$cust,$api_url);
//                 echo json_encode($names);
//                 if(array_key_exists('fullName',$names) && array_key_exists('abbrName',$names)){
//                     $fullName = $names["fullName"];
//                     $abbrName = $names["abbrName"];
//                 } 
//             }
//         } else {
//             echo 'no soo';
//         }
//         $date_string = $order["createdTime"];
//         $dater = gmdate("Y-m-d H:i:s", $date_string/1000);
//         if(!$con->query("insert into orders(order_id,merchant_id,title,fullName,abbrName,payment_state,order_state,date_created) values('$oid','$merchant_id','$title','$fullName','$abbrName','$payment_state','PREPARING','$dater') on duplicate key update payment_state='$payment_state'")){
//             echo json_encode("Error description: " . mysqli_error($con)); 
//             break;
//         } else {
//             echo json_encode($result);
//         }
//     }

// } else {
// 	echo json_encode('no accesstoken');
// }
