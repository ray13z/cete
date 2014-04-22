<!DOCTYPE html>

<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		<link rel="shortcut icon" href="assets/images/icon.png">

		<title>Login</title>
		
		<!-- PHP Code -->
		
		<?php
			require("config.php");
//			require("PasswordHash.php");

            if(!empty($_SESSION['user'])) {
                echo "Already logged in";
                header("Location: userhome.php");
                die("Redirecting to userhome.php");
            }
			
//			$hasher = new PasswordHash(8, false);
//			
//			$submitted_username = '';
//			if(!empty($_POST)) {
//				$query = "
//					SELECT 
//						userid,
//						username,
//						email,
//						hash
//					FROM user
//					WHERE
//						username = :username
//				";
//				
//				$query_params = array(
//					':username'	=> $_POST['username']
//				);
//				
//				try {
//					$stmt 	= $db 	-> prepare($query);
//					$result	= $stmt -> execute($query_params);
//				} catch(PDOException $ex) {
//					die("Failed to run query: " . $ex.getMessage());
//				}
//				
//				$login_ok 	= false;
//				$row 		= $stmt -> fetch();
//				
//				if($row) {
//					$check_password = $hasher -> CheckPassword($_POST['password'], $row['hash']);
//					if($check_password) {
//						$login_ok = true;
//					}
//				}
//				
//				if($login_ok) {			
//					unset($row['password']);
//					$_SESSION['user'] = $row;
//					header("Location: secret.php");
//					die("Redirecting to: secret.php");
//				}
//				else {
//					print("Login Failed.");
//					$submitted_username = htmlentities($_POST['username'], ENT_QUOTES, 'UTF-8');
//				}
//			}
		?>
		
		
		

		<!-- Bootstrap core CSS -->
		<link href="css/bootstrap.min.css" rel="stylesheet">

		<!-- Custom styles for this template -->
		<link href="css/signin.css" rel="stylesheet">
        
        <!-- Bootstrap validator css -->
        <link href="css/bootstrapValidator.min.css" rel="stylesheet">

		<!-- Just for debugging purposes. Don't actually copy this line! -->
		<!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
		<script>window["_GOOG_TRANS_EXT_VER"] = "1";</script>
	</head>

	<body style="">

    <div class="container">

		<form class="form-signin" role="form" action="index.php" method="post">
			<h2 class="form-signin-heading">Code Evaluation <div class="italic">and</div> Testing Engine</h2><br/>
            
            <div id="alert-message"></div>
            
			<input id="username" name="username" type="text" class="form-control" placeholder="Username" required="" autofocus="">
            
			<input id="password" name="password" type="password" class="form-control" placeholder="Password" required="">
            
            <button id="submit" class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
            
			<div class="row" style="margin-bottom: 10px; margin-left: 7.5%;">
				<div class="col-sm-6">
					<label class="checkbox">
						<input type="checkbox" value="remember-me"> Remember me
					</label>
				</div>
				<div class="col-sm-6" style="margin-top: 10px;">
					<a href="register.php">New user?</a>
				</div>
			</div>
            
			
            
		</form>

    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
        
    <!--    jQuery js    -->
    <script src="js/jquery.min.js"></script>
        
    <!--    boostrap js    -->
    <script src="js/bootstrap.min.js"></script>
        
    <!--    Custom jQuery   -->
    <script>
        
        $(document).ready(function(){
            var alertDivErrorHtml =  '<div class="alert alert-danger alert-dismissable">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + 
                                '<strong>Login failed!</strong>&nbsp;Username or password incorrect.' + 
                                '</div>';
            
            var alertDivWarningHtml =  '<div class="alert alert-warning alert-dismissable">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + 
                                '<strong>Warning!</strong>&nbsp;Username and password fields cannot be empty.' + 
                                '</div>';
            
            $('#submit').click(function(event){
                
                var uname = $('#username').val();
                var pwd = $('#password').val();
                
                if(uname.length === 0 || pwd.length === 0) {
                    $('#alert-message').html(alertDivWarningHtml);
                    return;
                }
                
                var request =   $.ajax({
                                    url: 'process_.php',
                                    type: "POST",
                                    data: {action:'validateLogin',username:uname,password:pwd}
                                });
                                     
                request.done(function(response, textStatus, jqXHR){
//                    console.log("response = " + response); 
                    var json = JSON.parse(response);
                    
                    var status = json['status'];
                    if(status === 'success') {
                        $('#alert-message').html('');
                        
                        // redirect to secret.php
                        window.location.replace('secret.php');
                    } else {
                        $('#alert-message').html(alertDivErrorHtml);   
                    }
                });
                
                request.fail(function(jqXHR, textStatus, errorThrown){
                    console.error(
                        "The following error occured: " + 
                        textStatus, errorThrown
                    );
                
                });
                
                event.preventDefault();
            
            });
            
        });
        
    </script>
  

	</body>
</html>