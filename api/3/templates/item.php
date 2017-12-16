<!DOCTYPE HTML>
<html>

<head>
    <title>Jimbo - Buy <?php echo $title ?> online with Jimbo</title>
    <!-- meta info -->
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
    <meta name="description" content="Jimbo helps you sell &amp; discover items near you...">
    <meta name="author" content="<?php echo $username ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google fonts -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700,300' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Roboto:400,100,300' rel='stylesheet' type='text/css'>
    <!-- Bootstrap styles -->
    <link rel="stylesheet" href="/templates/css/boostrap.css">
    <!-- Font Awesome styles (icons) -->
    <link rel="stylesheet" href="/templates/css/font_awesome.css">
    <!-- Main Template styles -->
    <link rel="stylesheet" href="/templates/css/styles.css">
    <!-- IE 8 Fallback -->
    <!--[if lt IE 9]>
	<link rel="stylesheet" type="text/css" href="css/ie.css" />
<![endif]-->

    <!-- Your custom styles (blank file) -->
    <link rel="stylesheet" href="/templates/css/mystyles.css">


</head>

<?php include "header.php" ?>
<div class="container">
            <div class="row">
            <div class="col-xs-12 col-md-3 ">
            	<div class="main-nav">
            		<ul>
                    	<a href="#"><li>Home</li></a>
                        <a href="#"><li class="active">Explore</li></a>
                        <a href="#"><li>Favourited Items</li></a>
                        <a href="#"><li>Sold Items</li></a>
                        <a href="#"><li>Profile</li>
                    </ul>
            	</div>
			</div>
                <div class="col-xs-12 col-md-5 ">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="fotorama" data-nav="thumbs" data-allowfullscreen="1" data-thumbheight="150" data-thumbwidth="150">
                            <?php $images = explode(",", $image);
							foreach ($images as $img){ ?>
                                <img src="http://jimbo.co/images/uploads/items/<?php echo $img ?>" />
                                <?php } ?>
                            </div>



                            <div class="gap-hidden">

                            <div class="product-info box item">
                              <div class="profile-image">
                                 <img class="profile-pic" src="http://jimbo.co/images/uploads/profile/<?php echo $userimage ?>">
                               </div>
                               <div class="profile-info">
                                 <span class="username"><?php echo $username ?></span><span class="hyphen"> - </span><span class="date"> <?php $created_at_date = new DateTime($created_at); 
								 $since_start = $created_at_date->diff(new DateTime());
								 if ($since_start->days < 7){
								 if ($since_start->days < 1){
								 	if ($since_start->h < 1){
										 echo $since_start->i . "m";
									}else{
										 echo $since_start->h . "h";
									 }
								 }else{
									 echo $since_start->days . "d";
								 }
								 }else{
									 echo floor($since_start->days/7) . "w";
								 }?></span><br>
                                 <span class="location"><img src="/templates/img/location.png" alt="Location"> <?php echo $locality ?></span>
                               </div>
                                <!--<ul class="icon-group icon-list-rating text-color" title="4.5/5 rating">
                                    <li><i class="fa fa-star"></i>
                                    </li>
                                    <li><i class="fa fa-star"></i>
                                    </li>
                                    <li><i class="fa fa-star"></i>
                                    </li>
                                    <li><i class="fa fa-star"></i>
                                    </li>
                                    <li><i class="fa fa-star-half-empty"></i>
                                    </li>
                                  </ul>-->
                                  <br>
                                <h4><?php echo $title ?></h4>
                                <p class="product-info-price">$<?php echo $price/1000000 ?></p>
                                <p class="text-smaller text-muted"><?php echo formatText($description) ?></p>
                                <br>
                                <ul class="list-inline">
                                    <li><a href="#contact-popup2" class="btn btn-primary popup-text" data-effect="mfp-zoom-out"> Contact Seller</a>
                                    </li>
                                    <li class="social-share">
                                    <h4>Share:</h4>
                                    <div class="a2a_kit a2a_kit_size_32 a2a_default_style social-links">
                                      <a class="a2a_button_facebook"></a>
                                      <a class="a2a_button_twitter"></a>
                                      <a class="a2a_button_google_plus"></a>
                                      <a class="a2a_button_pinterest"></a>
                                    </div>
                                  <script type="text/javascript" src="//static.addtoany.com/menu/page.js"></script>
                                </li>  
                                      
                                </ul>
                                <div id="contact-popup" class="mfp-with-anim mfp-hide mfp-dialog">
                                  <h2>Select Device to Contact this Seller</h2>
                                  <div class="row">
                                    <div class="col-xs-12 col-sm-6 device-button">
                                      <a href="https://play.google.com/store/apps/details?id=com.vp.jimbo&hl=en">
                                        <div class="centered">
                                          <img src="/templates/img/download-android.png" alt="Download on Android" />
                                          <h4>Android</h4>
                                        </div>
                                      </a>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 device-button">
                                      <a href="#contact-popup2" class="popup-text" data-effect="mfp-zoom-out">  
                                        <div class="centered">
                                          <img src="/templates/img/download-iphone.png" alt="Download on iPhone" />
                                          <h4>iPhone</h4>
                                        </div>
                                      </a>
                                    </div>
                                    </div>
                                    <div class="row other-option">
                                      <h5>If you don’t use a mobile device<br>
                                      <a href="#">click here</a></h5>
                                    </div>
                                  
                                </div>






                                <div id="contact-popup2" class="mfp-with-anim mfp-hide mfp-dialog">
                                  <h2>Contact Seller</h2>
                                  <div class="row">
                                    <div class="col-xs-12">
                                      <form name="contactForm" id="contact-form" class="contact-form" method="post" action="items/<?php echo $item_id ?>/contact">
                        <fieldset>
                            <div class="form-group">
                                <label>Name</label>
                                <div class="bg-warning form-alert" id="form-alert-name">Please enter your name</div>
                                <input class="form-control" id="name" type="text" placeholder="Enter Your name here">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <div class="bg-warning form-alert" id="form-alert-email">Please enter your valid E-mail</div>
                                <input class="form-control" id="email" type="text" placeholder="You E-mail Address">
                            </div>
                            <div class="form-group">
                                <label>Message</label>
                                <div class="bg-warning form-alert" id="form-alert-message">Please enter message</div>
                                <textarea class="form-control" id="message" placeholder="Your message"></textarea>
                            </div>
                            <div class="bg-warning alert-success form-alert" id="form-success">Your message has been sent successfully!</div>
                            <div class="bg-warning alert-error form-alert" id="form-fail">Sorry, error occured this time sending your message</div>
                            <button id="send-message" type="submit" class="btn btn-primary">Send Message</button>
                        </fieldset>
                    </form>

                    </div>
                  </div>
                </div>
              </div>





                            <div class="gap gap-small"></div>


                    <div class="tabbable">
                        <div class="tab-content">
                            <div class="tab-pane  active" id="tab-1">
                                <ul class="comments-list">
                                    <?php for ($i=max(count($comments)-3,0);$i<count($comments);$i++){ $comment = $comments[$i]; ?>
                                    <li>
                                        <!-- REVIEW -->
                                        <article class="comment">
                                            <div class="comment-author">
                                                <img src="/images/uploads/profile/<?php echo $comment["userimage"] ?>" alt="Profile Image" />
                                            </div>
                                            <div class="comment-inner">
                                                <span class="comment-author-name"><?php echo $comment["username"] ?></span>
                                                <p class="comment-content"><?php echo formatText( $comment["comment"]); ?></p>
                                            </div>
                                        </article>
                                    </li>
                                <?php } ?>
                                </ul>
                                <a href="#contact-popup" class="view-all-comments popup-text" data-effect="mfp-zoom-out">View All Comments</a>
                            </div>
                        </div>
                    </div>




</div>


                    <div class="gap-mini"></div>






</div>
                </div>







                        </div>
                        <div class="gap-hidden"><div class="gap gap-small"></div></div>


                        <div class="gap-hidden-small">
                        <div class="col-md-4">
                            <div class="product-info box item">
                              <div class="profile-image">
                                 <img class="profile-pic" src="http://jimbo.co/images/uploads/profile/<?php echo $userimage ?>">
                               </div>
                               <div class="profile-info">
                                 <span class="username"><?php echo $username ?></span><span class="hyphen"> - </span><span class="date"> <?php $created_at_date = new DateTime($created_at); 
								 $since_start = $created_at_date->diff(new DateTime());
								 if ($since_start->days < 7){
								 if ($since_start->days < 1){
								 	if ($since_start->h < 1){
										 echo $since_start->i . "m";
									}else{
										 echo $since_start->h . "h";
									 }
								 }else{
									 echo $since_start->days . "d";
								 }
								 }else{
									 echo floor($since_start->days/7) . "w";
								 }?></span><br>
                                 <span class="location"><img src="/templates/img/location.png" alt="Location"> <?php echo $locality ?></span>
                               </div>
                                <!--<ul class="icon-group icon-list-rating text-color" title="4.5/5 rating">
                                    <li><i class="fa fa-star"></i>
                                    </li>
                                    <li><i class="fa fa-star"></i>
                                    </li>
                                    <li><i class="fa fa-star"></i>
                                    </li>
                                    <li><i class="fa fa-star"></i>
                                    </li>
                                    <li><i class="fa fa-star-half-empty"></i>
                                    </li>
                                  </ul>-->
                                  <br>
                                <h4><?php echo $title ?></h4>
                                <p class="product-info-price">$<?php echo $price/1000000 ?></p>
                                <p class="text-smaller text-muted"><?php echo formatText($description) ?></p>
                                <br>
                                <ul class="list-inline">
                                    <li><a href="#contact-popup2" class="btn btn-primary popup-text" data-effect="mfp-zoom-out"> Contact Seller</a>                         </li>
                                   <div id="contact-popup" class="mfp-with-anim mfp-hide mfp-dialog">
                                  <h4>Select your Device</h4>
                                  <div class="col-sm-12 col-md-6 device-button">

                                  </div>
                                  <div class="col-sm-12 col-md-6 device-button">

                                  </div>
                                </div>


                                <li class="social-share">
                                    <h4>Share:</h4>
                                    <div class="a2a_kit a2a_kit_size_32 a2a_default_style social-links">
                                      <a class="a2a_button_facebook"></a>
                                      <a class="a2a_button_twitter"></a>
                                      <a class="a2a_button_google_plus"></a>
                                      <a class="a2a_button_pinterest"></a>
                                    </div>
                                  <script type="text/javascript" src="//static.addtoany.com/menu/page.js"></script>
                                </li>
                                </ul>
                            </div>
                            <div class="gap gap-small"></div>
                            <div class="comments">


                    <div class="tabbable">
                        <div class="tab-content">
                            <div class="tab-pane fade in active" id="tab-1">
                                <ul class="comments-list">
                                    <?php for ($i=max(count($comments)-3,0);$i<count($comments);$i++){ $comment = $comments[$i]; ?>
                                    <li>
                                        <!-- REVIEW -->
                                        <article class="comment">
                                            <div class="comment-author">
                                                <img src="/images/uploads/profile/<?php echo $comment["userimage"] ?>" alt="Profile Image" />
                                            </div>
                                            <div class="comment-inner">
                                                <span class="comment-author-name"><?php echo $comment["username"] ?></span>
                                                <p class="comment-content"><?php echo formatText($comment["comment"]); ?></p>
                                            </div>
                                        </article>
                                    </li>
                                <?php } ?>
                                </ul>
                                <a href="#contact-popup" class="view-all-comments popup-text" data-effect="mfp-zoom-out">View All Comments</a>
                            </div>
                        </div>
                      </div>
                    </div>
                    </div>
                        </div>
                    </div>
                    <div class="gap gap-hidden-small"></div>


                    <div class="gap gap-small"></div>
                    
                    
                    
                           <div class="col-xs-12">
            	                              <h3>Other Items from this seller</h3>
                      <div class="gap gap-mini"></div>
                      <div class="row row-wrap">
                      <?php $i = 0;?>
                      <?php foreach ($selleritems as $item){ ?>
                          <div class="col-xs-6 col-md-2">
                              <a href="/items/<?php echo $item["id"] ?>"	><div class="product-thumb">
                                  <header class="product-header">
                                      <img src="/images/uploads/items/<?php $images = explode(",",$item["image"]); echo $images[0]; ?>" alt="Image Alternative text" title="Ana 29" />
                                  </header>
                                  <div class="product-inner">
                                      <h5 class="product-title"><?php echo $item["title"]?></h5>
                                      <p class="product-desciption"><?php echo $item["description"]?></p>
                                      <div class="product-meta">
                                          <ul class="product-price-list">
                                              <li><span class="product-price">$ <?php echo $item["price"]/1000000?></span>
                                              </li>
                                          </ul>

                                      </div>
                                  </div>
                              </div>
                              </a>
                          </div>
                          <?php 
						  if (++$i == 6) break;
						  } ?>
</div>
                    
                    
                    
                    
                </div>

                
                
            </div>
     
            </div>

        </div>
<?php include "footer.php" ?>