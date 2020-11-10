<?php
if (!isset($_SESSION)) {
    session_start();
}
error_log('update-settings.php');
if(!isset($_SESSION['token']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in']){
    header('Location: login.php');
}
if(isset($_SESSION['mid'])){
    $mid = $_SESSION['mid'];
    include 'settings.php';
    include 'twilio.php';
    //check and Save settings
    $defaults = array();
    $defaults['color_text'] = '#000000';
    $defaults['color_hl_text'] = '#ffffff';
    $defaults['color_hl_bg'] = '#9ed637';
    $defaults['display_title'] = 'Current Orders';
    $defaults['default_wait'] = '30';
    $defaults['logo'] = 'default.png';
    $defaults['bg_image'] = 'default-bg.jpg';
    $chcker = ['logo','bg_image','color_text','color_hl_text','color_hl_bg','display_title','default_wait'];
    if(isset($_POST['ssubmit'])){
        $is_ok = true;
        foreach($_POST as $key => $value){
            if($value == ''){
                $value = $defaults[$key];
            }
            if(in_array($key,$chcker)){
                $result = add_update_setting($mid,$key,$value);
                if(!is_array($result)){
                    $_SESSION['e_message'] = 'Could not update '.$key;
                    $_SESSION['error'] = true;
                    $is_ok = false;
                    break;
                }
            }
        }
        if($is_ok){
            addImageUpdateSettings($mid);
            if(!$_SESSION['error']){
                $_SESSION['e_message'] = 'Settings Saved Successfully';
                $_SESSION['error'] = false;
            }
        }
    }
    //check and save twilio
    if(isset($_POST['tsubmit'])){
        if(isset($_POST['t_num']) && isset($_POST['t_sid']) && isset($_POST['t_sec'])){
            $num = $_POST['t_num'];
            $for_num = preg_replace('/[^0-9]/', '', $num);
            if(strlen($for_num)==10 && $for_num[0] !== "1"){
                $for_num = "1".$for_num;
            }
            if(strlen($for_num) == 11){
                $for_num = "+".$for_num;
                $sid = $_POST['t_sid'];
                $sec = $_POST['t_sec'];
                $result = add_update_twilio($mid,$sid,$for_num,$sec);
                if(is_array($result)){
                    $_SESSION['e_message'] = 'Twilio Added Successfully';
                    $_SESSION['error'] = false;
                } else {
                    $_SESSION['e_message'] = 'Error updating Twilio Integration';
                    $_SESSION['error'] = true;
                }
            } else {
                $_SESSION['e_message'] = 'There was an error with you Twilio Number';
                $_SESSION['error'] = true;
            }
           

        } else {
            $_SESSION['e_message'] = 'Please add Twilio SID, Phone Number, and Secret';
            $_SESSION['error'] = true;
        }
    }
    //disable twilio
    if(isset($_POST['tdsubmit'])){
        $result = disable_twilio($mid);
        if(is_array($result)){
            $_SESSION['e_message'] = 'Twilio Disabled';
            $_SESSION['error'] = false;
        } else {
            $_SESSION['e_message'] = 'Error occured while disabling twilio.'.$result;
            $_SESSION['error'] = true;
        }
    }
    $twilio_data = check_twilio_integration($mid);
    $settings = get_cfd_settings($mid);
    //var_dump($settings);
    foreach($chcker as $key){
        if(!array_key_exists($key,$settings) || $settings[$key] == ''){
            $settings[$key] = $defaults[$key];
        }
    }

    
}
function addImageUpdateSettings($mid){
    $defaults = array();

    foreach($_FILES as $setting=>$file){
        if($file == ''){
            $file = $defaults[$setting];
        } else {
            $chcker = ['logo','bg_image'];
            if(in_array($setting,$chcker)){
                // $result = addImageUpdateSettings($mid,$file,$key);
                if ($file['size'] != 0 && $file['error'] == 0) {
                    $allowedExts = array("gif", "jpeg", "jpg", "png");
                    $extension = end(explode(".", $file["name"]));
                    if ((($file["type"] == "image/gif") || ($file["type"] == "image/jpeg") || ($file["type"] == "image/jpg") || ($file["type"] == "image/png") || ($file["type"] == "image/gif"))) {
                        if(($file["size"] <= 25000000)){
                            if(in_array($extension, $allowedExts)){
                                $namer = $file["name"];
                                $ext = pathinfo($namer, PATHINFO_EXTENSION);
                                if($ext == "jpeg" || $ext == "jpg" || $ext == "png" || $ext == "gif"){
                                    // Save the image file
                                    $rand = rand(10000,100000000);
                                    $ider = $_SESSION['mid'];
                                    $imagename = $ider . '-' . $rand . '.' . $ext;
                                    $ref_dest = 'uploads/images/';
                                    // $url_dest = '/cfd/uploads/images/';
                                    $dest_ref = $ref_dest.$imagename;
                                    // $dest_url = $url_dest.$imagename;
                                    if(move_uploaded_file($file["tmp_name"],$dest_ref))
                                    {
                                        $result = add_update_setting($mid,$setting,$imagename);
                                        if(!is_array($result)){
                                            $_SESSION['e_message'] = 'Could not update '.$key;
                                            $_SESSION['error'] = true;
                                            $is_ok = false;
                                        }
                                        $response['error'] = false;
                                    } else{
                                        $_SESSION['messager'] = 'Error uploading image.';
                                        $_SESSION['error'] = true;
                                    }
                                }
                            } else {
                                $_SESSION['messager'] = 'Error with image file extension.';
                                $_SESSION['error'] = true;
                            }
                        } else {
                            $_SESSION['messager'] = 'Image must be smaller than 25MB.';
                            $_SESSION['error'] = true;
                        }
                    } else {
                        $_SESSION['messager'] = 'Error with image file type.';
                        $_SESSION['error'] = true;
                    }
                // $tmp = end($extension);
                }  else {
                    $_SESSION['messager'] = 'Error submitting image.';
                    $_SESSION['error'] = false;
                }
                if(!is_array($result)){
                    $_SESSION['e_message'] = 'Could not update '.$key;
                    $_SESSION['error'] = true;
                    break;
                }
            }
        }
    }
    
}
$site_title = 'Update Settings'
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
    <head>
    <title><?php echo $site_title; ?></title>
          <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
          <link rel="icon" href="images/favicon.ico" type="image/x-icon">

          <link rel="stylesheet" href="resources/css/style.css?d=<?php echo time(); ?>" type="text/css" media="screen and (min-device-width : 641px)" />
          <link href="resources/css/style.css?d=<?php echo time(); ?>" rel="stylesheet" type="text/css" media="(orientation:portrait)" />
          <link href="resources/css/style.css?d=<?php echo time(); ?>" rel="stylesheet" type="text/css" media="(orientation:landscape)" />
          <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <head>
    <body>
        <?php  
        if(isset($_SESSION['error'])){
            if($_SESSION['error'] == true){
                $class = ' alert-red';
            } else {
                $class=" alert-green";
            }
            echo '<div class="alert'.$class.'" id="alert" style=display:block;>';
                echo '<span class="closebtn" onclick=closeAlertBox()>&times;</span>';
                echo '<strong>'.$_SESSION["e_message"].'</strong>';
            echo '</div>';
            $_SESSION['e_message'] = '';
            unset($_SESSION['error']);
        } ?>
        <div class="admin-wrapper">
            <h1 class="txt-center">SETTINGS</h1>
            <?php if(isset($settings) && is_array($settings)): ?>
                <div class="doc-section">
                    <form action="#" method="post" class=""  enctype="multipart/form-data">
                        <div class="pad-item txt-center">
                            <label class="image-display inline-block">
                                <img src="uploads/images/<?php echo htmlspecialchars($settings['logo']); ?>" class="settings-image clickable" id="logo-display"/>
                                <input type="file" id="logo" name="logo" style="display:none;" onchange="loadLogo(event)" />
                                <l class="btn-link block txt-center clickable">Upload Logo</l>
                            </label>
                        </div>
                        <div class="pad-item txt-center">
                            <label class="image-display inline-block">
                                <img src="uploads/images/<?php echo htmlspecialchars($settings['bg_image']); ?>" class="settings-image clickable" id="background-display"/>
                                <input type="file" id="background" name="image" style="display:none;" onchange="loadBG(event)" />
                                <l class="btn-link block txt-center clickable">Upload Background</l>
                            </label>
                        </div>
                        <div class="pad-item flex">
                            <label class="left-expand">Font Color (unhighlighted)</label>
                            <input type="text" name="color_text" placeholder="<?php echo htmlspecialchars($defaults['color_text']); ?>" value="<?php echo htmlspecialchars($settings['color_text']); ?>">
                        </div>
                        <div class="pad-item flex">
                            <label class="left-expand">Font Color (highlighted)</label>
                            <input type="text" name="color_hl_text" placeholder="<?php echo htmlspecialchars($defaults['color_hl_text']); ?>" value="<?php echo htmlspecialchars($settings['color_hl_text']); ?>">
                        </div>
                        <div class="pad-item flex">
                            <label class="left-expand">Branding Color (highlighted background)</label>
                            <input type="text" name="color_hl_bg"  placeholder="<?php echo htmlspecialchars($defaults['color_hl_bg']); ?>" value="<?php echo htmlspecialchars($settings['color_hl_bg']); ?>">
                        </div>
                        <div class="pad-item flex">
                            <label class="left-expand">Order Display Title</label>
                            <input type="text" name="display_title" placeholder="<?php echo htmlspecialchars($defaults['display_title']); ?>" value="<?php echo htmlspecialchars($settings['display_title']); ?>">
                        </div>
                        <div class="pad-item flex">
                            <label class="left-expand">Default Wait Time (Minutes)</label>
                            <input type="text" name="default_wait" placeholder="<?php echo htmlspecialchars($defaults['default_wait']); ?>" value="<?php echo htmlspecialchars($settings['default_wait']); ?>">
                        </div>
                        <div class="txt-center">
                            <input type="submit" name="ssubmit"  value="Save" class="submit-btn btn-link txt-center clickable">
                        </div>
                        <!-- branding -->

                        <!-- background image, logo, colors, buttons, text on display screen -->

                        <!-- twilio integration -->
                        <!-- api and secret, default message -->

                        <!-- default wait time -->

                        <!-- settings on controller -->
                        <!--  -->
                        
                    </form>
                </div>
            <?php endif; ?>
            <div class="doc-section">
                <h1 class="txt-center">TWILIO INTEGRATION</h1>
                <form action="#" method="post" type="">
                    <?php if(!isset($twilio_data) || !$twilio_data): ?>
                        <div class="pad-item flex">  
                            <label class="left-expand">Twilio Phone Number</label>
                            <input type="text" name="t_num">
                        </div>
                        <div class="pad-item flex">  
                            <label class="left-expand">Twilio SID</label>
                            <input type="text" name="t_sid">
                        </div>
                        <div class="pad-item flex">
                            <label class="left-expand">Twilio Secret</label>
                            <input type="text" name="t_sec">
                        </div>
                        <div class="txt-center">
                            <input type="submit" name="tsubmit" value="Save"  class="submit-btn btn-link txt-center clickable">
                        </div>
                
                    <?php else: ?>
                        <div class="pad-item flex">
                            <label class="left-expand">Twilio Integration Successful</label>
                            <input type="submit" name="tdsubmit" value="Disable" class="btn-done btn-link txt-center clickable">
                            
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <script>
            var loadLogo = function(event) {
                var output = document.getElementById('logo-display');
                output.src = URL.createObjectURL(event.target.files[0]);
            };
            var loadBG = function(event) {
                var output = document.getElementById('background-display');
                output.src = URL.createObjectURL(event.target.files[0]);
            };
        </script>
    </body>
</html>