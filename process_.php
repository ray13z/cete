<?php
    require('config.php');


    if(isset($_POST['action']) && !empty($_POST['action'])) {
        $action = $_POST['action'];
        
        switch($action) {
            
            
            case 'validateLogin':
                require("PasswordHash.php");
                $hasher = new PasswordHash(8, false);
			
                $submitted_username = '';
                $result = '{"status":"error"}';
            
                if(isset($_POST['username'], $_POST['password'])) {
                    $query = "
                        SELECT 
                            userid,
                            username,
                            email,
                            hash
                        FROM user
                        WHERE
                            username = :username
                    ";

                    $query_params = array(
                        ':username'	=> $_POST['username']
                    );

                    try {
                        $stmt 	= $db 	-> prepare($query);
                        $result	= $stmt -> execute($query_params);
                    } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex.getMessage());
                    }

                    $login_ok 	= false;
                    $row 		= $stmt -> fetch();

                    if($row) {
                        // username matches
                        $check_password = $hasher -> CheckPassword($_POST['password'], $row['hash']);
                        if($check_password) {
                            // password matches
                            $login_ok = true;
                        }
                        else {
                            // password incorrect
                            echo '{"status":"error","description":"incorrect password"}';
                            exit;
                        }
                    }
                    else {
                        // username does not match
                        echo '{"status":"error","description":"invalid username"}';   
                        exit;
                    }

                    if($login_ok) {			
                        unset($row['password']);
                        $_SESSION['user'] = $row;
//                        header("Location: secret.php");
//                        die("Redirecting to: secret.php");
                        echo '{"status":"success"}';
                    }
                    
                    return $result;
                }
                
            
                break;
            /**
             *  End of Case 'checkUsername'
             */ 
            
            case 'checkUsername':
                $result = '{"status":"error","description":"process_.php: something went wrong"}';
                
                if(isset($_POST['username'])) {
                    // Check if username already taken
                    $query = "
                            SELECT 1
                            FROM user
                            WHERE username = :username
                    ";

                    $query_params = array(
                            ':username' => $_POST['username']
                    );

                    try {
                        $stmt = $db -> prepare($query);
                        $result = $stmt -> execute($query_params);

                        $rows = $stmt -> fetchAll();
                        if(sizeof($rows) > 0)
                            $result = '{"status":"success","available":"false","description":"username not available"}';
                        else
                            $result = '{"status":"success","available":"true","description":"username available"}';
                    } catch (PDOException $ex) {
                        die('{"status":"error","description":"process_.php: something went wrong"}');
                    }

                    
                }
                else {
                    $result = '{"status":"error","description":"process_.php: username not set"}'; 
                }
                echo $result;
                break;
            /**
             *  End of Case 'checkUsername'
             */   
            
            
            case 'checkEmail':
                $result = '{"status":"error","description":"process_.php: something went wrong"}';
                
                if(isset($_POST['email'])) {
                    // Check if username already taken
                    $query = "
                            SELECT 1
                            FROM user
                            WHERE email = :email
                    ";

                    $query_params = array(
                            ':email' => $_POST['email']
                    );

                    try {
                        $stmt = $db -> prepare($query);
                        $result = $stmt -> execute($query_params);

                        $rows = $stmt -> fetchAll();
                        if(sizeof($rows) > 0)
                            $result = '{"status":"success","available":"false","description":"email id already registered"}';
                        else
                            $result = '{"status":"success","available":"true","description":"email id not yet registered"}';
                    } catch (PDOException $ex) {
                        die('{"status":"error","description":"process_.php: something went wrong"}');
                    }

                    
                }
                else {
                    $result = '{"status":"error","description":"process_.php: email not set"}'; 
                }
                echo $result;
                break;
            /**
             *  End of Case 'checkUsername'
             */   
            
            
            case 'deleteResultRow' : 
                if(isset($_POST['id'])) {
                    $array = explode("-", $_POST['id']);
                    $id = trim($array[1]);
//                    echo '{"status":"success","text":"id = ' . $id . '"}';
                    
                    $query = "
                                DELETE
                                FROM results
                                WHERE
                                    id = :id
                            ";

                    $query_params = array(
                        ':id'	=> $id
                    );

                    try {
                        $stmt 	= $db 	-> prepare($query);
                        $result	= $stmt -> execute($query_params);
                    } catch(PDOException $ex) {
//                        die("Failed to run query: " . $ex.getMessage());
                        die('{"status":"error","description":"Failed to run query: ' . $ex.getMessage() . '"}');
                    }
                    
                    $row_count = $stmt -> rowCount();
                    echo '{"status":"success","text":"no of rows deleted = ' . $row_count . '"}';
                }
                else 
                    echo '{"status":"error","description":"process_.php: id not set"}';
                break;
                /**
                 *  End of Case 'deleteResultRow'
                 */
            
            
            case 'attemptToFix':
                if(isset($_POST['filename'])) {
                    
                    include 'process.php';
                    
                    // default response for failure
                    $response = '{"status":"error","description":"process.php: Something went wrong"}';

                    //    $username               = 'viraj892';
                    $username   = $_SESSION['user']['username'];
                    $filename = trim($_POST['filename']);
                    $path_to_corrector      = 'java' . DIRECTORY_SEPARATOR;
                    $path_to_code_analyser  = 'java' . DIRECTORY_SEPARATOR;
                    $upload_dir             = 'mini-upload-form' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $username;
                    
                    if(is_dir($upload_dir)) {
                    // upload_dir contains files   
                        
                        if(file_exists($upload_dir . DIRECTORY_SEPARATOR . $filename)) {
                        
                            // Getting OS environment
                            if(substr(PHP_OS, 0, 3) == 'WIN') {
                                $java_path = "\"" . exec('where java') . "\"";
                                $check_dir_listing = "dir $upload_dir /b";
                            }
                            else {
                                $java_path = exec('which java');
                                $check_dir_listing = "ls $upload_dir";
                            }
                            
                            // Run Corrector
                            $cmd = "$java_path -cp $path_to_code_analyser"."CodeAnalyzer.jar master.Corrector $upload_dir" . DIRECTORY_SEPARATOR . "$filename 2>&1";
                            // debug
//                            echo "$java_path -cp $path_to_code_analyser"."CodeAnalyzer.jar master.Corrector $upload_dir" . DIRECTORY_SEPARATOR . "$filename\n";
                            
//                            echo $upload_dir . DIRECTORY_SEPARATOR . 'Correct' . $filename . "\n";
                            
                            exec($cmd, $result);
                            
//                            var_dump($result);
                            
                            // delete the original uploaded file
                            // corrected file has name as 'CorrectFileName.java'
//                            deleteFile($upload_dir, $filename);
                            
                            if(file_exists($upload_dir . DIRECTORY_SEPARATOR . 'Correct' . $filename)) {
                                // File was corrected successfully
                                if(rename($upload_dir . DIRECTORY_SEPARATOR . 'Correct' . $filename, $upload_dir . DIRECTORY_SEPARATOR . $filename)) {
                                    // renamed to original filename
                                    
                                    
                                    // unsetting response variable
                                    unset($response);

                                    // Run Code Analyzer
                                    $response = runCodeAnalyzerOnFile($filename, $upload_dir);
                                    echo $response;

                                    exit;
                                    
                                }
                            }
                            else {
                                // File was not properly corrected
                                echo '{"status":"error","description":"process_.php: File could not be corrected"}';
                            }
                        
                        }
                        
                    }
                    else {
                        // No files uploaded
                        echo '{"status":"error","description":"process_.php: upload directory not found"}';
                    }
                    
                    echo $response;
                    
                }
                else 
                    echo '{"status":"error","description":"process_.php: filename not set"}';
                break;
                /**
                  *     End of case 'attemptToFix'
                  */
                
            
            default:
                echo '{"status":"error","description":"process_.php: No case matched"}';
            
            
        }
    }
?>