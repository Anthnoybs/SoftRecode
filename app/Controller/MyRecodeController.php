<?php

namespace App\Controller;
require_once  '././vendor/autoload.php';
use App\Controller\BasicController;
use App\Tables\Keyword;
use App\Tables\User;
use DateTime;
use App\Tables\Tickets;
use App\Apiservice\ApiTest;


class MyRecodeController extends BasicController {
    public static function displayList(){
        self::init();
        self::security();
        $Api = new ApiTest();
        if (empty($_SESSION['user']->refresh_token)) {
            $token = $Api->login($_SESSION['user']->email , 'test');
            if ($token['code'] != 200) {
                echo 'Connexion LOGIN à L API IMPOSSIBLE';
                die();
            }
            $_SESSION['user']->refresh_token = $token['data']['refresh_token'] ; 
            $token =  $token['data']['token'];
        }else{
            $refresh = $Api->refresh($_SESSION['user']->refresh_token);
            if ( $refresh['code'] != 200) {
                echo 'Rafraichissemnt de jeton API IMPOSSIBLE';
                die();
            }
            $token =  $refresh['token']['token'];
        }
        // $test = $Api->getFiles($token , 105333 , 'scoreJPG.jpg');
        // var_dump($test);
        // die();
        // return var_dump($test);
        // die();
        // $_SESSION['user']->refreshToken = $token['data']['refresh_token'];
        // $token = self::handleToken($Api);
        $query_exemple = [
            'tk__id' =>  [],
            'tk__groupe' => [] ,
            'tk__lu' => [], 
            'tk__motif' => ['TKM'] , 
            'search' => '', 
            'RECODE__PASS' => "secret"
        ];
        if (!empty($_GET)){

            if (!empty($_GET['tk__lu'])) {
                foreach ($_GET['tk__lu']  as  $value) {
                    array_push($query_exemple['tk__lu'], $value);
                }
            }
            if (!empty($_GET['search'])) {
                    if (is_numeric($_GET['search']) and strlen($_GET['search']) ==  5 ) {
                        array_push( $query_exemple['tk__id'] , $_GET['search']);
                    }
                    elseif (is_numeric($_GET['search']) and strlen($_GET['search']) ==  4 ) {
                        array_push( $query_exemple['tk__groupe'] ,$_GET['search']);
                    }else{
                        $query_exemple['search'] = $_GET['search'] ;
                    }
            }
           
        }
        if (!empty($_GET['nonLu'])) {
            $nonLus = [
                'tk__id' => $_GET['nonLu'] , 
                'tk__lu' => 3
            ];
            $Api->updateTicket($token , $nonLus);
            header('location: myRecode');
            die();
        }
        $query_exemple['RECODE__PASS'] = "secret" ;
        $list = $Api->getTicketList($token , $query_exemple);
        $list = $list['data'];
        $definitive_edition = [];
        $t_lu = 0;
        $t_nlu = 0;
        $t_clo = 0 ;
        foreach ($list as $ticket){
            switch ($ticket['tk__lu']) {
                case 5:
                    $t_lu ++;
                    break;
                case 9:
                    $t_clo ++;
                    break;
                default:
                    $t_nlu ++;
                    break;
            }
            $ticket['user'] = reset($ticket['lignes']);
            $ticket['user'] = $ticket['user']['tkl__user_id'];
            $ticket['dest'] = end($ticket['lignes']);
            $ticket['last'] =  $ticket['dest']['tkl__user_id'];
            $ticket['dest'] =  $ticket['dest']['tkl__user_id_dest'];
            $ticket['info'] = end($ticket['lignes']);
            $ticket['memo']  =  $ticket['info']['tkl__memo'];
            $mat_request = $Api->getMateriel($token, ['mat__id[]' =>  $ticket['tk__motif_id'] , 'RECODE__PASS' => 'secret']);
            if ($mat_request['code'] == 200) {
                $ticket['mat'] =  $mat_request['data'][0];
                $ticket['cli'] =  $Api->getClient($token, ['cli__id' => $ticket['mat']['mat__cli__id']])['data'];
            }
            $date_time = new DateTime($ticket['info']['tkl__dt']);
			$ticket['date'] = $date_time->format('d/m/Y H:i');
            array_push($definitive_edition , $ticket );
        }
        //////////////////filters
        $filters = [];
        $filters['lu'] = false ;
        $filters['nonLu'] = false;
        $filters['cloture'] = false ;
        $filters['search'] = false ;
        $filters['tk__id'] = false ;
        $filters['tk__groupe'] = false ;
        foreach ($query_exemple['tk__lu'] as $value) {
           switch ( $value) {
               case  3 :
                   $filters['nonLu'] =  true ;
                   break;
                case  5 :
                    $filters['lu'] = true;
                    break;
                case  9 :
                    $filters['cloture'] = true;
                    break; 
           }
        }
        if (!empty($_GET['search']))
            $filters['search'] = $_GET['search'];
        // if (!empty($query_exemple['tk__id'])) {
        //     $filters['tk__id'] = " ";
        //     foreach ($query_exemple['tk__id'] as  $value) {
        //         $filters['tk__id'] .= " " . $value . " ";
        //     }
        // }
        // if (!empty($query_exemple['tk__groupe'])) {
        //     $filters['tk__groupe'] = " ";
        //     foreach ($query_exemple['tk__groupe'] as  $value) {
        //         $filters['tk__groupe'] .= " " . $value . " ";
        //     }
        // }
        $total = count($definitive_edition);
        $nb_resultats = $t_nlu . ' NON LUS - ' . $t_lu . ' LUS - ' . $t_clo . ' CLOTURES SUR ' . $total . ' RESULTATS' ;
      
        return self::$twig->render(
            'display_ticket_myrecode_list.html.twig',[
                'user' => $_SESSION['user'],
                'list' => $definitive_edition , 
                'filters' => $filters , 
                'results' => $nb_resultats
            ]
        );
    }

    public static function displayTickets(){
        self::init();
        self::security();
        $Users = new User(self::$Db);
        $Api = new ApiTest();
        $alert = false;
        
        if (empty($_SESSION['user']->refresh_token)){
            $token = $Api->login($_SESSION['user']->email , 'test');
            if ($token['code'] != 200) {
                echo 'Connexion LOGIN à L API IMPOSSIBLE';
                die();
            }
            $_SESSION['user']->refresh_token = $token['data']['refresh_token'] ; 
            $token =  $token['data']['token'];
        }else{
            $refresh = $Api->refresh($_SESSION['user']->refresh_token);
            if ( $refresh['code'] != 200) {
                echo 'Rafraichissemnt de jeton API IMPOSSIBLE';
                die();
            }
            $token =  $refresh['token']['token'];
        }
       
        if (!empty($_GET['tk__id'])){
            $query_exemple = [
                'tk__id' => [],
                'RECODE__PASS' => "secret"
             ] ;
            
            if (is_numeric($_GET['tk__id']) and strlen($_GET['tk__id']) ==  5 ) {
                array_push( $query_exemple['tk__id'] ,$_GET['tk__id']);

                $query_exemple['RECODE__PASS'] = "secret";
                $list = $Api->getTicketList($token , $query_exemple);
              
                $list = $list['data'];
                $definitive_edition = [];
                foreach ($list as $ticket){
                    self::updateTicket($ticket , $token , 5 , $Api );
                    $ticket['user'] = reset($ticket['lignes']);
                    $ticket['user'] = $ticket['user']['tkl__user_id'];
                    $ticket['dest'] = end($ticket['lignes']);
                    $ticket['last'] =  $ticket['dest']['tkl__user_id'];
                    $ticket['dest'] =  $ticket['dest']['tkl__user_id_dest'];
                    $ticket['info'] = end($ticket['lignes']);
                    $ticket['memo']  =  $ticket['info']['tkl__memo'];
                    $mat_request = $Api->getMateriel($token, ['mat__id[]' =>  $ticket['tk__motif_id'] , 'RECODE__PASS' => 'secret']);
        
                    if ($mat_request['code'] == 200){
                        $ticket['mat'] =  $mat_request['data'][0];
                        $ticket['cli'] =  $Api->getClient($token, ['cli__id' => $ticket['mat']['mat__cli__id']])['data'];
                    }

                    foreach ($ticket['lignes'] as $key  => $entry) {
                        $ticket['lignes'][$key]['logos'] = $Api->les_fichiers('public/img/tickets/'.$entry['tkl__id'] , null);
                    }
                    
                    $date_time = new DateTime($ticket['info']['tkl__dt']);
                    $ticket['date'] = $date_time->format('d/m/Y H:i');
                    array_push($definitive_edition , $ticket);
                }

                if (empty($definitive_edition)){
                    header('location: myRecode');
                    exit;
                }

                $ticket = $definitive_edition[0];
                switch ($ticket['mat']['mat__kw_tg']) {
                    case 'AUT':
                        $gar = 'Autre';
                        break;
                    case 'GCO':
                        $gar = 'Garantie constructeur';
                        break;
                    case 'GNO':
                        $gar = 'NON garantie';
                        break;
                    case 'GRE':
                        $gar = 'Garantie RECODE';
                        break;
                    case 'LOC':
                        $gar = 'Location RECODE';
                        break;
                    case 'MNT':
                        $gar = 'Maintenance RECODE';
                        break;
                    default:
                        $gar = 'NON garantie';
                        break;
                }
                $ticket['lignes'][0]['entities'][0] = [
                    "gar" => $gar ,
                    'dateof' => $ticket['mat']['mat__date_offg'] ,
                    "name" => $ticket['mat']['mat__model'], 
                    "label" => $ticket['mat']['mat__pn'], 
                    "additionals" => $ticket['mat']['mat__sn']  , 
                    "alternative" => "public/img/pn2.jpg" ,
                ];
                $ticket['lignes'][0]['entities'][1] = [
                    "identifier" => $ticket['cli']['cli__id'],
                    "name" => $ticket['cli']['cli__nom'], 
                    "label" => $ticket['cli']['cli__adr1'], 
                    "additionals" => $ticket['cli']['cli__cp'] . ' ' . $ticket['cli']['cli__ville'] , 
                    "alternative" => "public/img/client_image.png"
                ];

                if (!empty($_POST['tk__id']) and !empty($_POST['what'])){ 
                    switch ($_POST['what']) {
                        case 'RPD':
                            $dest = intval($ticket['last']['user__id']);
                            break;
                        case 'CIN':
                            $dest = intval($_POST['dest']);
                            break;
                        case 'CLO':
                            $dest = intval($ticket['last']['user__id']);
                            break;
                    }

                    if (!empty($_FILES)){
                        $fileName = $_FILES['file']['name'];
                        $tempPath = $_FILES['file']['tmp_name'];
                        $fileSize = $_FILES['file']['size'];
                       
                        $fileExtension = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));
                        $validExtension = array('jpeg','jpg','png','gif','pdf','txt');
                        if (!in_array($fileExtension, $validExtension) and $fileSize > 111) {
                            $_SESSION['file_alert']  = '  Merci de télécharger un fichier au format : jpeg , jpg , png , gif , pdf ou txt';
                            header('location: myRecode-ticket?tk__id='.$_GET['tk__id']);
                            die();
                        }
                        if ($fileSize > 10000000) {
                             $_SESSION['file_alert']  = 'Fichier trop volumineux';
                             header('location: myRecode-ticket?tk__id='.$_GET['tk__id']);
                             die();
                        }
                    }
                    
                    $id_ligne =  self::PostLigne($_POST ,$dest , $Api, $token);
                    if ($fileSize > 111) {
                        $ticket = self::PostChamps($id_ligne,$_POST,$Api,$token);
                    }
                    
                    if (!empty($_FILES)){
                        move_uploaded_file($tempPath, __DIR__ .'/' .$fileName);
                        $file = $Api->postFile($token, fopen(__DIR__ . '/' .$fileName , 'r') ,$id_ligne);
                        unlink(__DIR__ .'/' .$fileName);
                       
                    }
                    header('location: myRecode');
                    exit;
                }

                if (!empty($_SESSION['file_alert'])) {
                    $alert = $_SESSION['file_alert'];
                    $_SESSION['file_alert'] = '';
                }

                return self::$twig->render(
                    'display_ticket_myrecode.html.twig',[
                        'user' => $_SESSION['user'],
                        'ticket' => $ticket , 
                        'users_list' => $Users->getAll() , 
                        'alert' => $alert
                    ]
                );
            }else{
                header('location: myRecode');
                exit;
            }
        }else{
            header('location: myRecode');
            exit;
        }
    }

    public static function PostLigne($post , $dest , $api , $token){
        $visible = 0 ;
        if ($post['what'] == 'SEC') {
            $visible = 1 ;
        }
        $tkl = [
            'tkl__tk_id' => $post['tk__id'],
            'tkl__motif_ligne' => $post['what'], 
            'tkl__memo' => 'Réponse Recode' ,
            'tkl__user_id' => $_SESSION['user']->id_utilisateur,
            'tkl__user_id_dest' => $dest , 
            'tkl__visible' => $visible
        ];  
        
        return $api->postTicketLigne($token,  $tkl)['data']['tkl__id'];
    }

    public static function PostChamps($ligne , $post , $api , $token){
        $tklc = [
            'tklc__id' =>  $ligne, 
            'tklc__nom_champ' => 'INF', 
            'tklc__ordre' =>  1 , 
            'tklc__memo' => $post['content']
        ];
        return $api->postTicketLigneChamps($token , $tklc);
    }

    public static function updateTicket($ticket , $token , $lu , $api){
        $ticket['tk__lu'] = $lu ;
        return $api->updateTicket($token , $ticket);
    }
}