<?php
require "./vendor/autoload.php";

session_start();
$Database = new App\Database('devis');
$Database->DbConnect();
$Cmd = new App\Tables\Stats($Database);
$Users = new \App\Tables\User($Database);



if (empty($_FILES)){
    http_response_code(400);
    $response = [
        "success" => "false"
    ];
    echo json_encode($response);
}else {

    if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
        foreach ($_FILES as $file) {
            $mime_type  = mime_content_type($file['tmp_name']);
            $path = 'C:\laragon\www\SoftRecode\upload\temp';
            if (!is_dir($path)) {
                mkdir($path, 0777, TRUE);
            }
            move_uploaded_file($file["tmp_name"], $path . '/' . $file['name']);
            http_response_code(200);
            $response = [
                "success" => "true"
            ];
            echo json_encode($response);
        }
    }else{
        foreach ($_FILES as $file) {
            
            $path = 'C:\laragon\www\SoftRecode\upload\temp/' . $file['name'];
            if (file_exists($path)){
                unlink($path);
            }
            $response = [
                "success" => "true"
            ];
            http_response_code(200);
            echo json_encode($response);
        }
    }
    
}



  