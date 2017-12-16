<!DOCTYPE HTML>
<html>
    <head>
        <title>Jimbo Test Bot</title>
        <link rel="stylesheet" type="text/css" href="css/wine.css">
    </head>
<body>
<div class="header">
  <select name="select" id="select" class="topButton">
  <?php
  $s = $_GET["s"];
  if ($s == NULL or $s == ""){
	  $s = 2;
  }
  for ($i=1;$i<=3;$i++){
  	echo '<option value="' . $i . '"';
	if($i == $s){
		echo " selected";
	}
	echo '>' . $i . '</option>';
  }?>
  </select>
  <button id="viewUsers" class="topButton">Refresh</button>
  <button id="addUser" class="topButton">New Test +</button>
	<!-- this is the loader image, hidden at first -->
	<div id='loaderImage'><img src='images/ajax-loader.gif' /></div>
</div>


<div class="leftArea">
<ul id="apiList"></ul>
</div>
<div class="mainArea">
<!-- this is wher the contents will be shown. -->
<div id='pageContent'></div>
<div id='bckPageContent' style="display: none;"></div>
</div>

<script src='js/jquery-1.9.1.min.js'></script>

<script type='text/javascript'>
$(window).on('hashchange', function() {
	type = window.location.hash;
	type = type.substring(1);
  $('#loaderImage').show();
	setTimeout("$('#pageContent').load('read.php?s=<?php echo $s ?>&type=" + type + "' , function(){ $('#loaderImage').hide(); });",0);
});



$(document).ready(function(){
	
	// VIEW USERS on load of the page
	$('#loaderImage').show();
	showUsers();
	
	// clicking the 'VIEW USERS' button
	$('#viewUsers').click(function(){
		// show a loader img
		$('#loaderImage').show();
		
		showUsers();
	});
	
	// clicking the '+ NEW USER' button
	$('#addUser').click(function(){
		showCreateUserForm();
	});
	
	// clicking the EDIT button
	$(document).on('click', '.editBtn', function(){ 
	
		var user_id = $(this).closest('td').find('.userId').text();
		// console.log(user_id);
		
		// show a loader image
		$('#loaderImage').show();

		// read and show the records after 1 second
		// we use setTimeout just to show the image loading effect when you have a very fast server
		// otherwise, you can just do: $('#pageContent').load('update_form.php?s=<?php echo $s ?>&user_id=" + user_id + "', function(){ $('#loaderImage').hide(); });
		$('#bckPageContent').html( $('#pageContent').html() );
		
		setTimeout("$('#pageContent').load('update_form.php?s=<?php echo $s ?>&test_id=" + user_id + "', function(){ $('#loaderImage').hide(); $('#pageContent .cancel').on('click', function(e) {\
	        e.preventDefault();\
	        $('#pageContent').html( $('#bckPageContent').html() );\
	        $('#bckPageContent').html('');\
	    })});",0);

		$("html, body").animate({ scrollTop: 0 }, "slow");

	});	
	
	
	// when clicking the DELETE button
    $(document).on('click', '.deleteBtn', function(){ 
        if(confirm('Are you sure?')){
		
            // get the id
			var user_id = $(this).closest('td').find('.userId').text();
			
			// trigger the delete file
			$.post("delete.php?s=<?php echo $s ?>", { id: user_id })
				.done(function(data) {
					// you can see your console to verify if record was deleted
					//console.log(data);
					
					$('#loaderImage').show();
					
					// reload the list
					showUsers();
					
				});

        }
    });
	// when clicking the DELETE button
    $(document).on('click', '.acceptBtn', function(){ 
		
		// get the id
		var user_id = $(this).closest('td').find('.userId').text();
		var newReturn = $(this).closest('td').find('.newReturn').text();
		
		// trigger the delete file
		$.post("accept.php?s=<?php echo $s ?>", { id: user_id, returnval: newReturn })
			.done(function(data) {
								//alert(data);
				$('#loaderImage').show();
				
				// reload the list
				showUsers();
				
			});
    });
	$(document).on('click', '.apilista', function(){
		$('li a').css('background-color', '#fff');
		$(this).css('background-color', '#2479B6');
		$("html, body").animate({ scrollTop: 0 }, "slow");
	showType($(this).find('span').text());
});	

	
    // CREATE FORM IS SUBMITTED
     $(document).on('submit', '#addUserForm', function(e) {

		// show a loader img
		$('#loaderImage').show();
		
		// post the data from the form
		/*$.post("create.php", $(this).serialize())
			.done(function(data) {
				// 'data' is the text returned, you can do any conditions based on that
				showUsers();
			});*/
		$.ajax( {
		  url: 'create.php?s=<?php echo $s ?>',
		  type: 'POST',
		  data: new FormData( this ),
		  processData: false,
		  contentType: false,
		  success: function(data){
			  showUsers();
			}
		} );
		e.preventDefault();
		return false;
    });
	
    // UPDATE FORM IS SUBMITTED
     $(document).on('submit', '#updateUserForm', function(e) {

		// show a loader img
		$('#loaderImage').show();
		
		// post the data from the form
		/*$.post("update.php", $(this).serialize())
			.done(function(data) {
				// 'data' is the text returned, you can do any conditions based on that
				showUsers();
			});*/
	 			
        $.ajax( {
		  url: 'update.php?s=<?php echo $s ?>',
		  type: 'POST',
		  data: new FormData( this ),
		  processData: false,
		  contentType: false,
		  success: function(data){
			  showUsers();
			}
		} );
		e.preventDefault();
		return false;
    });
	
});

// READ USERS
function showUsers(){
	type = window.location.hash;
	type = type.substring(1);
	// read and show the records after at least a second
	// we use setTimeout just to show the image loading effect when you have a very fast server
	// otherwise, you can just do: $('#pageContent').load('read.php', function(){ $('#loaderImage').hide(); });
	// THIS also hides the loader image
	setTimeout("$('#pageContent').load('read.php?s=<?php echo $s ?>&type=" + type + "', function(){ $('#loaderImage').hide(); });", 0);
	setTimeout("$('#apiList').load('api_list.php?s=<?php echo $s ?>', function(){ });", 0);
}

// CREATE USER FORM
function showCreateUserForm(){
	// show a loader image
	$('#loaderImage').show();
	
	// read and show the records after 1 second
	// we use setTimeout just to show the image loading effect when you have a very fast server
	// otherwise, you can just do: $('#pageContent').load('read.php');
	type = window.location.hash;
	type = type.substring(1);
	if (type == "All" || type == "Changed"){
		type = "";
	}
	setTimeout("$('#pageContent').load('create_form.php?s=<?php echo $s ?>&type=" + type + "', function(){ $('#loaderImage').hide(); });",0);
}
function showType(type){
	window.location.hash = type;
}
$(window).hashchange();


</script>

</body>
</html>