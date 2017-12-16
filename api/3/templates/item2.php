<!doctype html>
<html class="no-js" lang="en"><head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Jimbo: Sell and discover items near you...</title>
        <meta name="description" content="Jimbo helps you sell &amp; discover items near you...">
        <meta name="author" content="Jimbo">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        
        <meta property="og:title" content="Buy <?php echo $title ?> online on Jimbo" />
        <meta property="og:site_name" content="Jimbo Shop Online"/>
        <meta property="og:url" content="http://jimbo.co/items/<?php echo $id ?>" />
        <meta property="og:image" content="http://jimbo.co/images/uploads/items/<?php $e = explode(",",$image); echo $e[0]; ?>" />
        <meta property="og:description" content="<?php echo $description ?>" />
        <meta property="fb:app_id" content="[311245872363009]" />
        <meta property="og:type" content="article" />
        
        
        
        <link href="css/bootstrap.css" rel="stylesheet" />
        <!-- Override Stylesheet -->
        <link href="css/slick.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <!--[if IE 9]>
            <link href="css/ie9.css" media="screen, projection" rel="stylesheet" type="text/css" />
        <![endif]-->
        <!-- HTML5 Shiv -->
        <!--[if lt IE 9]>
        <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <script>window.html5 || document.write('<script src="javascripts/libs/html5.js"><\/script>')</script>
        <![endif]-->
    </head>
    <body>
        <div class="header">
           	<div class="container">
           		<div class="iphone">
           			<div class="slideshow">
	            		<div class="slider">
                        <?php $e = explode(",",$image); foreach($e as $i){ ?>
	            			<div class="slide" style="background-image: url(/images/uploads/items/<?php echo $i; ?>)"></div>
                            <?php } ?>
	            		</div>
	            	</div>
           		</div>
           	</div>
        </div>
        <div class="main">
            <div class="container">
            	<div class="content">
	            	<div class="main-content">
	            		<h2><?php echo $title ?></h2>
		            	<p><?php echo $description ?></p>
	            	</div>
	            </div>
            </div>
        </div>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/libs/jquery-1.11.2.min.js"><\/script>')</script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="js/script.js"></script>

        <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-51493243-1', 'jimbo.co');
  ga('send', 'pageview');

</script>
    </body>
</html>
