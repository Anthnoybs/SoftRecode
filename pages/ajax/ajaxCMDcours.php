<?php
require "./vendor/autoload.php";

session_start();
$Database = new App\Database('devisrecode');
$Database->DbConnect();
$Devis = new App\Tables\Devis($Database);
$Command = new App\Tables\Command($Database);

// si pas connecté on ne vole rien ici :
if (empty($_SESSION['user'])) {
    echo 'no no no .... ';
 }
 else {

// requete table client:
 if (!empty($_POST['AjaxCmd'])){
    $resArray = [];
    $command =  $Command->GetById($_POST['AjaxCmd']);
    $ligne = $Command->commandLigne($_POST['AjaxCmd']);
    array_push($resArray , $command , $ligne);
    echo  json_encode($resArray);
 }
 else {
    echo 'request failed';
 }

}