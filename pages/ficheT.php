<?php
require "./vendor/autoload.php";
require "./App/twigloader.php";
session_start();

 //URL bloqué si pas de connexion :
 if (empty($_SESSION['user'])) 
 {
    header('location: login');
 }
 if ($_SESSION['user']->user__devis_acces < 10 ) 
 {
   header('location: noAccess');
 }

 

 //déclaration des instances nécéssaires :
 $user= $_SESSION['user'];
 $Database = new App\Database('devis');
 $Database->DbConnect();
 $Keyword = new App\Tables\Keyword($Database);
 $Client = new App\Tables\Client($Database);
 $Contact = new \App\Tables\Contact($Database);
 $Cmd = new App\Tables\Cmd($Database);
 
//par défault le champ de recherche est égal a null:
 $champRecherche = null;
 
// variable qui determine la liste des devis à afficher:
if (!empty($_POST['recherche-fiche'])) 

{
    switch ($_POST['recherche-fiche']) {
        case 'search':
           $devisList = $Cmd->magicRequestCMD($_POST['rechercheF']);
           $champRecherche = $_POST['rechercheF'];
           break;
   
        case 'id-fiche':
           $devisList = [];
           $devisSeul = $Cmd->GetById(intval($_POST['rechercheF']));
           $champRecherche = $_POST['rechercheF'];
           array_push($devisList, $devisSeul);
           break;
        
        default:
           $devisList = $Cmd->getFromStatusCMD();
           break;
    }
   
} else $devisList = $Cmd->getFromStatusCMD();
 
//nombre des fiches dans la liste 
 $NbDevis = count($devisList);

//formatte la date pour l'utilisateur:
 foreach ($devisList as $devis) 
 {
   $devisDate = date_create($devis->cmd__date_cmd);
   $date = date_format($devisDate, 'Y/m/d');
   $devis->devis__date_crea = $date;
 }
  
// Donnée transmise au template : 
echo $twig->render('ficheT.twig',
[
'user'=>$user,
'devisList'=>$devisList,
'NbDevis'=>$NbDevis,
'champRecherche'=>$champRecherche

]);