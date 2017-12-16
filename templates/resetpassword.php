<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Jimbo - Reset Password</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>  
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

		<!--== CSS Files ==-->
		<link href="<?php echo WEB_URL; ?>/css/bootstrap.css" rel="stylesheet" media="screen">
		<link href="<?php echo WEB_URL; ?>/css/dialog.css" rel="stylesheet" media="screen">
		<link href="<?php echo WEB_URL; ?>/css/responsive.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="<?php echo WEB_URL; ?>/templates/css/boostrap.css">
    <!-- Font Awesome styles (icons) -->
    <link rel="stylesheet" href="<?php echo WEB_URL; ?>/templates/css/font_awesome.css">
    <!-- Main Template styles -->
    <link rel="stylesheet" href="<?php echo WEB_URL; ?>/templates/css/styles.css">

		<!--== Google Fonts ==-->
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700,400italic' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,700' rel='stylesheet' type='text/css'>
	</head>
<?php include "header.php" ?>
		<div id="home"></div>
		<!--<div id="navigation-wrap" class="navigation-wrap">
			<div id="navigation" class="navigation">
				<div class="container">
					<nav class="navbar navbar-custom" role="navigation">

						<div class="navbar-header">
							<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
							<a href="#register" class="register">Register</a>
						</div>

						<!--== Site Menu ==-->
						<!--<div class="collapse navbar-collapse" id="menu">
							<ul class="nav navbar-nav">
								<li class="active"><a href="#home">Home</a></li>
								<li><a href="#schedule">Schedule</a></li>
								<li><a href="#speakers">Speakers</a></li>
								<li><a href="#partners">Partners</a></li>
								<li><a href="#faq">FAQ</a></li>
								<li><a href="#news">News</a></li>
								<li><a href="#location">Location</a></li>
							</ul>
						</div>

					</nav>
				</div>
			</div>
		</div>-->

		<div class="container content">
			<!--===============================-->
			<!--== Subscribe & Register =======-->
			<!--===============================-->
            <?php if ($token != NULL){ ?>
			<section id="register" class="wow bounceInUp" data-wow-duration="0.8s" data-wow-delay="0.1s">
				<div class="row">
					<div class="col-sm-12 col-md-6">
						<div class="section-header text-left">
							<h2><b>Reset Password</b></h2>
							<p>Enter your desired password below</p>
						</div>
						<form id="reset-password-form" method="post" class="form">
							<input name="password" id="password" type="password" placeholder="New Password" required>
							<input name="confirm" id="confirm" type="password" placeholder="Confirm New Password" required>
							<button type="submit" name="submit" id="submit" class="button button-active">Change Password</button>
						</form>
					</div>
				</div>
			</section>
            <?php } else { ?>
            <section id="register" class="wow bounceInUp" data-wow-duration="0.8s" data-wow-delay="0.1s">
				<div class="row">
					<div class="col-sm-12 col-md-6">
						<div class="section-header text-left">
							<h2><b>Reset Password</b></h2>
							<p>This link has expired or is invalid.<br /> Try resetting your password again from the mobile app.</p>
						</div>
						
					</div>
				</div>
			</section>
            <?php } ?>
			<!--==========-->
		</div>
		<!--== Javascript Files ==-->
		<!--<script src="/js/jquery-2.1.0.min.js"></script>-->
		<script src="<?php echo WEB_URL; ?>/js/libs/jquery-2.1.0.min.js"></script>
		<script src="<?php echo WEB_URL; ?>/templates/js/bootstrap.min.js"></script>
        <?php include "footer.php" ?>