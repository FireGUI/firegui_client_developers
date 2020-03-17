<?php
    ini_set('memory_limit', '5120M');
    set_time_limit(0);
    
    $dbHost = (!empty($_POST['dbHost'])) ? trim(strtolower($_POST['dbHost'])) : '127.0.0.1';
    $dbPort = (!empty($_POST['dbPort'])) ? trim(strtolower($_POST['dbPort'])) : '3306';
    $dbUser = (!empty($_POST['dbUser'])) ? trim(strtolower($_POST['dbUser'])) : die(json_encode(['status' => 0, 'txt' => 'Db User must not be empty']));
    $dbPass = (!empty($_POST['dbPassword'])) ? trim(strtolower($_POST['dbPassword'])) : die(json_encode(['status' => 0, 'txt' => 'Db Password must not be empty']));
    $dbName = (!empty($_POST['dbName'])) ? trim(strtolower($_POST['dbName'])) : die(json_encode(['status' => 0, 'txt' => 'Db Name must not be empty']));
    
    try {
        $db = new PDO("mysql:host={$dbHost}:{$dbPort};dbname={$dbName}",
            $dbUser,
            $dbPass,
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        
        if (!empty($_POST['dbImport']) && $_POST['dbImport'] == 1) {
            if (!file_exists('../dump_file.sql')) {
                die(json_encode(['status' => 0, 'txt' => 'Unable to find sql database file']));
            }
            
            $sqlFile = file_get_contents('../dump_file.sql');
            
            $result = $db->exec($sqlFile);
            
            if (!$result) {
                $dbconf = __DIR__.'/../../application/config/database.php';

                if (file_exists($dbconf)) {
                    $str = file_get_contents($dbconf);
    
                    $str = str_replace('server1.firegui.com', $dbHost,$str);
                    $str = str_replace('<dbname>', $dbName,$str);
                    $str = str_replace('<dbuser>', $dbUser,$str);
                    $str = str_replace('<dbpassword>', $dbPass,$str);
    
                    file_put_contents($dbconf, $str);
                }
                
                die(json_encode(['status' => 1, 'txt' => 'Db imported successfully']));
            } else {
                die(json_encode(['status' => 0, 'txt' => 'Db import failed: <b>' . $db->errorInfo() . '</b>']));
            }
        }
    } catch (PDOException $ex) {
        die(json_encode(['status' => 0, 'txt' => 'Db error: <b>' . $ex->getMessage() . '</b>']));
    }