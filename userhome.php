<!DOCTYPE html>
<!-- saved from url=(0050)http://getbootstrap.com/examples/starter-template/ -->
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		<link rel="shortcut icon" href="assets/images/icon.png">

		<title>Home</title>
		
		
		<!-- PHP code -->
		
		<?php
			require("config.php");
    
            // Redirect if not logged in
            if(empty($_SESSION['user'])) {
                header("Location: index.php");
                die("Redirecting to index.php");
            }

            $username = $_SESSION['user']['username'];
		?>

		<!-- Bootstrap core CSS -->
		<link href="css/bootstrap.min.css" rel="stylesheet">

		<!-- Custom styles for this template -->
		<link href="css/userhome.css" rel="stylesheet">
        
        
        <!-- mini-upload-form css -->
<!--        <link href="assets/css/style.css" rel="stylesheet" />-->
        <link href="mini-upload-form/assets/css/style.css" rel="stylesheet" />
        
        
		

		<!-- Just for debugging purposes. Don't actually copy this line! -->
		<!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>

	<body style="">

		<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="index.php">CETE</a>
				</div>
                
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li class="active"><a href="userhome.php">Home</a></li>
						<li><a href="results.php">Results</a></li>
					</ul>
                    
                    <!--    Wrench stuff    -->
                    <ul class="nav navbar-nav navbar-right">
<!--                        <li><a href="#">Link</a></li>-->
                        <li class="dropdown">
<!--                              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>-->
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-cog cog"></span>&nbsp;<b class="caret"></b></a>   
                              <ul class="dropdown-menu">
                                    <li><a>Hello <b><?php echo $username ?></b></a></li>
                                    <li class="divider"></li>
                                    <li><a href="logout.php">Log out</a></li>
                              </ul>
                        </li>
                    </ul>
                    
				</div><!--/.nav-collapse -->
			</div>
		</div>

		<div class="container">

		  <div class="starter-template">
			<h1>Code Analyser</h1>
			<p class="lead">Use to calculate the complexity and Big O of your java code.<br> Simply upload a <b>.java</b> file and click 'Check Result'!</p>
			
			
			<!--  mini-upload-form stuff -->
              
            <form id="upload" method="post" action="mini-upload-form/upload.php" enctype="multipart/form-data">
                <div id="drop">
                    
                    
                    <a>Browse</a>
                    <input type="file" name="upl" multiple />
                </div>

                <ul>
                    <!-- The file uploads will be shown here -->
                </ul>
                
                <div id="check-result-button">
                
                </div>

            </form>  
            
            
              
              
            <!-- Modal -->
            <div class="modal fade" id="listErrorsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title" id="errorListModalLabel">Modal title</h4>
                        </div>
                        <div class="modal-body">
                            
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button id="attempt-to-fix" type="button" class="btn btn-primary">Attempt to fix</button>
                        </div>
                    </div>
                </div>
            </div>
			
			
		  </div>

		</div><!-- /.container -->


		<!-- Bootstrap core JavaScript
		================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
        
        
        <!-- mini-upload-form js    -->
        <script src="mini-upload-form/assets/js/jquery.knob.js"></script>
        
        <!-- jQuery File Upload Dependencies -->
        <script src="mini-upload-form/assets/js/jquery.ui.widget.js"></script>
        <script src="mini-upload-form/assets/js/jquery.iframe-transport.js"></script>
        <script src="mini-upload-form/assets/js/jquery.fileupload.js"></script>
        
       <!-- Our main JS file -->
        <script src="mini-upload-form/assets/js/script.js"></script>
  

	</body>
</html>