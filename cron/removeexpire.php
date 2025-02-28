<?php
ini_set('error_log', 'error_log');
date_default_timezone_set('Asia/Tehran');
require_once '../config.php';
require_once '../botapi.php';
require_once '../panels.php';
require_once '../functions.php';
require_once '../text.php';
$ManagePanel = new ManagePanel();


$setting = select("setting", "*");
// buy service 
$stmt = $pdo->prepare("SELECT * FROM invoice WHERE (status = 'active' OR status = 'end_of_time' OR status = 'end_of_volume' OR status = 'sendedwarn') AND name_product != 'usertest' ORDER BY RAND() LIMIT 10");
$stmt->execute();
        while ($line = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $line  = trim($line['username']);
        $resultss = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$line'"));
        $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$resultss['Service_location']}'"));
        if($marzban_list_get['type'] == "hiddify")continue;
        $get_username_Check = $ManagePanel->DataUser($resultss['Service_location'],$resultss['username']);
        if($get_username_Check['status'] != "Unsuccessful"){
        if(in_array($get_username_Check['status'],['limited','expired'])){
        $timeservice = $get_username_Check['expire'] - time();
        $day = floor($timeservice / 86400);
        $output =  $get_username_Check['data_limit'] - $get_username_Check['used_traffic'];
        $textservice = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (text) FROM textbot WHERE id_text = 'text_Purchased_services'"))['text'];
        $RemainingVolume = formatBytes($output);
        $status_var = [
        'active' => $textbotlang['users']['stateus']['active'],
        'limited' => $textbotlang['users']['stateus']['limited'],
        'disabled' => $textbotlang['users']['stateus']['disabled'],
        'expired' => $textbotlang['users']['stateus']['expired'],
        'on_hold' => $textbotlang['users']['stateus']['onhold'],
    ][$get_username_Check['status']];
    
        if ($day <= intval("-".$setting['removedayc'])) {
           $textre = "📌 Dear user, due to non-renewal, the service {$resultss['username']} has been removed from your service list.  

🌟 To purchase a new service, please proceed to the service purchase section.";
            sendmessage($resultss['id_user'], $textre, null, 'HTML');
            update("invoice","status","removeTime", "username",$line);
            $ManagePanel->RemoveUser($resultss['Service_location'], $line);
            $text_report = "❌ The service with the username $line has been deleted.  
            Reason for deletion: $status_var";
            if (strlen($setting['Channel_Report']) > 0) {
            sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
        }
            }
        }
        }
    }
