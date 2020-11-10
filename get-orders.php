<?php
function get_orders($merchant_id,$access_token,$api_url)
    { 
        // $this_morning = strtotime('today midnight')*1000;
        $date = get_this_morning($merchant_id);
        $curl=curl_init($api_url.'/v3/merchants/'.$merchant_id.'/orders?expand=payments&expand=refunds&expand=credits&filter=createdTime>='.$date);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        
        "Authorization: Bearer ".$access_token,
        "Content-Type: application/json" 
        )
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $auth = curl_exec($curl);
        if (curl_errno($curl)) {
            echo curl_error($curl);
        } 
        $info = curl_getinfo($curl);
        $order_detail=json_decode($auth,true);	
        return $order_detail;
    } 
    function get_this_morning($mid){
        include("config.php");
        $result = get_merchant_meta($mid);
        if(is_array($result) && array_key_exists('timezone',$result) && !is_null($result['timezone'])){
            $tmp_date = new DateTime('today', new DateTimeZone($result['timezone']));
            // $tmp_date->setTimezone(new DateTimeZone('UTC'));
            return  $tmp_date->getTimestamp()*1000;
        } else {
            return strtotime('today midnight')*1000;
        }
    }
function get_ready_orders($mid)
    {
        include("config.php");
        if($stmt = $con->prepare("SELECT orders.order_id, orders.title,orders.fullName,orders.abbrName,orders.phone, orders.payment_state,orders.order_state,sms_messages.order_id FROM orders LEFT JOIN sms_messages ON sms_messages.order_id = orders.order_id WHERE orders.merchant_id = ? AND orders.order_state = 'READY' AND orders.payment_state = 'PAID' ORDER BY orders.date_created ASC"))
        {
            $stmt->bind_param("s", $mid);
            $stmt->execute();
            $stmt->bind_result($oid, $title,$fullName,$abbrName,$phone, $p_state, $o_state,$mess_id);
            $orders = array();
            while($stmt->fetch()){
                $tmp = array();
                $tmp['id'] = $oid;
                $tmp['title'] = $title;
                $tmp['full_name'] = $fullName;
                if($phone == ''){
                    $tmp['has_phone'] = false;
                } else {
                    $tmp['has_phone'] = true;
                }
                $tmp['abbr_name'] = $abbrName;
                $tmp['payment_state'] = $p_state;
                $tmp['order_state'] = $o_state;
                $tmp['message_id'] = $mess_id;
                array_push($orders,$tmp);
            }
            $stmt->close();
        } else {
            $orders = 'zero results';
        }
        return $orders;
    } 
function get_order($oid)
    {
        include("config.php");
        if($stmt = $con->prepare("SELECT order_id, title,abbrName,phone,payment_state FROM orders WHERE order_id = ?"))
        {
            $stmt->bind_param("s", $oid);
            $stmt->execute();
            $stmt->bind_result($oid, $title,$abbrName,$phone,$payment);
            $stmt->fetch();
            $tmp = array();
            $tmp['id'] = $oid;
            $tmp['title'] = $title;
            $tmp['abbr_name'] = $abbrName;
            $tmp['phone'] = $phone;
            $tmp['payment_state'] = $payment;

            $stmt->close();
        } else {
            $tmp = 'zero results';
        }
        return $tmp;
    } 

function get_preparing_orders($mid)
    {
        include("config.php");
        if($stmt = $con->prepare("SELECT order_id, title,fullName,abbrName, payment_state, order_state FROM orders WHERE merchant_id = ? AND (order_state = 'PREPARING' OR (order_state = 'READY' AND payment_state = 'OPEN')) ORDER BY date_created ASC"))
        {
            $stmt->bind_param("s", $mid);
            $stmt->execute();
            $stmt->bind_result($oid, $title,$fullName,$abbrName, $p_state, $o_state);
            $orders = array();
            while($stmt->fetch()){
                $tmp = array();
                $tmp['id'] = $oid;
                $tmp['title'] = $title;
                $tmp['full_name'] = $fullName;
                $tmp['abbr_name'] = $abbrName;
                $tmp['payment_state'] = $p_state;
                $tmp['order_state'] = $o_state;
                array_push($orders,$tmp);
            }
            $stmt->close();
        } else {
            $orders = 'zero results';
        }
        return $orders;
    } 
function get_done_time($mid)
    {
        include("config.php");
        // AND date_ready >= DATE_SUB(NOW(),INTERVAL 1 HOUR)
        if($stmt = $con->prepare("SELECT order_id,date_created,date_ready FROM orders WHERE merchant_id = ? AND (order_state = 'DONE' OR order_state = 'READY') AND payment_state = 'PAID' AND CAST(date_ready AS DATE) = CAST(NOW() AS DATE) ORDER BY date_created DESC LIMIT 5"))
        {
            $stmt->bind_param("s", $mid);
            $stmt->execute();
            $stmt->bind_result($oid, $created,$ready);
            $orders = array();
            while($stmt->fetch()){
                $tmp = array();
                $tmp['id'] = $oid;
                $tmp['date_created'] = $created;
                $tmp['date_ready'] = $ready;
                array_push($orders,$tmp);
            }
            $stmt->close();
        } else {
            $orders = 'zero results'.$con->error;
        }
        return $orders;
    } 
function update_order_ready($oid,$state,$dater)
    {
        include("config.php");
        $order = '';
        //if(is_order_exist($oid)){
            $stmt = $con->prepare("UPDATE orders SET order_state = ?, date_ready = ? WHERE order_id = ?");
            $stmt->bind_param("sss", $state,$dater,$oid);
            $stmt->execute();
            if ($stmt->errno) {
                $order = 'ORDER_NOT_UPDATED';
            } else {

                $order = 'ORDER_UPDATED';
            }
            $stmt->close();
        return $order;
    } 
function update_order_pickup($oid,$state,$dater)
    {
        include("config.php");
        $order = '';
        //if(is_order_exist($oid)){
            $stmt = $con->prepare("UPDATE orders SET order_state = ?, date_pickup = ? WHERE order_id = ?");
            $stmt->bind_param("sss", $state,$dater,$oid);
            $stmt->execute();
            if ($stmt->errno) {
                $order = 'ORDER_NOT_UPDATED';
            } else {

                $order = 'ORDER_UPDATED';
            }
            $stmt->close();
        return $order;
    } 
    function add_update_order($oid,$mid,$cid,$title,$full_name,$abbr_name,$phone,$origin,$payment_state,$created)
{
    include("config.php");
    if(!$stmt = $con->prepare("INSERT INTO orders (order_id,merchant_id,customer_id,title,fullName,abbrName,phone,origin,payment_state,date_created) VALUES(?,?,?,?,?,?,?,?,?,?) on duplicate key update payment_state=VALUES(payment_state), phone=VALUES(phone)")){
        return "Error description: " . mysqli_error($con); 
    } else {
        $stmt->bind_param("ssssssssss", $oid,$mid,$cid,$title,$full_name,$abbr_name,$phone,$origin,$payment_state,$created);
        if ($stmt->execute()) {
            $data = array();
            $data['id'] = $stmt->insert_id;
            $data['message'] = "save successful";
            return $data;
        } else {
            return 'ORDER_NOT_CREATED';
        }
    }
}
// function add_update_order($mid,$oid,$title,$full_name,$abbr_name,$origin,$payment_state,$order_state,$created)
//     {
//         include("config.php");
//         if(!$stmt = $con->prepare("INSERT INTO orders (order_id,merchant_id,title,fullName,abbrName,origin,payment_state,order_state,date_created) VALUES(?,?,?,?,?,?,?,?,?) on duplicate key update payment_state=VALUES(payment_state)")){
//             return "Error description: " . mysqli_error($con); 
//         } else {
//             $stmt->bind_param("sssssssss", $oid,$mid,$title,$full_name,$abbr_name,$origin,$payment_state,$order_state,$created);
//             if ($stmt->execute()) {
//                 $data = array();
//                 $data['id'] = $stmt->insert_id;
//                 $data['message'] = "save successful";
//                 return $data;
//             } else {
//                 return 'ORDER_NOT_CREATED';
//             }
//         }
//     }
function is_order_exist($oid)
    {
        include("config.php");
        $stmt = $con->prepare("SELECT order_id FROM orders WHERE order_id = ?");
        $stmt->bind_param("s", $oid);
        $stmt->execute();
        $stmt->store_result();
        $rows = $stmt->num_rows;
        $stmt->close();
        return $rows > 0;     
    }
