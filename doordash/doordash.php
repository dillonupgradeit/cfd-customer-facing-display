<?php
if (!isset($_SESSION)) {
    session_start();
}
if(isset($_SESSION['mid'])){
    include '../config.php';
    require '../vendor/autoload.php';
    include '../get-orders.php';
    $parser = new \Smalot\PdfParser\Parser();
    $mid = $_SESSION['mid'];
    if (! function_exists('imap_open')) {
        echo "IMAP is not configured.";
        exit();
    } else {  
        /* Connecting Gmail server with IMAP */
        $folder = "attachment";
        $connection = imap_open('{'.$email_server.'}INBOX', $email_username, $email_password) or die('Cannot connect to Gmail: ' . imap_last_error());
        /* Search Emails having the specified keyword in the email subject */
        $emailData = imap_search($connection, 'SUBJECT "Delivery order from "');
        if (! empty($emailData)) {
            /* put the newest emails on top */
            rsort($emailData);
            foreach ($emailData as $emailIdent) {
                $overview = imap_fetch_overview($connection, $emailIdent, 0);
                $rec_date = $overview['date'];
                $utc_tz = new DateTimeZone('UTC'); 
                $starter = new DateTime($rec_date, $utc_tz);
                $rec_utc = $starter->format('Y-m-d H:i:s');
                $oid_tmp = trim(substr($overview[0]->subject, strpos($overview[0]->subject, "Order # ") + 8));
                $dest_tmp = $folder.'/'.$oid_tmp.'.pdf';
                if(!file_exists($dest_tmp)){
                    $message = imap_fetchbody($connection, $emailIdent, '1.1');
                    $messageExcerpt = substr($message, 0, 150);
                    $partialMessage = trim(quoted_printable_decode($messageExcerpt)); 
                    $date = date("d F, Y H:m:s", strtotime($overview[0]->date));
                    $structure = imap_fetchstructure($connection,  $emailIdent);
                    $attachments = array();
                    if(isset($structure->parts) && count($structure->parts)) 
                    {
                        for($i = 0; $i < count($structure->parts); $i++) 
                        {
                            $attachments[$i] = array(
                                'is_attachment' => false,
                                'filename' => '',
                                'name' => '',
                                'attachment' => ''
                            );
                            if($structure->parts[$i]->ifdparameters) 
                            {
                                foreach($structure->parts[$i]->dparameters as $object) 
                                {
                                    
                                    if(strtolower($object->attribute) == 'filename') 
                                    {
                                        $attachments[$i]['is_attachment'] = true;
                                        $attachments[$i]['filename'] = $object->value;
                                    }
                                }
                            }
                            if($structure->parts[$i]->ifparameters) 
                            {
                                foreach($structure->parts[$i]->parameters as $object) 
                                {
                                    if(strtolower($object->attribute) == 'name') 
                                    {
                                        $attachments[$i]['is_attachment'] = true;
                                        $attachments[$i]['name'] = $object->value;
                                    }
                                }
                            }
                            if($attachments[$i]['is_attachment']) 
                            {
                                $attachments[$i]['attachment'] = imap_fetchbody($connection, $emailIdent, $i+1);
                                //echo $structure->parts[$i]->encoding;
                                /* 3 = BASE64 encoding */
                                if($structure->parts[$i]->encoding == 3) 
                                { 
                                    $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                                }
                                /* 4 = QUOTED-PRINTABLE encoding */
                                elseif($structure->parts[$i]->encoding == 4) 
                                { 
                                    $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                                }
                            }
                        } // end for structure
                    }
                    /* iterate through each attachment and save it */
                    foreach($attachments as $attachment)
                    {
                        if($attachment['is_attachment'] == 1)
                        {
                            $filename = $attachment['name'];
                            if(empty($filename)) $filename = $attachment['filename'];

                            if(empty($filename)) $filename = time() . ".dat";
                            
                            if(!is_dir($folder))
                            {
                                mkdir($folder);
                            }
                            $new_filename = substr($filename, strpos($filename, "-") + 1);
                            $dest = $folder ."/". $new_filename;
                            if(!file_exists($dest)){
                                $fp = fopen("./".$dest, "w+");
                                fwrite($fp, $attachment['attachment']);
                                
                                fclose($fp);
                                $pdf = $parser->parseFile($dest);
                                
                                $text = $pdf->getText();
                                //ORIGIN
                                $origin = 'DOORDASH';
                                //ORDER ID
                                $oid = strstr($new_filename,'.pdf',true);
                                // echo ' OrderID: '.$oid.' ';
                                //ORDER CREATED DATE 
                                $created_tmp = substr($text, strpos($text, "Placed on ") + 10);
                                $created_tmp = trim(strstr($created_tmp,' For',true));
                                $created_tmp = str_replace("at ","",$created_tmp);
                                $this_tz = new DateTimeZone('America/Los_Angeles'); 
                                $starter = new DateTime($created_tmp, $this_tz);
                                $created_la = $starter->format('Y-m-d H:i:s');
                                $starter->setTimezone(new DateTimeZone('UTC'));
                                $created_utc = $starter->format('Y-m-d H:i:s');
                                // echo ' Created: '.$created_utc;
                                //ABBR. NAME
                                $name_tmp = substr($overview[0]->subject, strpos($overview[0]->subject, "Delivery order from ") + 20);
                                $name_tmp = trim(strstr($name_tmp,' for',true));
                                $name = $name_tmp;
                                // echo ' Name: '.$name;
                                // Order State 
                                $order_state = 'PREPARING';
                                //PAYMENT STATUS
                                $payment_tmp = substr($text, strpos($text, "Payment: ") + 9);
                                $payment_tmp = trim(strstr($payment_tmp,' ',true));
                                if($payment_tmp == 'Pre-Paid'){
                                    $payment_state = 'PAID';
                                } else {
                                    $payment_state = 'OPEN';
                                }
                                if(isset($mid) && isset($origin) && isset($oid) && isset($name) && isset($payment_state) && isset($order_state) && isset($created_utc)){
                                    $result = add_update_order($mid,$oid,$oid,$name,$name,$origin,$payment_state,$order_state,$created_utc);
                                    if(is_array($result) && array_key_exists('id',$result) && is_numeric($result['id'])){
                                        echo $result['id'].' success<br>';
                                    } else {
                                        var_dump($result);
                                    }
                                } else {
                                    echo 'params missing<br>';
                                }
                            } else {
                                echo 'already exists<br>';
                            }
                        }
                    } // end foreach attachment
                } else {
                    echo 'already exists1<br>';
                }
                
            } // End foreach
        } // end if
        imap_close($connection);
    }
}
?>
