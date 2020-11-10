<?php
if (!isset($_SESSION)) {
    session_start();
}
error_log('login');
include("config.php");
if (isset($_POST['lsubmit']) && isset($_POST['email']) && isset($_POST['password']))
{
    $email = $_POST['email'];
    $pass = md5($_POST['password']);
    if($stmt = $con->prepare("SELECT token.merchant_id,token.employee_id,token.access_token,users.id,users.name FROM token,users WHERE token.merchant_id = users.merchant_id AND users.id IN (SELECT users.id FROM users WHERE users.email = ? AND users.pass = ?) LIMIT 1"))
        {
            $stmt->bind_param("ss", $email,$pass);
            $stmt->execute();
            $stmt->bind_result($mid,$eid, $token,$uid,$name);
            $stmt->fetch();
            if($mid !== null){
                $_SESSION['mid'] = $mid;
                $_SESSION['eid'] = $eid;
                $_SESSION['token'] = $token;
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $uid;
                $_SESSION['user_name'] = $name;
                $stmt->close();
                header('Location: home.php');
            } else {
                $_SESSION['error'] = true;
                $_SESSION['e_message'] = 'Invalid Login Credentials';
            }
        } else {
            $tmp = 'zero results';
            $_SESSION['error'] = true;
            $_SESSION['e_message'] = 'Invalid Login'.$con->error;
            $_SESSION['logged_in'] = false;

        }
        
} elseif(isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
	header('Location: home.php');
} elseif(isset($_GET['error'])){
    $error = $_GET['error'];
    if($error == "email-exists"){
        $_SESSION['error'] = true;
        $_SESSION['e_message'] = "EMAIL ALREADY EXISTS IN OUR SYSTEM";
    }
}
$site_title = 'CFD Login';
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
            if (!isset($_SESSION['token'])): ?>
                <div class="row">
					<div class="col-md-offset-3 col-md-6 ">
						<div class="panel panel-info">
							<div class="panel-heading"><b><center style="font-size: 18px;">Login</center></b></div>
							<table class="table table-striped">
                                <tr><td>
                                    <form action="#" method="post">
                                        <input type="text" name="email" class="input-field" placeholder="Email Address">
                                        <input type="password" name="password" class="input-field" placeholder="Password">
                                        <input type="submit" name="lsubmit" class="btn-submit">
                                    </form>
                                </td></tr>
                                <tr><td>
                                       <a href="forgot-password.php" class="" style="text-align: center;">Forgot Password?</a>
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