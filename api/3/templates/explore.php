<!DOCTYPE HTML>
<html>

<head>
    <title>Jimbo - Explore</title>
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
        <?php foreach ($items as $item){ ?>
            <div class="explore-item">
            	<a href="/items/<?php echo $item["id"] ?>"><div class="explore-item-image" style="background: url('/images/uploads/items/<?php $images = explode(",",$item["image"]); $img = explode(".", $images[0]); echo $img[0] . "_288_288." . $img[1]; ?>') no-repeat; background-size: 100%;">
                	<div class="explore-item-price">
                    	<span>$ <?php echo (float)($item["price"])/1000000?></span>
                    </div>
                </div></a>
                <div class="explore-item-info">
                	<div class="profile">
                    	<div class="profile-image product-info">
                    	<img class="profile-pic" src="/images/uploads/profile/<?php $pic = explode(".",$item["userimage"]); echo $pic[0] . "_38_38." . $pic[1]?>" alt="Profile Img" style="width:38px;">
                        </div>
                        <div class="profile-info">
                                 <span class="username"><?php echo $item["username"]?></span><br>
                                 <span class="location"><img src="/templates/img/location.png" alt="Location" style="width:13px;"> <?php echo $item["locality"]?></span>
                         </div>
                         <div class="explore-title">
                         	<span class="title"><a href="/items/<?php echo $item["id"] ?>"><?php echo $item["title"]?></a></span>
                         </div>
                         <?php if ($item["num_likes"] != 0 || $item["num_comments"] != 0){ ?>
                         <div class="explore-social">
                         <span><?php if ($item["num_likes"] != 0){ echo $item["num_likes"]?></span> <span>Favourites<?php } ?><?php if ($item["num_likes"] != 0 && $item["num_comments"] != 0){ ?></span> - <span><?php } ?><?php if ($item["num_comments"] != 0){ echo $item["num_comments"]?></span> <span>Comments<?php } ?></span>
                         </div>
                         <?php } ?>
                               
                    </div>
                </div>
            </div>
            <?php } ?>
            
            
            
            <div class="explore-item">
            	<div class="explore-item-image" style="background: url('http://jimbo.co/images/uploads/items/u55d2f316b8cc2.jpg') no-repeat; background-size: 100%;">
                	<div class="explore-item-price">
                    	<span>$25</span>
                    </div>
                </div>
                <div class="explore-item-info">
                	<div class="profile">
                    	<div class="profile-image product-info">
                    	<img class="profile-pic" src="/templates/img/profile-pic-small.png" alt="Profile Img" style="width:38px;">
                        </div>
                        <div class="profile-info">
                                 <span class="username">ramikabar</span><br>
                                 <span class="location"><img src="/templates/img/location.png" alt="Location" style="width:13px;"> Bossley Park</span>
                         </div>
                         <div class="explore-title">
                         	<span class="title">iPod Nano 8gb #ipod #apple 
#music</span>
                         </div>
                         <div class="explore-social">
                         <span>5</span> <span>Favourites</span> - <span>23</span> <span>Comments</span>
                         </div>
                               
                    </div>
                </div>
            </div>
            
            
          </div>
        </div>
	</div>
    
<?php include "footer.php" ?>