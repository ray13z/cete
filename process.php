<?php
    require("config.php");

// @TODO Uncomment lower lines

    // Redirect if not logged in
    if(empty($_SESSION['user'])) {
        header("Location: index.php");
        die("Redirecting to index.php");
    }

    /***
     *  Run the Code Analyzer on java files stored uploaded by user
     *  Will be called from 'mini-upload-form/upload.php'
     *  @param null No parameters
     *  @return JSON The AJAX response
     *
     **/
    function runCodeAnalyzer() {
        // default response for failure
        require("../config.php");
        $response = '{"status":"error","description":"process.php: Something went wrong"}';
        
        //    $username               = 'viraj892';
        $username   = $_SESSION['user']['username'];
        $path_to_code_analyser  = '..' . DIRECTORY_SEPARATOR . 'java' . DIRECTORY_SEPARATOR;

        $upload_dir             = 'uploads' . DIRECTORY_SEPARATOR . $username;


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
            // Invalid username in cookie
                header("Location: ../index.php");
                die("Redirecting to index.php");
            }

            $userid = $rows[0]['userid'];

        } catch (PDOException $ex) {
            die("Something went wrong" . $ex);
        }


        if(is_dir($upload_dir)) {
        // user has uploaded programs
            
            // Checking java installation

            // Getting OS environment
            if(substr(PHP_OS, 0, 3) == 'WIN') {
                $java_path = "\"" . exec('where java') . "\"";
                $check_dir_listing = "dir $upload_dir /b";
            }
            else {
                $java_path = exec('which java');
                $check_dir_listing = "ls $upload_dir";
            }


            if(strlen($java_path) !== 0) {
            // Java found on system
                
                // Setting timezone to India
                date_default_timezone_set('Asia/Calcutta');

                exec($check_dir_listing, $files);

                foreach($files as $file) {

                    if(strtolower(substr($file, -4)) == 'java') {

                        $cmd = "$java_path -cp $path_to_code_analyser"."CodeAnalyzer.jar master.CodeAnalyzer $upload_dir" . DIRECTORY_SEPARATOR . "$file";
                            
                        exec($cmd, $result);
                        
//                        //debug 
//                        echo "$cmd\n";
//                        echo '{"result":"$result"}';
//                        var_dump($result);
//                        exit;

                        $prog_name  = $file;
                        $complexity = $result[0];
                        $big_o      = $result[1];
                        $timestamp  = date("D, F d, Y H:i:s", time());

                        // Add to database
                        $query = "
                                INSERT INTO results (
                                    userid,
                                    prog_name,
                                    complexity,
                                    big_o,
                                    timestamp
                                ) VALUES (
                                    :userid,
                                    :prog_name,
                                    :complexity,
                                    :big_o,
                                    :timestamp
                                )
                        ";

                        $query_params = array(
                            ':userid'       => $userid,
                            ':prog_name'    => $prog_name,
                            ':complexity' 	=> $complexity,
                            ':big_o'		=> $big_o,
                            ':timestamp'    => $timestamp
                        );

                        try {
                            $stmt 	= $db -> prepare($query);
                            $return = $stmt -> execute($query_params);
                        } catch(PDOException $ex) {
                            $response = '{"status":"error","description":"process.php: Database query error"}';
                            die("Failed to run query: ".$ex -> getMessage());
                        }

                        // exec() will append to existing array, so there is need to unset it each time
                        unset($result);
                    }
                }

                if($return) {
                // Java files successfully added to Database
                    
                    // Now deleting folder specified by $upload_dir

                    $result = deleteDir($upload_dir);

                    if($result) {
                    // Building JSON response to return back to upload.php
                        $response = '{"status":"success"}';
                    }
                    else {
//                        echo '{"status":"error","code":"110","description":"process.php: Error deleting directory"}';   
                        $response = '{"status":"error","code":"110","description":"process.php: Error deleting directory"}';   
                    }
                }
            }
            else {
            // Java not found installed on server
            // Or Java environment path not set
                
//                echo '{"status":"error","code":"111","description":"process.php: Java not found on system. Contact administrator!"}';
                $response = '{"status":"error","code":"111","description":"process.php: Java not found on system. Contact administrator!"}';
                die("process.php: Java not found on system! Please install java");
            }

        }
        else {
            // User hasn't uploaded any programs yet
    //        echo '{"status":"error","code":"310","process.php: No programs uploaded yet"}';   // debug
            header("Location: ../userhome.php");
            die("Redirecting to userhome.php");
        }
        
        return $response;
    }

    //////////////////////////////
    // DEBUG
    //////////////////////////////


    /***
     *  Run the Code Analyzer on a java file corrected by 'master.Corrector'
     *  Will be called from 'process_.php: attemptToFix'
     *  @param $filename name of file to analyze
     *  @param $upload_dir path to user directory where file is present
     *  @return JSON The AJAX response
     *
     **/
    function runCodeAnalyzerOnFile($filename, $upload_dir) {
        require("config.php");
        
        // default response for failure
        $response = '{"status":"error","description":"process.php: Something went wrong"}';
        
        //    $username               = 'viraj892';
        $username   = $_SESSION['user']['username'];
        $path_to_code_analyser  = 'java' . DIRECTORY_SEPARATOR;

//        $upload_dir             = 'uploads' . DIRECTORY_SEPARATOR . $username;


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
            // Invalid username in cookie
                header("Location: ../index.php");
                die("Redirecting to index.php");
            }

            $userid = $rows[0]['userid'];

        } catch (PDOException $ex) {
            die("Something went wrong" . $ex);
        }

        if(file_exists($upload_dir . DIRECTORY_SEPARATOR . $filename)) {
            // desired program found
            
            // Checking java installation

            // Getting OS environment
            if(substr(PHP_OS, 0, 3) == 'WIN') {
                $java_path = "\"" . exec('where java') . "\"";
//                $check_dir_listing = "dir $upload_dir /b";
            }
            else {
                $java_path = exec('which java');
//                $check_dir_listing = "ls $upload_dir";
            }

                
            // Setting timezone to India
            date_default_timezone_set('Asia/Calcutta');

//            exec($check_dir_listing, $files);

//            foreach($files as $file) {
            
            $file = $upload_dir . DIRECTORY_SEPARATOR . $filename;

            if(strtolower(substr($file, -4)) == 'java') {

                $cmd = "$java_path -cp $path_to_code_analyser"."CodeAnalyzer.jar master.CodeAnalyzer $file";
                
                // debug
//                echo $cmd. "\n";
                
                exec($cmd, $result);
                
                // debug
//                var_dump ($result);

                $prog_name  = $filename;
                $complexity = $result[0];
                $big_o      = $result[1];
                $timestamp  = date("D, F d, Y H:i:s", time());

                // Add to database
                $query = "
                        INSERT INTO results (
                            userid,
                            prog_name,
                            complexity,
                            big_o,
                            timestamp
                        ) VALUES (
                            :userid,
                            :prog_name,
                            :complexity,
                            :big_o,
                            :timestamp
                        )
                ";

                $query_params = array(
                    ':userid'       => $userid,
                    ':prog_name'    => $prog_name,
                    ':complexity' 	=> $complexity,
                    ':big_o'		=> $big_o,
                    ':timestamp'    => $timestamp
                );

                try {
                    $stmt 	= $db -> prepare($query);
                    $return = $stmt -> execute($query_params);
                } catch(PDOException $ex) {
                    $response = '{"status":"error","description":"process.php: Database query error"}';
                    die("Failed to run query: ".$ex -> getMessage());
                }

                // exec() will append to existing array, so there is need to unset it each time
                unset($result);
            }
            

            if($return) {
                // Java files successfully added to Database

                // Now deleting folder specified by $upload_dir
                $result = deleteDir($upload_dir);

                if($result) {
                    // Building JSON response to return back to upload.php
                    $response = '{"status":"success"}';
                }
                else { 
                    $response = '{"status":"error","code":"110","description":"process.php: Error deleting directory"}';   
                }
            }
            

        }
        else {
            // Desired program not found
            $response = '{"status":"error","description":"process.php: File \"' . $filename . '\"" not found!"}';
        }
        
        return $response;
    }

//    runChecker();

    /***
     *  Run the Checker on java files stored uploaded by user
     * @param null No parameters
     * @return JSON response (status: success/error, acceptance: true/false, errors: code, line no)
     *
     **/
    function runChecker() {
    // @TODO uncomment in live environment
        require("../config.php");
        
//        echo "DEBUGGING runChecker()...<br>";
        
        // Debug
//        require("config.php");
        
        // default response for failure
        $response = '{"status":"error","description":"process.php: Something went wrong"}';
        
//            $username               = 'viraj892';
        $username   = $_SESSION['user']['username'];
        $path_to_checker        = '..' . DIRECTORY_SEPARATOR . 'java' . DIRECTORY_SEPARATOR;
        
        // DEBUG
//        $path_to_checker        = 'java' . DIRECTORY_SEPARATOR;

        $upload_dir             = 'uploads' . DIRECTORY_SEPARATOR . $username;
        
        // Debug
//        $upload_dir             = 'mini-upload-form' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $username;
        
        if(is_dir($upload_dir)) {

        // user has uploaded programs
            // Checking java installation

            // Getting OS environment
            if(substr(PHP_OS, 0, 3) == 'WIN') {
                $java_path = "\"" . exec('where java') . "\"";
                $check_dir_listing = "dir $upload_dir /b";
            }
            else {
                $java_path = exec('which java');
                $check_dir_listing = "ls $upload_dir";
            }


            if(strlen($java_path) !== 0) {
                // Java found on system

                exec($check_dir_listing, $files);

                foreach($files as $file) {

                    if(strtolower(substr($file, -4)) == 'java') {

                        $cmd = "$java_path -cp $path_to_checker"."CodeAnalyzer.jar master.Checker $upload_dir" . DIRECTORY_SEPARATOR . "$file";

                        exec($cmd, $result);

//                        var_dump($result);
                        
                        $accept = "false";
                        
                        if(strpos(strtolower($result[0]), "accept") !== false) {
                            $accept = "true";
                        }
                        
                        $response = '{"status":"success","acceptance":"' . $accept;
                        
                        if(sizeof($result) > 1) {
                            $response .= '","errors":"';
                            $response .= implode(",", array_splice($result, 1));
                        }
                        $response .= '"}';
//                        echo "<br/>JSON response = $response<br/>";
            //                    echo "<br/>query = $query<br/>";

                        // exec() will append to existing array, so there is need to unset it each time
                        unset($result);
                    }
                }
            }
            else {
                // Java not found installed on server
                // Or Java environment path not set
//                echo '{"status":"error","code":"111","description":"process.php: Java not found on system. Contact administrator!"}';
                $response = '{"status":"error","code":"111","description":"process.php: Java not found on system. Contact administrator!"}';
                die("process.php: Java not found on system! Please install java");
            }

        }
        else {
            // User hasn't uploaded any programs yet
    //        echo '{"status":"error","code":"310","process.php: No programs uploaded yet"}';   // debug
            header("Location: ../userhome.php");
            die("Redirecting to userhome.php");
        }
        
        
        ////////////////////////////////////////////////////////////////////////
        //
        // ALERT: Remember that no files have been deleted as of now
        // .class files may have been created
        //
        ////////////////////////////////////////////////////////////////////////
        
        return $response;
        
    }


    function deleteDir($dir) {
        if(!file_exists($dir)) return true;
        if(!is_dir($dir)) return unlink($dir);
        foreach(scandir($dir) as $item) {
            if($item == '.' || $item == '..') continue;   
            if(!deleteDir($dir.DIRECTORY_SEPARATOR.$item)) return false;
        }
        return rmdir($dir);
    }

    function deleteFile($dir, $file) {
        if(!file_exists($dir)) return true;
        if(!is_dir($dir)) return unlink($dir);
        foreach(scandir($dir) as $item) {
            if($item == '.' || $item == '..') continue;  
            if(strpos($item, $file) !== false) {
                if(!deleteDir($dir.DIRECTORY_SEPARATOR.$item)) return false;
            }
        }
        return @rmdir($dir);
    }
    
?>