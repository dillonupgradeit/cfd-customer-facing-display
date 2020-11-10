<?php
if (!isset($_SESSION)) {
    session_start();
}
error_log('login');
include("config.php");
include 'users.php';
$response = array();
//verify and update password
if (isset($_POST["submiter"])) {
    if (isset($_POST['reenter']) && isset($_POST['password']) && isset($_POST['link'])) {
        $link = $_POST['link'];
        $reenter = $_POST['reenter'];
        $pass = $_POST['password'];
        $dater = gmdate("Y-m-d H:i:s");
        $id = urldecode(base64_decode($_GET['u']));
        $link = $_GET['re'];
        $result = checkForgotPasswordByEmailLink($id,$link,$dater);
        if (is_array($result)){
            if (array_key_exists('token', $result)) {
                $resultzi = verifyPasswordToken($result['token'],$dater);
                if ($resultzi == 'TOKEN_VERIFIED') {

                    $response['error'] = false;
                    $response['message'] = 'Token Verified';
                    if ($pass === $reenter) {

                        $resulter = updateUserPass($pass,$result['uid']);
            
                        if ($resulter == 'PASS_UPDATED'){
            
            
                              $response['error'] = false;
                              $response['message'] = 'Password Updated';
            
                              $resulter = updateRecoveryEmails($result['id'],$dater);
                              if($resulter == 'RECOVERY_UPDATED'){
                                    $response['error'] = false;
                                    $response['message'] = 'Recovery Emails Updated';
                                    
                              } elseif($updater == 'RECOVERY_NOT_UPDATED'){
                                     $response['error'] = true;
                                    $response['message'] = 'Could Not Finish Updating Recovery Email';
                              } else {
                               $response['error'] = true;
                                $response['message'] = 'Error Updating Recovery Email';
                              }
            
                        } else {
                            $response['error'] = true;
                            $response['message'] = 'Error Updating Password';
            
                         }
            
                    } else {
                        echo 'Please Retype Password';
            
                    }

                } elseif($resultzi == 'TOKEN_NOT_VERIFIED') {
                    $response['error'] = true;
                    $response['message'] = 'Email link unable to be verified.';

                }
            } else {
                    $response['error'] = true;
                    $response['message'] = 'No Email Token Found';

            }
        } else {
            $response['error'] = true;
            $response['message'] = 'Email Link Invalid';

        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Parameters are missing';

    }
//create forgot password token and email
} elseif (isset($_POST['fpsubmit']) && isset($_POST['email']))
{
    $dater = gmdate("Y-m-d H:i:s");
    $email = trim($_POST['email']);
    if(is_user_exist($email)){
        $result = checkUserForForgotPasswordByEmail($email);
        if(is_array($result) && array_key_exists('user_id',$result)){
             //create forgot password token
            $token = createPasswordToken($result['user_id']);
            $result['theemail'] = sendPasswordEmail($result['user_id'], $token['link'], $token['token'], $result['email'], $result['name'],$dater);
           
            $response['error'] = false;
            $response['message'] = 'success';

        }
    } else {
        $_SESSION['error'] = true;
        $_SESSION['e_message'] = 'Email not tied to an account.';
    }
} elseif(isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
	header('Location: home.php');
} elseif(isset($_GET['error'])){
    $error = $_GET['error'];
    // if($error == "email-exists"){
    //     $_SESSION['error'] = true;
    //     $_SESSION['e_message'] = "EMAIL ALREADY EXISTS IN OUR SYSTEM";
    // }
}elseif(isset($_GET['re']) && isset($_GET['u'])){
    //if coming from email link verify link
    $dater = gmdate("Y-m-d H:i:s");
    $id = urldecode(base64_decode($_GET['u']));
    $link = $_GET['re'];
    $result = checkForgotPasswordByEmailLink($id,$link,$dater);
    if (is_array($result)){
        if (array_key_exists('token', $result)) {
            $resultzi = verifyPasswordToken($result['token'],$dater);
            if ($resultzi == 'TOKEN_VERIFIED') {
                $response['error'] = false;
                $response['message'] = 'Token Verified';
            } elseif($resultzi == 'TOKEN_NOT_VERIFIED') {
                $response['error'] = true;
                $response['message'] = 'Email link unable to be verified.';
                $_SESSION['error'] = true;
                $_SESSION['e_message'] = $response['message'];
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'Email Link Bad Format';
            $_SESSION['error'] = true;
            $_SESSION['e_message'] = $response['message'];
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Email Link Invalid';
        $_SESSION['error'] = true;
        $_SESSION['e_message'] = $response['message'];
    }
}
$site_title = 'CFD Forgot Password';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
    <title><?php echo $site_title; ?></title>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script>
        function closeAlertBox() {
                document.getElementById('alert').style.display = "none";
        }
        function closeHoverAlertBox() {
                document.getElementById('hoverAlert').style.display = "none";
        }
    </script>
    </head>
    <body class="wrapper">
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
        <p></p>
        <div class="container">
            <?php
            //show successfully completed reset
            if(is_array($response) && array_key_exists('error',$response) && $response['error'] == false && $response['message'] == 'Recovery Emails Updated'):
                ?>
                <div class="row">
                    <div class="col-md-offset-3 col-md-6 ">
                        <div class="panel panel-info">
                            <div class="panel-heading"><b><center style="font-size: 18px;">SUCCESS!</center></b></div>
                            <table class="table table-striped">
                                <tr><td>
                                    <div class="eighty-center txt-gry details-label">
                                    Password Updated Successfully
                                    </div>
                                </td></tr>
                            </table>
                            <p></p>
                            <div class="panel-heading"><b><center style="font-size: 18px;">Login</center></b></div>
                            <table>
                                <tr>
                                    <a  class="btn btn-block btn-primary" style="text-align: center;" href="/cfd/login.php">Go to Login</a>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                </div>
                <?php
            elseif(is_array($response) && array_key_exists('error',$response) && $response['error'] == false && $response['message'] == 'success'):
                ?>
                <div class="row">
                    <div class="col-md-offset-3 col-md-6 ">
                        <div class="panel panel-info">
                            <div class="panel-heading"><b><center style="font-size: 18px;">CHECK YOUR EMAIL</center></b></div>
                            <table class="table table-striped">
                                <tr><td>
                                    <div class="eighty-center txt-gry details-label">
                                        Please check your email for a password reset link.
                                    </div>
                                </td></tr>
                            </table>
                            <p></p>
                            <div class="panel-heading"><b><center style="font-size: 18px;">Remember Your Password?</center></b></div>
                            <table>
                                <tr>
                                    <a  class="btn btn-block btn-primary" style="text-align: center;" href="/cfd/login.php">Go to Login</a>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                </div>
                <?php
            //if coming from email show reset passwrod form
            elseif(isset($_GET['re']) && isset($_GET['u'])):
                if (is_array($response) && array_key_exists('error', $response) && $response['error'] == false):
                    ?>
                    <div class="row">
                        <div class="col-md-offset-3 col-md-6 ">
                            <div class="panel panel-info">
                                <div class="panel-heading"><b><center style="font-size: 18px;">Reset Password</center></b></div>
                                <table class="table table-striped">
                                    <tr><td>
                                        <form action="#" method="post">
                                            <div class="form">
                                                <div>
                                                    <input type="password" name="password" placeholder="New Password" class="txtfld">
                                                </div>
                                                <div>    
                                                    <input type="password" name="reenter" placeholder="Re-enter Password" class="txtfld">
                                                </div>
                                                <div>
                                                        <input type="hidden" name="link" value="<?php echo $link; ?>">
                                                        <input type="submit" name="submiter" id="submiter" class="btn-submit">
                                                </div>
                                            </div>
                                        </form>
                                    </td></tr>
                                </table>
                                <p></p>
                                <div class="panel-heading"><b><center style="font-size: 18px;">Don't Have an Account?</center></b></div>
                                <table>
                                    <tr>
                                        <a href="register.php" class="btn btn-block btn-primary" style="text-align: center;">Register</a>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                    </div>
                    <?php
                else:
                ?>
                    <div class="row">
                        <div class="col-md-offset-3 col-md-6 ">
                            <div class="panel panel-info">
                                <div class="panel-heading"><b><center style="font-size: 18px;">Forgot Your Password?</center></b></div>
                                <table class="table table-striped">
                                    <tr><td>
                                        <form action="#" method="post">
                                            <input type="text" name="email" class="input-field" placeholder="Email Address">
                                            <input type="submit" name="fpsubmit" class="btn-submit">
                                        </form>
                                    </td></tr>
                                </table>
                                <p></p>
                                <div class="panel-heading"><b><center style="font-size: 18px;">Login</center></b></div>
                                <table>
                                    <tr>
                                        <a  class="btn btn-block btn-primary" style="text-align: center;" href="/cfd/login.php">Go to Login</a>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                    </div>
                    <?php   
                
                endif;
            //ask for email to recovery password
            elseif (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']): ?>
                <div class="row">
					<div class="col-md-offset-3 col-md-6 ">
						<div class="panel panel-info">
							<div class="panel-heading"><b><center style="font-size: 18px;">Forgot Your Password?</center></b></div>
							<table class="table table-striped">
                                <tr><td>
                                    <form action="#" method="post">
                                        <input type="text" name="email" class="input-field" placeholder="Email Address">
                                        <input type="submit" name="fpsubmit" class="btn-submit">
                                    </form>
                                </td></tr>
                            </table>
                            <p></p>
                            <div class="panel-heading"><b><center style="font-size: 18px;">Don't Have an Account?</center></b></div>
                            <table>
                                <tr>
                                    <a href="register.php" class="btn btn-block btn-primary" style="text-align: center;">Register</a>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    
                </div>
                <?php
            endif; ?>
        </div>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
      <script src="js/bootstrap.min.js"></script>
   </body>
</html>