<?php
if (!isset($_SESSION)) {
    session_start();
}
error_log('register');
include("config.php");
if(isset($_POST['psubmit']) && isset($_SESSION['user_id'])){
	update_user_cred();
} elseif (isset($_GET['code']))
{
    create_update_login();
	
} elseif(isset($_SESSION['token'])) {
	// var_dump($_POST);
	// var_dump($_SESSION);
	header('Location: home.php');
} 
function update_user_cred() {
	include("config.php");
	include("users.php");
	include 'customers.php';
	if(isset($_POST['password']) && isset($_POST['confirm_password']) && $_POST['password'] != '' && isset($_POST['email']) && isset($_POST['name'])){
		$pass2 = $_POST['confirm_password'];
		$pass = $_POST['password'];
		$email = $_POST['email'];
		$name = $_POST['name'];
		if($pass2 == $pass){
			$uid = $_SESSION["user_id"];
			$result = update_user($uid,$name,$email,$pass);
			if(is_array($result) && array_key_exists('success',$result) && $result['success']){
				$_SESSION['logged_in'] = true;
				$_SESSION['user_email'] = $email;
				$_SESSION['user_name'] = $name;
				header('Location: home.php');
			} elseif($result == "USER_ALREADY_EXISTS"){
				unset($_SESSION);
				session_destroy();
				if (!isset($_SESSION)) {
					session_start();
				}
				$_SESSION['error'] = true;
				$_SESSION['e_message'] = "EMAIL ALREADY EXISTS IN OUR SYSTEM";
				header('Location: login.php?error=email-exists');
			}else {
				$_SESSION['error'] = true;
				$_SESSION['e_message'] = 'There was a problem registering your account. Please contact support@upgrade-sf.com';
			}
		} else {
			$_SESSION['error'] = true;
			$_SESSION['e_message'] = 'Passwords do not match.';
		}
	} else {
		$_SESSION['error'] = true;
		$_SESSION['e_message'] = 'Please fill out entire form.';
	}
}
function create_update_login(){
	include("config.php");
	include("users.php");
	$_SESSION['mid'] = $_GET['merchant_id'];
	$_SESSION['eid'] = $_GET['employee_id'];
	
    $code = $_GET['code'];
	$cid = $_GET['client_id'];
	$merchant_id = $_SESSION['mid'];
    $employee_id = $_SESSION['eid'];
	
	//GET ACCESS TOKEN FROM CODE
	$access_token = get_clover_token($cid,$code);
	error_log('accesstoken: '.$access_token);
	if($access_token){
		//INSERT OR UPDATE TOKEN
		$result = add_update_token($merchant_id,$employee_id,$access_token);
		if($result == 'TOKEN_NOT_UPDATED'){
			$_SESSION['error'] = true;
			$_SESSION['e_message'] = $result;
		}elseif($result == 'TOKEN_ALREADY_EXISTS'){
			$_SESSION['error'] = true;
			$_SESSION['e_message'] = 'token'.$result;
		}else {
			//IF NOT LOGGED IN THEN CHECK AND CREATE USER
			if(!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']){
				$detail = get_merchant_details($merchant_id,$access_token);
				if(is_array($detail)){
					$owner_email = $detail["owner"]["email"];
					$owner_name = $detail["owner"]["name"];
					$user = add_new_user($merchant_id,$owner_name);
					if(!is_numeric($user)){
						$_SESSION['error'] = true;
						$_SESSION['e_message'] = $user;
					} else {
						$_SESSION['logged_in'] = false;
						$_SESSION['token'] = $access_token;
						$_SESSION['user_id'] = $user;
						$_SESSION['user_email'] = $owner_email;
						$_SESSION['user_name'] = $owner_name;
						add_default_settings($merchant_id);
					}
				} else {
					$_SESSION['error'] = true;
					$_SESSION['e_message'] = $detail;
				}
			} else {
				$_SESSION['error'] = false;
				$_SESSION['e_message'] = 'Token Updated';
				$_SESSION['token'] = $access_token;
			}
		}
	} else {
		$_SESSION['error'] = true;
		$_SESSION['e_message'] = 'Access Token Invalid';
	}
}
function add_default_settings($mid){
	include 'settings.php';
	$defaults = array();
    $defaults['color_text'] = '#000000';
    $defaults['color_hl_text'] = '#ffffff';
    $defaults['color_hl_bg'] = '#9ed637';
    $defaults['display_title'] = 'Current Orders';
    $defaults['default_wait'] = '30';
    $defaults['default_range'] = '0';
    $defaults['dynamic_wait'] = 0;
    $defaults['logo'] = 'default.png';
    $defaults['bg_image'] = 'default-bg.jpg';
    $chcker = ['color_text','color_hl_text','color_hl_bg','display_title','default_wait','default_range','dynamic_wait'];
	// $is_ok = true;
	foreach($defaults as $key => $value){
		if(in_array($key,$chcker)){
			$result = add_update_setting($mid,$key,$value);
			if(!is_array($result)){
				// $_SESSION['e_message'] = 'Could not update '.$key;
				// $_SESSION['error'] = true;
				// $is_ok = false;
				break;
			}
		}
	} 
}
$site_title = 'CFD Register';
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
		  	<div class="row">
				<div class="col-md-offset-3 col-md-6 ">
						<div class="panel panel-info">
							<?php
							if (isset($_SESSION['user_id']) && !$_SESSION['logged_in']): ?>
								<div class="panel-heading"><b><center style="font-size: 18px;">Create a Password</center></b></div>
								<table class="table table-striped">
									<tr>
										<form action="#" method="post">
											<p>
												<input type="text" placeholder="Name" name="name" class="input-field" value="<?php echo $_SESSION['user_name']; ?>">
											</p>
											<p>
												<input type="text" placeholder="Email" name="email" class="input-field" value="<?php echo $_SESSION['user_email']; ?>">
											</p>
											<p>
												<input type="password" placeholder="Password" name="password" class="input-field">
											</p>
											<p>
												<input type="password" placeholder="Confirm Password" name="confirm_password" class="input-field">
											</p>
											<p>
												<input type="submit" class="btn-submit" name="psubmit">
											</p>
										</form>
									</tr>
								</table>
								<?php
							else: ?>
								<div class="panel-heading"><b><center style="font-size: 18px;">Register</center></b></div>
								<table class="table table-striped">
									<tr>
										<a href="<?php echo $api_url; ?>/oauth/authorize?client_id=<?php echo $client_id; ?>&redirect_uri=<?php echo $redirect_uri; ?>register.php" class="btn btn-block btn-primary" style="text-align: center;">Make Clover Authorization<i class="fa fa-fw fa-plus-circle"></i></a>
									<!-- 
									For authorization here we are using url of sandbox with client id and redirect url. We will client id from developer site of clover. Redirect url is used to redirect the page where you get access token.
									-->
									
									</tr>
							</table>
							<p></p>
							<div class="panel-heading"><b><center style="font-size: 18px;">Already Have Account?</center></b></div>
							<table>
								<tr>
									<a href="login.php" class="btn btn-block btn-primary" style="text-align: center;">Login<i class="fa fa-fw fa-plus-circle"></i></a>
								</tr>
							</table>
								<?php
							endif; ?>

						</div>
					</div>
				</div>
			</div>
	    </div>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
      <script src="js/bootstrap.min.js"></script>
   </body>
</html>