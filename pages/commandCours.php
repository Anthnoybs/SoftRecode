<?php
require "./vendor/autoload.php";
require "./App/twigloader.php";
session_start();

 //URL bloqué si pas de connexion :
 if (empty($_SESSION['user'])) {
    header('location: login');
 }else{
$user = $_SESSION['user'];

//Connexion et requetes : 
$Database = new App\Database('devisrecode');
$Database->DbConnect();
$Command = new \App\Tables\Command($Database);
$Devis = new \App\Tables\Devis($Database);



// si une validation de devis a été effectuée : 
if(!empty($_POST['devisCommande'])){
    $date = date("Y-m-d H:i:s");
    $command = $Command->insertOne(
        $date,
        $_POST['devisCommande'],
        $_POST['userCommande'],
        intval($_POST['clientCommande']),
        intval($_POST['LivraisonCommande']),
        $_POST['portCommande'],
        intval($_POST['contactCommande']),
        $_POST['ComClientCommande'],
        $_POST['ComInterCommande'],
        'ATN',
        json_decode($_POST['arrayLigneDeCommande']),
    );
    $Devis->updateStatus('CMD',$_POST['devisCommande']);
    header('Location: commandCours');
}
// listes de commandes : 
$listOfCommand = $Command->getAll();

foreach ($listOfCommand as $command) {
   $commandDate = date_create($command->cmd__date_crea);
   $date = date_format($commandDate, 'd/m/Y');
   $command->cmd__date_crea = $date;
}

// Donnée transmise au template : 
echo $twig->render('commandCours.twig',[
    "user"=> $user, 
    'listOfCommand' => $listOfCommand
]);


 }