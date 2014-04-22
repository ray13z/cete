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
	
	<title>Register</title>
		
	<!-- PHP Code -->


	<?php
		require("config.php");
		require("PasswordHash.php");
		
		if(!empty($_POST)) {
            
            // Should not fail these, but just in case
			if(empty($_POST['username'])) {
				die("Please enter a username<br>");
			}
			if(empty($_POST['password'])) {
				die("Please enter a password<br>");
			}
			if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				die("Invalid email address<br>");
			}
			
			// Add to database
			$query = "
					INSERT INTO user (
						username,
						email,
						hash
					) VALUES (
						:username,
						:email,
						:hash
					)
			";
			
			// Security measures
			$hasher = new PasswordHash(8, false);
			$hash = $hasher -> HashPassword($_POST['password']);
			
			$query_params = array(
				':username' => $_POST['username'],
				':email' 	=> $_POST['email'],
				':hash'		=> $hash
			);
			
			try {
				$stmt 	= $db -> prepare($query);
				$result = $stmt -> execute($query_params);
			} catch(PDOException $ex) {
				die("Failed to run query: ".$ex -> getMessage());
			}
			header("Location: index.php");
			die("Redirecting to index.php");
		}
    
	?>
		
		
	</head>
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/signin.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  <script>window["_GOOG_TRANS_EXT_VER"] = "1";</script></head>

  <body style="">

    <div class="container">

        <form class="form-signin" role="form" action="register.php" method="post">
            <h2 class="form-signin-heading">Please Register</h2>
            
            <div class="form-group has-feedback">
                <input name="username" id="username" type="text" class="form-control" placeholder="Enter username" required="" autofocus="">
                <label class="control-label" for="username"></label>
                <span class="glyphicon form-control-feedback" for="username"></span>
            </div>
            
            <div class="form-group has-feedback">
                <input name="email" id="email" type="email" class="form-control" placeholder="Email address" required="">
                <label class="control-label" for="email"></label>
                <span class="glyphicon form-control-feedback" for="email"></span>
            </div>
            
            <div class="form-group has-feedback">
                <input name="password" id="password" type="password" class="form-control" placeholder="Password" required="">
                <label class="control-label" for="password"></label>
                <span class="glyphicon form-control-feedback" for="password"></span>
            </div>
            
            <button class="btn btn-lg btn-primary btn-block" type="submit">Register</button>
            
        </form>

    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
      
      <!--  jQuery js    -->
      <script src="js/jquery.min.js"></script>
      
      <script>
            $(document).ready(function(){
                
                $('div.container').find('label').hide();
                $('div.container').find('span').hide();
                $('form.form-signin').find('button').prop('disabled', true);
                
                var usernameOk  = false;
                var emailOk     = false;
                var passwordOk  = false;
                
                $('input').keyup(function(){                    
                    var div     = $(this).parent();
                    var id      = $(this).attr('id');
                    
                    var text    = $(this).val();
                        
                    if(id === 'username') {
                        RemoveAllWarningsFromDiv(div);
                        usernameOk = false;
                        
                        if(text.length <= 3) {
                            AddWarningToDiv(div, 'username too short');
                        }
                        else {                            
                            // AJAX drops the bass here
                            var request     = $.ajax({
                                                url: 'process_.php',
                                                type: "POST",
                                                async: false,
                                                data: {action:'checkUsername',username:text}
                                            });
                            
                            request.done(function(response, textStatus, jqXHR){
                                var json = JSON.parse(response);
                                
                                if(json['status'] === 'success') {
                                    var avail = json['available'];
                                    var desc = json['description'];
                                    
                                    if(avail === 'true') {
                                        AddSuccessToDiv(div, desc);
                                        usernameOk = true;
                                    } else {
                                        AddErrorToDiv(div, desc);
                                    }
                                } else {
                                    AddErrorToDiv(div, 'database error');
                                }
                            });
                            
                            request.fail(function(jqXHR, textStatus, errorThrown){
                                console.error(
                                    "The following error occured: " + 
                                    textStatus, errorThrown
                                );
                            });
                        }
                        
                    } else if(id === 'email') {
                        RemoveAllWarningsFromDiv(div);
                        emailOk = false;
                        
                        if(!IsEmail(text)) {
                            AddWarningToDiv(div, 'please enter a valid email address');
                        } else {
                            // AJAX drops the bass here
                            var request     = $.ajax({
                                                url: 'process_.php',
                                                type: "POST",
                                                async: false,
                                                data: {action:'checkEmail',email:text}
                                            });
                            
                            request.done(function(response, textStatus, jqXHR){
                                var json = JSON.parse(response);
                                
                                if(json['status'] === 'success') {
                                    var avail = json['available'];
                                    var desc = json['description'];
                                    
                                    if(avail === 'true') {
                                        AddSuccessToDiv(div, desc);
                                        emailOk = true;
                                    } else {
                                        AddErrorToDiv(div, desc);
                                    }
                                } else {
                                    AddErrorToDiv(div, 'database error');
                                }
                            });
                            
                            request.fail(function(jqXHR, textStatus, errorThrown){
                                console.error(
                                    "The following error occured: " + 
                                    textStatus, errorThrown
                                );
                            });
                        }
                        
                    } else if(id === 'password') {
                        RemoveAllWarningsFromDiv(div);
                        passwordOk = false;
                        
                        if(text.length <= 6) {
                            AddWarningToDiv(div, 'password too short');
                        }
                        else {
                            AddSuccessToDiv(div, '');
                            passwordOk = true;
                        }
                        
                    } else {
                        console.log("error: incorrect 'input' id");   
                    }
                    
                    if(usernameOk === true && emailOk === true && passwordOk === true){
                        // Enable 'submit' button only if all inputs are valid
                        $('form.form-signin').find('button').prop('disabled', false);
                    }
                    else {
                        $('form.form-signin').find('button').prop('disabled', true);   
                    }
                    
                });
                
            });
          
          
            function IsEmail(email) {
                var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                return regex.test(email);
            }
          
            function AddWarningToDiv(div, msg) {
                div.addClass('has-warning');
                div.find('label').text(msg).show();
                div.find('span').addClass('glyphicon-warning-sign').show();   
            }
          
            function AddErrorToDiv(div, msg) {
                div.addClass('has-error');
                div.find('label').text(msg).show();
                div.find('span').addClass('glyphicon-remove').show(); 
            }
          
            function AddSuccessToDiv(div, msg) {
                div.addClass('has-success');
                div.find('label').text(msg).show();
                div.find('span').addClass('glyphicon-ok').show(); 
            }
          
            function RemoveAllWarningsFromDiv(div) {
                div.find('label').text('').hide();
                
                div.removeClass('has-warning');   
                div.removeClass('has-error');   
                div.removeClass('has-success');   
                
                div.find('span').removeClass('glyphicon-warning-sign').hide();
                div.find('span').removeClass('glyphicon-remove').hide();   
                div.find('span').removeClass('glyphicon-ok').hide();   
            }
          
      </script>
  

    </body>
</html>