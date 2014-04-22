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

		<title>Results</title>
		
		
		<!-- PHP code -->
		
		<?php
			require("config.php");

            // Redirect if not logged in
            if(empty($_SESSION['user'])) {
                header("Location: index.php");
                die("Redirecting to index.php");
            }
            
//            $username               = 'viraj892';
            $username   = $_SESSION['user']['username'];
            
            // Get userid from db
            $query = "
                    SELECT userid
                    FROM user
                    WHERE username = :username
            ";

            $query_params = array(
                    ':username' => $username
            );

            try {
                $stmt   = $db -> prepare($query);
                $return = $stmt -> execute($query_params);
                $rows   = $stmt->fetchAll();
                if(sizeof($rows) == 0) {
                    echo "process.php: Invalid username<br>";   // debug
                    header("Location: index.php");
                    die("Redirecting to index.php");
                }

                $userid = $rows[0]['userid'];

            } catch (PDOException $ex) {
                 die("Something went wrong" . $ex);
            }


            // HTML Default values
            $html_table_start   = "<table class='table table-bordered'>";
            $html_table_end     = "</table>";
            $html_table_content = "<tr><td>No files Uploaded</td></tr>";
            

            // Get data from cete.results table
            $query = "
                    SELECT id, prog_name, complexity, big_o, timestamp
                    FROM results
                    WHERE userid = :userid
            ";

            $query_params = array(
                    ':userid' => $userid
            );

            try {
                $stmt   = $db -> prepare($query);
                $return = $stmt -> execute($query_params);
                $rows   = $stmt->fetchAll();

        //        var_dump($rows);
                if(sizeof($rows) !== 0) {
                    // programs uploaded
                    
                    $html_table_content =   "<tr>
                                                <th style='text-align: center;'>File Name</th>
                                                <th style='text-align: center;'>Complexity</th>
                                                <th style='text-align: center;'>Big O</th>
                                                <th style='text-align: center;'>Timestamp</th>
                                            </tr>
                    ";

                    foreach($rows as $row) {
                        $id         = $row['id'];
                        $prog_name  = $row['prog_name'];   
                        $complexity = $row['complexity'];
                        $big_o      = $row['big_o'];
                        $timestamp  = $row['timestamp'];
                        
                        $complexity = LogarithmicHTMLFormatter($complexity);                    
                        $big_o      = LogarithmicHTMLFormatter($big_o);
                        $big_o      = ExponentHTMLFormatter($big_o);

                        $html_table_content .= "<tr id=\"row-$id\">
                                                    <td>$prog_name</td>
                                                    <td>$complexity</td>
                                                    <td>$big_o</td>
                                                    <td>$timestamp<a class=\"a-delete-row\" href=\"#\"><span class=\"glyphicon glyphicon-remove\"></span></a></td>
                                                </tr>
                        ";
                    }
                }


            } catch (PDOException $ex) {
                        die("Something went wrong" . $ex);
            }


            function LogarithmicHTMLFormatter($str) {
                $pattern = '/(log) (\d+) /i';
                $replace = '\1<sub>\2</sub>';
                return preg_replace($pattern, $replace, $str);
            }

            function ExponentHTMLFormatter($str) {
                $pattern = '/(n)\^(\d+)/i';
                $replace = '\1<sup>\2</sup>';
                return preg_replace($pattern, $replace, $str);
            }
?>
		
		
		

		<!-- Bootstrap core CSS -->
		<link href="css/bootstrap.min.css" rel="stylesheet">

		<!-- Custom styles for this template -->
		<link href="css/userhome.css" rel="stylesheet">
        
        <!-- Custom CSS-->
        <link href="css/results.css" rel="stylesheet">
        
        
        
        
		

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
						<li><a href="userhome.php">Home</a></li>
						<li class="active"><a href="results.php">Results</a></li>
<!--						<li><a href="http://getbootstrap.com/examples/starter-template/#contact">Contact</a></li>-->
<!--						<li><a href="logout.php">Log out</a></li>-->
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
			<h1>Results</h1>
<!--			<p class="lead">User Code Analyser to calculate the complexity and Big O of your java code.<br> Simply upload a <b>.java</b> file and click 'analyse'!</p>-->
			
			
			<!--  mini-upload-form stuff -->
            
              
            <?php
                echo $html_table_start . $html_table_content . $html_table_end;
            ?>
            
			
			
		  </div>

		</div><!-- /.container -->


		<!-- Bootstrap core JavaScript
		================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
        
        <!--    Custom jQuery    -->
        
        <!--    Code to delete row on clicking 'X'    -->
        <script>
            $(document).ready(function(){
                $('.a-delete-row').click(function(event){
                    
                    // Get the TR element corresponding to the 'X' clicked
                    var rowElement = $(this).parent().parent();
                    var id = rowElement.attr('id'); 
//                    console.log("id = " + id);
//                    console.log("row elem = " + rowElement.get(0).tagName);
                    
                    var request = $.ajax({
                                    url: 'process_.php',
                                    type: "POST",
                                    data: {action:'deleteResultRow',id:id}
                                });
                    
                    request.done(function(response, textStatus, jqXHR){
                        // parse the response json
                        var json = JSON.parse(response);
                        console.log("status = " + json['status']);
                        console.log("text = " + json['text']);
                        
                        var status = json['status'];
                        if(status === 'success') {
//                            rowElement.fadeOut('slow');
//                            rowElement.remove();
                            
                            $(rowElement).html('<td colspan="4"><div class="alert alert-success"><strong>Done!</strong>&nbsp;Successfully deleted!</div></td>');
                            
                            setTimeout(function(){
                                rowElement.fadeOut('slow');
                             },1000);
                        }
                        else {
                            var contents = rowElement.html();
                            
//                           $(rowElement).html('<td colspan="4"><div class="alert alert-danger"><a class="close" data-dismiss="alert">x</a><strong>Error!</strong>Could not delete row.</div></td>');
                           $(rowElement).html('<td colspan="4"><div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Error!</strong>&nbsp;Could not delete row.</div></td>');
                            
                            $('button.close').click(function(event){
                                $(rowElement).html(contents).fadeIn('slow');
                            });
                        }
                        
                    });
                    
                    request.fail(function(jqXHR, textStatus, errorThrown){
                        console.error(
                            "The following error occured: " + 
                            textStatus, errorThrown
                        );
                    });
                        
                    request.always(function(){
                        
                    });
                        
                    event.preventDefault();
                    
                });
            });
        </script>

	</body>
</html>