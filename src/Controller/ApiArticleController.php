<?php
namespace src\Controller;

use src\Model\Concert;
use src\Service\JwtService;

class ApiArticleController{

    public function __construct(){
        header('Content-Type: application/json; charset=utf-8');
    }

    //GET ALL
    public function getAll(){
        if($_SERVER["REQUEST_METHOD"] != "GET"){
                header("HTTP/1.1 404 Not Found");
            return json_encode("Erreur de méthode (GET attendu)");
        }

        $result = JwtService::checkToken();
        if($result["code"] == 1){
            return json_encode($result);
        }

        $articles = Concert::SqlGetAll();
        return json_encode($articles);
    }

    public function add(){
        if($_SERVER["REQUEST_METHOD"] != "POST"){
            header("HTTP/1.1 404 Not Found");
            return json_encode("Erreur de méthode (POST attendu)");
        }

        if(!isset($_POST["Titre"]) || !isset($_POST["Description"])){
            header("HTTP/1.1 404 Not Found");
            return json_encode("Erreur il manque des données)");
        }

        $article = new Concert();
        $article->setTitre($_POST["Titre"])
            ->setDescription($_POST["Description"])
            ->setDatePublication(new \DateTime($_POST["DatePublication"]))
            ->setAuteur($_POST["Auteur"]);
        $result = $article->SqlAdd();
        return json_encode($result);
    }

    public function addBis(){
        if($_SERVER["REQUEST_METHOD"] != "POST"){
            header("HTTP/1.1 404 Not Found");
            return json_encode("Erreur de méthode (POST attendu)");
        }

        // Takes raw data from the request
        $json = file_get_contents('php://input');
        // Converts it into a PHP object
        $data = json_decode($json);

        if(!isset($data->Titre) || !isset($data->Description)){
            header("HTTP/1.1 404 Not Found");
            return json_encode("Erreur il manque des données)");
        }



        $article = new Concert();
        $article->setTitre($data->Titre)
            ->setDescription($data->Description)
            ->setDatePublication(new \DateTime($data->DatePublication))
            ->setAuteur($data->Auteur);
        $result = $article->SqlAdd();
        return json_encode($result);
    }
}










