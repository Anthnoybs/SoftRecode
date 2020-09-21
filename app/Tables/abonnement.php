<?php

namespace App\Tables;
use App\Tables\Table;
use App\Database;
use PDO;

class Abonnement extends Table 
{ 

    public Database $Db;

    public function __construct($db) 
    {
        $this->Db = $db;
    }


    public function createOne($cmd , $client , $actif , $auto , $presta , $note , $mois )
    {
        $request = $this->Db->Pdo->prepare('INSERT INTO abonnement ( ab__cmd__id, ab__client__id_fact,
        ab__actif, ab__fact_auto,  ab__presta,
        ab__note, ab__mois_engagement)
        VALUES (:ab__cmd__id, :ab__client__id_fact, :ab__actif, :ab__fact_auto, :ab__presta,
        :ab__note, :ab__mois_engagement)');

        $request->bindValue(":ab__cmd__id", $cmd);
        $request->bindValue(":ab__client__id_fact", $client);
        $request->bindValue(":ab__actif", $actif);
        $request->bindValue(":ab__fact_auto", $auto);
        $request->bindValue(":ab__presta",$presta);
        $request->bindValue(":ab__note", $note);   
        $request->bindValue(":ab__mois_engagement", $mois);

        $request->execute();

        $idABN = $this->Db->Pdo->lastInsertId();

        return $idABN;
    }

    public function getById($id)
    {
        $request =$this->Db->Pdo->query("SELECT  ab__cmd__id, ab__client__id_fact,
        ab__actif, ab__fact_auto,  ab__presta
        FROM abonnement
        WHERE ab__cmd__id = ".$id."
        ORDER BY  ab__cmd__id DESC LIMIT 200 ");
        $data = $request->fetch(PDO::FETCH_OBJ);
        return $data;
    }

    public function getAll()
    {
        $request =$this->Db->Pdo->query("SELECT  ab__cmd__id, ab__client__id_fact,
        ab__actif, ab__fact_auto,  ab__presta
        FROM abonnement
        ORDER BY  ab__cmd__id DESC LIMIT 200 ");
        $data = $request->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }

    public function getLigne($id)
    {
        $request =$this->Db->Pdo->query("SELECT 
        abl__cmd__id , abl__ligne , abl__dt_debut , abl__actif, abl__id__fmm, 
        abl__designation, abl__sn, abl__type_repair, abl__prix_mois, abl__note_interne
        FROM abonnement_ligne
        WHERE abl__cmd__id = ".$id."
        ORDER BY  abl__ligne DESC LIMIT 200 ");
        $data = $request->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }

    public function returnMax($cmd)
    {
        $verifOrdre = $this->Db->Pdo->query(
            'SELECT MAX(abl__ligne) as ligne from abonnement_ligne');
        $response  = $verifOrdre->fetch(PDO::FETCH_OBJ);
        return $response;
    }

    public function insertRobot($idCmd , $numeroLigne , $datedeDebut , $idFmm , $designation , $sn , $type , $prix , $note)
    {
        $request = $this->Db->Pdo->prepare('INSERT INTO abonnement_ligne ( abl__cmd__id , abl__ligne,
        abl__dt_debut, abl__actif,  abl__id__fmm,
        abl__designation, abl__sn , abl__type_repair , abl__prix_mois , abl__note_interne)
        VALUES (:abl__cmd__id, :abl__ligne, :abl__dt_debut, :abl__actif, :abl__id__fmm,
        :abl__designation, :abl__sn , abl__type_repair , abl__prix_mois , abl__note_interne)');

        $request->bindValue(":abl__cmd__id", $idCmd);
        $request->bindValue(":abl__ligne", $numeroLigne);
        $request->bindValue(":abl__dt_debut", $datedeDebut);
        $request->bindValue(":abl__actif", $idFmm);
        $request->bindValue(":abl__id__fmm",$designation);
        $request->bindValue(":abl__designation", $note);   
        $request->bindValue(":abl__sn", $sn);
        $request->bindValue(":abl__type_repair", $type);
        $request->bindValue(":abl__prix_mois", $prix);
        $request->bindValue(":abl__note_interne",  $note);

        $request->execute();

        $idABN = $this->Db->Pdo->lastInsertId();

        return $idABN;
    }
}