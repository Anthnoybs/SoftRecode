<?php
require "./vendor/autoload.php";


session_start();
$Database = new App\Database('devisrecode');
$Database->DbConnect();
$Client = new App\Tables\Client($Database);

// si pas connecté on ne vole rien ici :
if (empty($_SESSION['user'])) {
    echo 'no no no .... ';
 }
 else {

// requete table client:
 if (!empty($_POST['AjaxClient'])){
    $client = $Client->getOne($_POST['AjaxClient']);
    echo  json_encode($client);
 }
 else {
    echo 'request failed';
 }


}