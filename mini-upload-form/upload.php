<?php

require("../config.php");
include '../process.php';

// A list of permitted file extensions
//$allowed = array('png', 'jpg', 'gif','zip');
$allowed = array('java');


// Redirect if not logged in
if(empty($_SESSION['user'])) {
    echo '{"status":"error","description":"upload.php: $_SESSION not set"}';
    header("Location: ../index.php");
    die("Redirecting to index.php");
}


$username = $_SESSION['user']['username'];
// echo "upload.php called! Hello " . $username . "<br>";


if(isset($_FILES['upl']) && $_FILES['upl']['error'] == 0){
    
    $directory = 'uploads/'.$username;
    
    if(!is_dir($directory)) {
        mkdir($directory) or die('{"status":"error","description":"upload.php: Could not create directory"}');
//        mkdir($directory) or die('{"status":"error","descritpion":"could not create directory"}');
//        echo "created directoty!";    
    }
    

    $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);

    if(!in_array(strtolower($extension), $allowed)){
        echo '{"status":"error", "description":"File format not supported"}';
        exit;
    }



    if(move_uploaded_file($_FILES['upl']['tmp_name'], $directory.'/'.$_FILES['upl']['name'])) {
//        echo '{"file moved"}';
        // Run Checker
        $response = runChecker();
//        echo $response;
//        exit;
        
        $json = json_decode($response, true);
        
        if(($json_error = json_last_error()) > 0) {
            // JSON parsing error
            
            if($json_error === 4) {
                // JSON Syntax error
                
                if(strpos($response, "Errors") !== false) {
                    // Compilation error
                    echo '{"status":"error","description":"Program must be well formed"}';
                    
                    // Detele this file
                    $filename = pathinfo($_FILES['upl']['name'], PATHINFO_FILENAME);
                    deleteFile($directory, $filename);
                    
                    exit;
                }
                
                echo '{"status":"error","description":"JSON syntax error"}';
                exit;
            }
            
            echo '{"status":"error","description":"JSON parsing error: ' . $json_error . '"}';
            exit;
        }
        else {
            // JSON parsed successfully
            
            if($json['status'] === "success") {
                if($json['acceptance'] === "true") {
                    // Submitted program validated by checker
                    
                    // unsetting response variable
                    unset($response);

                    // Run Code Analyzer
                    $response = runCodeAnalyzer();
                    echo $response;
                    
                    exit;
                }
                else {
                    // @TODO
                    // To process when checker fails
                    // two scenarios kinds of errors:
                    // 2. non - correctable errors
                    // 3. correctable errors

                    $errorString = $json['errors'];
                    if(isset($errorString)) {
                        // Possible scenarios: 2 or 3
                        
                        
                        // SET array of correctable errors
                        // Correctable errors: Any errors which can be resolved by the 'Corrector'
                        $correctableErrors = array('901', '902');
                        
                        $errors = explode(",", $errorString);
                        
                        $correctable = true;
                        
                        foreach($errors as $error) {
                            $terms  = explode(":", $error);
                            $lineNo = trim($terms[0]);
                            $errNo  = trim($terms[1]);
                            
                            if(!in_array($errNo, $correctableErrors)) {
                                $correctable = false;
                                break;
                            }
                            
                        }
                        
                        $errorString = str_replace(" ", '', $errorString);
                        
                        echo '{"status":"error","description":"Invalid program format","correctable":"' . $correctable . '","errors":"' . $errorString . '"}';
                        exit;
                        
                    }
                    
                    // @todo handle later
                    echo '{"status":"error","description":"upload.php: json[\'errors\'] not set"}';
                }
            }
            else {
                // Something went wrong with Checker
                // Return response json as is to the UI
    //            echo $response;
                echo '{"status":"success","json[status] is not success"}';
                exit;
            }
            
        }
        
        
        // debug
//        // Run code analyzer
//        $response = runCodeAnalyzer();
//        echo $response;
//        exit;
    }
    
}


echo '{"status":"error", "desctiption":"Something went wrong while uploading file"}';
//echo '{"status":"error"}';
exit;