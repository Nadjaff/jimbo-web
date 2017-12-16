<?php
function formatText($str){
	return preg_replace('/(^|\s)@(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://jimbo.co/users?q=\2">@\2</a>', preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://jimbo.co/search?q=%23\2">#\2</a>',$str));
}?>


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
                      <a href="http://jimbo.co" class="logo"><img src="/templates/img/logo.png" alt="Jimbo Logo" /></a>
                    </div>
                    <div class="col-xs-12 col-sm-7">
                      <div class="search-area-division search-area-division-input">
                          <input class="form-control" type="text" placeholder="Search..." />
                          <img src="/templates/img/search.svg" alt="Search" class="search-icon" />
                      </div>
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



        