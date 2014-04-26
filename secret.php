<?php
	require("config.php");
	if(empty($_SESSION['user'])) {
		header("Location: index.php");
		die("Redirecting to index.php");
	}
	echo "Hello " . $_SESSION['user']['username'];
	
	
	// Add remaining markup after this.

    header("Location: userhome.php");
    die("Redirecting to userhome.php");
?>


<li><a href="logout.php">Log Out</a></li>