<!DOCTYPE HTML>
<html>

<head>
    <title>Jimbo - Create Item</title>
    <!-- meta info -->
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
    <meta name="keywords" content="Koupon HTML5 Template" />
    <meta name="description" content="Koupon - Premiun HTML5 Template for Coupons Website">
    <meta name="author" content="Tsoy">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google fonts -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700,300' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Roboto:400,100,300' rel='stylesheet' type='text/css'>
    <!-- Bootstrap styles -->
    <link rel="stylesheet" href="/api/3/templates/css/steve.css">
    <link rel="stylesheet" href="/api/3/templates/css/boostrap.css">
    <!-- Font Awesome styles (icons) -->
    <link rel="stylesheet" href="/api/3/templates/css/font_awesome.css">
    <!-- Main Template styles -->
    <link rel="stylesheet" href="/api/3/templates/css/styles.css">
    <!-- IE 8 Fallback -->
    <!--[if lt IE 9]>
	<link rel="stylesheet" type="text/css" href="css/ie.css" />
<![endif]-->

    <!-- Your custom styles (blank file) -->
    <link rel="stylesheet" href="/api/3/templates/css/mystyles.css">
    <link rel="stylesheet" href="/api/3/templates/css/steve.css">


</head>

<body>


    <div class="global-wrap">


        <!-- //////////////////////////////////
	//////////////MAIN HEADER/////////////
	////////////////////////////////////-->

	


        <!-- SEARCH AREA -->
        <form class="search-area form-group">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-sm-5 ">
                      <a href="http://jimboapi.herokuapp.com/api/3" class="logo"><img src="/api/3/templates/img/logo.png" alt="Jimbo Logo" /></a>
                    </div>
                </div>
            </div>
        </form>
        <!-- END SEARCH AREA -->

        <div class="gap"></div>


        <!-- //////////////////////////////////
	//////////////END MAIN HEADER////////// 
	////////////////////////////////////-->


        <!-- //////////////////////////////////
	//////////////PAGE CONTENT///////////// 
	////////////////////////////////////-->



        <div class="container">
            <div class="row row-wrap">
                <div class="col-md-6">
                    <div id="img-canvas" style="width:400px; height:400px;overflow:hidden"><img src="<?php echo $img ?>" /></div>
                </div>
                <div class="col-md-3">
                    <form name="addToJimboForm" id="add-to-jimbo-form" class="contact-form" method="post">
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" id="imageurl" type="text" placeholder="Enter an image url" value="<?php echo $img ?>" />
                            </div>
                            <div class="form-group">
                                <input class="form-control" id="itemurl" type="text" placeholder="Enter an page url" value="<?php echo $url ?>" />
                            </div>
                            <div class="form-group">
                                <input class="form-control" id="title" type="text" placeholder="Enter a title" value="<?php echo $title ?>" />
                            </div>
                            <div class="form-group">
                                <input class="form-control" id="price" type="text" placeholder="$XX.XX" value="<?php echo $price ?>" />
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" id="description" placeholder="Your description goes here" value="<?php echo $desc ?>" ><?php echo $desc ?></textarea>
                            </div>
                            <div class="form-group">
                                <input class="form-control" id="quantity" type="text" placeholder="Quantity" value="1" />
                            </div>
                            <div class="bg-warning alert-success form-alert" id="form-success">Your message has been sent successfully!</div>
                            <div class="bg-warning alert-error form-alert" id="form-fail">Sorry, error occured this time sending your message</div>
                            <button id="submit" type="submit" class="btn btn-primary">Add to Jimbo</button>
                        </fieldset>
                    </form>
                </div>
            </div>
            <div class="gap gap-small"></div>
        </div>


        <!-- //////////////////////////////////
	//////////////END PAGE CONTENT///////// 
	////////////////////////////////////-->



        <!-- //////////////////////////////////
	//////////////MAIN FOOTER////////////// 
	////////////////////////////////////-->

        <!-- //////////////////////////////////
	//////////////MAIN FOOTER//////////////
	////////////////////////////////////-->
            <div class="footer-copyright">
                <div class="container">
                    <div class="row">
                        <div class="col-md-4">
                            <p>Copyright © 2015, Jimbo.co</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- //////////////////////////////////
	//////////////END MAIN  FOOTER///////// 
	////////////////////////////////////-->



        <!-- Scripts queries -->
        <script src="/api/3/templates/js/jquery.js"></script>
        <script src="/api/3/templates/js/boostrap.min.js"></script>
        <script src="/api/3/templates/js/countdown.min.js"></script>
        <script src="/api/3/templates/js/flexnav.min.js"></script>
        <script src="/api/3/templates/js/magnific.js"></script>
        <script src="/api/3/templates/js/tweet.min.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
        <script src="/api/3/templates/js/fitvids.min.js"></script>
        <script src="/api/3/templates/js/mail.min.js"></script>
        <script src="/api/3/templates/js/ionrangeslider.js"></script>
        <script src="/api/3/templates/js/icheck.js"></script>
        <script src="/api/3/templates/js/fotorama.js"></script>
        <script src="/api/3/templates/js/card-payment.js"></script>
        <script src="/api/3/templates/js/owl-carousel.js"></script>
        <script src="/api/3/templates/js/masonry.js"></script>
        <script src="/api/3/templates/js/nicescroll.js"></script>

        <!-- Custom scripts -->
        <script src="/api/3/templates/js/custom.js"></script>
		<script src="/js/jimbo_dialog.js"></script>
    </div>
</body>

</html>
