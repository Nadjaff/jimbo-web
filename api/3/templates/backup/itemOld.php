<!DOCTYPE html>
<html>
<html class="no-js" lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Jimbo - Buy <?php echo $title ?> online with Jimbo</title>
        <meta name="description" content="Jimbo helps you sell &amp; discover items near you...">
        <meta name="author" content="Jimbo">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Override Stylesheet -->
        <link href="http://jimbo.co/templates/css/style.css" rel="stylesheet" />
        <link href="http://jimbo.co/templates/css/bootstrap.css" rel="stylesheet" />

        <meta property="og:title" content="Buy <?php echo $title ?> online on Jimbo" />
        <meta property="og:site_name" content="Jimbo Shop Online"/>
        <meta property="og:url" content="http://jimbo.co/items/<?php echo $id ?>" />
        <meta property="og:image" content="http://jimbo.co/images/uploads/items/<?php $e = explode(",",$image); echo $e[0]; ?>" />
        <meta property="og:description" content="<?php echo $description ?>" />
        <meta property="fb:app_id" content="[311245872363009]" />
        <meta property="og:type" content="article" />
    </head>

<body>

      <nav class="navbar navbar-fixed-top">
      	<div class="container">
          <div class="logo">
            <a href="http://jimb.co"><img src="http://jimbo.co/templates/images/logo.png" alt="Jimbo Logo"></a>
          </div>
          <div class="download-app">
             <a href="https://play.google.com/store/apps/details?id=com.vp.jimbo&hl=en" class="google-btn"><img src="http://jimbo.co/templates/images/google-play-btn.png" alt="Download"></a>
          </div>
         </div>
      </nav>
	<div class="container margin-top">
      <div class="content">
        <div class="main-item col-md-6 col-xs-12">
          <div class="item">
            <div class="item-header">
            <div class="profile-image">
              <img class="profile-pic"src="http://jimbo.co/images/uploads/profile/<?php echo $userimage ?>" />
            </div>
            <div class="profile-info">
              <span class="username"><?php echo $username ?></span><span class="hyphen"> - </span><span class="date"> <!--<?php echo $created_at ?>-->2min </span><br>
              <span class="location"><img src="http://jimbo.co/templates/images/location.png" alt="Location"> <?php echo $locality ?></span>
            </div>
            <div class="price">
              <span>$<?php echo $price ?></span>
            </div>
          </div>
            <div class="item-photo">
              <img src="http://jimbo.co/images/uploads/items/<?php $e = explode(",",$image); echo $e[0]; ?>" />
            </div>
          </div>
        </div>
        <div class="item-details col-md-6 col-xs-12">

          <div class="download-btn">
		          <a href="https://play.google.com/store/apps/details?id=com.vp.jimbo&amp;hl=en" class="btn btn-lg btn-default"><img src="http://jimbo.co/templates/images/google-play-btn-big.png" alt="Download On Google Play"/></a> <span>Discover more items in the app</span>
		      </div>

          <h2><?php echo $title ?></h2>
          <p><?php echo $description ?></p>
          <div class="download-btn2">
              <a href="https://play.google.com/store/apps/details?id=com.vp.jimbo&amp;hl=en" class="btn btn-lg btn-default"><img src="http://jimbo.co/templates/images/google-play-btn-big.png" alt="Download On Google Play"/></a>
          </div>
          <!--<div class="comments">
          <strong class="comments-title">Latest Comments</strong>
          <ul>
            <li><strong>tkay_lola</strong> hey @ramikabar what do you think?</li>
            <li><strong>michaelkay</strong> Just noticed how disorganised the table looks</li>
            <li><strong>jared_wilson</strong> hey @ramikabar what do you think?</li>
            <li><strong>alexia</strong> an amazing birthday lunch</li>
            <li><strong>jayden</strong> hey @ramikabar what do you think?</li>
          </ul>
        </div>-->

          <div class="contact-seller-form">
            <h2>Contact Seller</h2>
            <form method="POST" action="#">
              <input type="text" placeholder="Name">
              <input type="email" placeholder="Email"><br>
              <textarea placeholder="Message"></textarea>
            </form>
          </div>

        </div>
      </div>

      <div class="container copyright-jimbo col-xs-12">
        <p class="copyright">© 2015 Jimbo.co | Contact Us: <a href="mailto:info@jimbo.co" class="email">info@jimbo.co</a></p>
      </div>
  </div>
</div>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</body>
</html>
