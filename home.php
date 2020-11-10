<?php
if (!isset($_SESSION)) {
    session_start();
}
include("config.php");
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']){
	header('Location: login.php');
} else {
	$merchant_id = $_SESSION['mid'];
	$employee_id = $_SESSION['eid'];
	$access_token = $_SESSION['token'];
	include 'orders-to-db.php';
}
$site_title = 'CFD Home';

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
   <body>
      	<p></p>
      	<div class="container">
		  	<a href="logout.php">LOGOUT</a>
			<?php
			if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
				<div class="row">
					<div class="col-md-4 col-md-offset-4">
					<div class="btn btn-block btn-success" style="text-align: center;">Authenticated<i class="fa fa-fw fa-plus-circle"></i></a></div>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-md-offset-3 col-md-6 ">
						<div class="panel panel-info">
							<div class="panel-heading"><b><center style="font-size: 18px;">Your Clover Detail</center></b></div>
							<table class="table table-striped">
								<tr>
									<td>Merchant id:</td>
									<td>
										<?php
										echo $merchant_id;
										?>
									</td>
								</tr>
								<!-- <tr>
									<td>Access Token:</td>
									<td>
								<?php 
									//echo $access_token;
								?>
									</td>
								</tr> -->
							</table>
						</div>
						<center><a href="display.php"> Order Display</a></center>
						<center><a href="controller.php"> Controller</a></center>
						<center><a href="update-settings.php"> Settings</a></center>
					</div>
				</div>
				<?php
			endif; ?>
	    </div>	
		<table style="position:absolute;bottom:20px;text-align:center;width:100%;"><tr><td style="margin:0px 25px;width:50%;"><a href="https://veganmob.biz/cfd/privacy-policy.txt">Privacy Policy</a></td><td style="margin:0px 25px;width:50%;"><a href="https://veganmob.biz/cfd/resources/docs/">Resources & Documentation</a></td></tr></table>	
   </body>
</html>