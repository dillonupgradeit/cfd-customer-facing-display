<?php
    function is_user_exist($email)
    {
        include("config.php");
        if($stmt = $con->prepare("SELECT merchant_id FROM users WHERE email = ? AND disabled = 0"))
        {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $rows = $stmt->num_rows;
            $stmt->close();
            return $rows > 0;     
        }
        
    } 
    function get_clover_token($cid,$code){
        include("config.php");
        if($cid == $client_id){
            $curl = curl_init($api_url.'/oauth/token?client_id='.$client_id.'&client_secret='.$secret.'&code=' . $code);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $auth   = curl_exec($curl);
            $info   = curl_getinfo($curl);
            $secret = json_decode($auth);
            if(isset($secret->access_token)){
                return $secret->access_token;
            } else {
                error_log('Could not get clover token');
            }
        } else {
            error_log('Client id != to cid');
        }
        return false;
    }
    function add_update_token($mid,$eid,$token){
        include("config.php");
        if($stmt = $con->prepare("INSERT INTO token (merchant_id,employee_id,access_token) VALUES (?,?,?) ON DUPLICATE KEY UPDATE employee_id = VALUES(employee_id), access_token = VALUES(access_token)"))
        {
            $stmt->bind_param("sss", $mid,$eid,$token);
            if ($stmt->execute()) {
                $token = 'UPDATED';
                return $token;
            } else {
                return 'TOKEN_NOT_UPDATED';
            }
        } else {
            return 'TOKEN_ALREADY_EXISTS '.$stmt->error;
        }
    }
    function get_merchant_details($mid,$token){
        include("config.php");
        try {
            $curl = curl_init($api_url.'/v3/merchants/' . $mid.'?expand=owner');
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                
                "Authorization: Bearer ".$token,
                "Content-Type: application/json" 
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $auth = curl_exec($curl);
        } catch (Exception $e) {
            $_SESSION['error'] = true;
            $_SESSION['e_message'] = "Cannot Get Merchant Details ". $e->getMessage();
            return "Cannot Get Merchant Details ";
            
        } 
        if (curl_errno($curl)) {
            return curl_error($curl);
        } 
        $info = curl_getinfo($curl);
        $detail=json_decode($auth,true);
        return $detail;
    }
    function add_new_user($mid,$name){
        include("config.php");
        $stmt = $con->prepare("INSERT INTO users (merchant_id,name) VALUES (?,?)");
        $stmt->bind_param("ss", $mid,$name);
        if ($stmt->execute()) {
            $user = $stmt->insert_id;
            return $user;
        } else {
            return 'USER_NOT_CREATED '.$stmt->error;
        }
    }
    function update_user($uid,$name,$email,$pass){
        include("config.php");
        if(!is_user_exist($email))
        {
            $user = array();
            $new_pass = md5($pass);
            if($stmt = $con->prepare("UPDATE users SET name = ?, email = ?, pass = ? WHERE id = ?")){
                $stmt->bind_param("ssss", $name,$email,$new_pass,$uid);
                $stmt->execute();
                if ($stmt->errno) {
                    $user = 'USER_NOT_UPDATED';
                } else {
                    $user['success'] = true;
                }
                $stmt->close();
            } else {
                $user = 'USER_NOT_UPDATED';
            }
        } else {
            $user = 'USER_ALREADY_EXISTS';
        }
        return $user;
    }
    function checkUserForForgotPasswordByEmail($email)
    {
        include("config.php");
        if($stmt = $con->prepare("SELECT id, name FROM users WHERE email = ? AND disabled = 0")){
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($uid, $namer);
            $stmt->fetch();
            $user = array();
            $user["user_id"] = $uid;
            $user["email"] = $email;
            $user["name"] = $namer;
            return $user;
        } else {
            return 'USER_NOT_LOGGED';
        }

    }
    function checkForgotPasswordByEmailLink($uid,$link,$dater)
    {
        include("config.php");
        $stmt = $con->prepare("SELECT id, email_token FROM recovery_emails WHERE user_id = ? AND email_link = ? AND disabled = 0 LIMIT 1");
        $stmt->bind_param("ss", $uid,$link);
        $stmt->execute();
        $stmt->bind_result($id, $email_token);
        $stmt->fetch();
            $user = array();
            $user['id'] = $id;
            $user['uid'] = $uid;
            $user['token'] = $email_token;
            return $user;

    }
    function verifyPasswordToken($token,$dater)
    {
        $secret_key = '3sdff447sdfHhYsdsSSDFWF';
        $jwt_values = explode('.', $token);
        $recieved_signature = $jwt_values[2];
        $payload = base64_decode($jwt_values[1]);
        $payload_data = json_decode($payload,true);
        $recievedHeaderAndPayload = $jwt_values[0] . '.' . $jwt_values[1];
        $resultedsignature = base64_encode(hash_hmac('sha256', $recievedHeaderAndPayload, $secret_key, true));
        if($resultedsignature == $recieved_signature) {
           if (is_array($payload_data)){
                if (array_key_exists('iat', $payload_data)) {
                    $ago_date = date('Y-m-d H:i:s', strtotime($dater. ' - 3 days'));
                    if ($ago_date < $payload_data['iat']) {
                        return 'TOKEN_VERIFIED';
                    } else {
                         return 'TOKEN_EXPIRED';
                    }
                } else {
                     return 'TOKEN_NOT_VERIFIED';
                }
            } else {
                return 'TOKEN_NOT_VERIFIED';
            }
        } else {
            return 'TOKEN_NOT_VERIFIED';
        }
    }
    function createPasswordToken($id)
    {

        // base64 encodes the header json
        $encoded_header = base64_encode('{"alg": "HS256","typ": "JWT"}');
        $payload = array();

         $payload['id'] = $id;
         $payload['iat'] = date('Y-m-d H:i:s');
         $payload_data = json_encode($payload);
        // base64 encodes the payload json
        $encoded_payload = base64_encode( $payload_data);

        // base64 strings are concatenated to one that looks like this
        $header_payload = $encoded_header . '.' . $encoded_payload;

        //Setting the secret key
        $secret_key = '3sdff447sdfHhYsdsSSDFWF';

        // Creating the signature, a hash with the s256 algorithm and the secret key. The signature is also base64 encoded.
        $signature = base64_encode(hash_hmac('sha256', $header_payload, $secret_key, true));

        $tokens = array();
        // Creating the JWT token by concatenating the signature with the header and payload, that looks like this:
        $tokens["token"] = $header_payload . '.' . $signature;
        $length = 32;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $tokens["link"] = $randomString;

         return $tokens;

    }
   function sendPasswordEmail($userid,$email_link,$email_token,$email,$uname)
{
    include("config.php");
    $succ = array();
    if ($stmt = $con->prepare("INSERT INTO recovery_emails (user_id,email_token,email_link) VALUES (?,?,?)"))
    {
        $stmt->bind_param("sss",$userid,$email_token,$email_link);
        $stmt->execute();
        $stmt->close();
        $passwordLink = "<a href=\"https://veganmob.biz/cfd/forgot-password.php?re=" . $email_link . "&u=" . urlencode(base64_encode($userid)) . "\">https://veganmob.biz/cfd/forgot-password.php?re=" . $email_link . "&u=" . urlencode(base64_encode($userid)) . "</a>";
        $message = "Dear $uname,\r\n<br />";
        $message .= "Please visit the following link to reset your password:<br /><br />\r\n";
        $message .= "-----------------------\r\n<br /><br /><br />";
        $message .= "$passwordLink\r\n<br><br /><br />";
        $message .= "-----------------------\r\n<br /><br />";
        $message .= "Please be sure to copy the entire link into your browser. The link will expire after 3 days for security reasons.\r\n\r\n";
        $message .= "If you did not request this forgotten password email, no action is needed, your password will not be reset as long as the link above is not visited. However, you may want to log into your account and change your security password and answer, as someone may have guessed it.\r\n\r\n<br />";


        $subject = "Password Reset";
            $comment = "\r\n\r\n<br />";
        $comment .= "Thank you,\r\n<br />";
        $comment .= "The Customer Facing Display Team!<br />";
        $result = emailTest($email,$uname,$message,$comment,$subject);
        $succ = FALSE;
        if($result == "Message sent!"){
            $succ = array();
            $succ['success'] = 'YES';
            $succ['message'] = 'SUCCESS';
        }
        return $succ;
    }
    $succ['success'] = 'NO';
    $succ['message'] = 'None';
    return $succ;
}
function updateRecoveryEmails($id,$dater){
    include("config.php");
    $stmt = $con->prepare("UPDATE recovery_emails SET used_date = ?, disabled = 1 WHERE id = ?");
    $stmt->bind_param("ss", $dater, $id);
    $stmt->execute();
    if ($stmt->errno) {
            return 'RECOVERY_NOT_UPDATED';
     } else {

        return 'RECOVERY_UPDATED';
     }
}
function emailTest($email,$name,$issue,$comment,$subject)
{

    include '../config.php';
    require 'vendor/phpmailer/phpmailer/src/Exception.php';
    require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require 'vendor/phpmailer/phpmailer/src/SMTP.php';
    $name = stripcslashes($name);

    $issue = stripcslashes($issue);
    $comment = stripcslashes($comment);
    $subject = stripcslashes($subject);

    // Send mail
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    // $mail->IsSMTP(); // telling the class to use SMTP
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = $email_server;                  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $email_username;             // SMTP username
    $mail->Password = $email_password;                           // SMTP password
    $mail->SMTPSecure = 'ssl';                            // Enable SSL encryption, TLS also accepted with port 465
    $mail->Port = 465;                                    // TCP port to connect to

    $mail->SMTPDebug = false;
    $mail->IsHTML(true);

    $mail->From = $email_username;
    $mail->FromName = "Customer Facing Display";
    $mail->Subject = $subject;
    $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
    $mail->MsgHTML($issue . "<br /><br />" . $comment);
    if(is_array($email)){
        foreach($email as $e) {
            $emailAddr = stripcslashes($e);

            $mail->AddAddress($emailAddr, $name);
        }
    } else {
        $emailAddr = stripcslashes($email);
        // Add as many as you want
        $mail->AddAddress($emailAddr, $name);
    }

    if(!$mail->Send()) {
        $mail->SmtpClose();
        $responser = "Mailer Error: " . $mail->ErrorInfo;
            return $responser;
    } else {
        $mail->SmtpClose();
        $responser = "Message sent!";
        // if ($this->save_mail($mail)) {
            //   $responser .= " Message saved!";
        // }
            return $responser;
    }
}
    function updateUserPass($new_pass, $id)
    {
        include("config.php");
        $new_password = md5($new_pass);
        $stmt = $con->prepare("UPDATE users SET pass = ? WHERE id = ?");
        $stmt->bind_param("ss", $new_password, $id);
        $stmt->execute();
        if ($stmt->errno) {
                return 'PASS_NOT_UPDATED';
         } else {

            return 'PASS_UPDATED';
         }
    }