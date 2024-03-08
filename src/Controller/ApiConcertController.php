<?php
namespace src\Controller;

use src\Model\Concert;
use src\Service\JwtService;

class ApiConcertController{

    public function __construct(){
        header('Content-Type: application/json; charset=utf-8');
    }

    //GET ALL
    public function getAll(){
        if($_SERVER["REQUEST_METHOD"] != "GET"){
            header("HTTP/1.1 405 Method Not Allowed");
            return json_encode("Erreur de méthode (GET attendu)");
        }

        $concerts = Concert::SqlGetAll();
        return json_encode($concerts);
    }

    public function add(){
        if($_SERVER["REQUEST_METHOD"] != "POST"){
            header("HTTP/1.1 405 Method Not Allowed");
            return json_encode("Erreur de méthode (POST attendu)");
        }

        $result = JwtService::checkToken();
        if($result["code"] == 401){
            return json_encode($result);
        }

        if(!isset($_POST["Nom"]) || !isset($_POST["Description"])){
            header("HTTP/1.1 400 Bad Request");
            return json_encode("Erreur il manque des données)");
        }


        $sqlRepository = null;
        $nomImage = null;

        if(isset($_POST["ImageFileName"])){
            $tabExt = ["jpg", "jpeg", "gif", "png"]; // Extension autorisée
            $extension = pathinfo($_POST["ImageFileName"], PATHINFO_EXTENSION);
            if(in_array(strtolower($extension), $tabExt)){
                //Fabriquer le répertoire d'accueil façon Wrodpress (YYYY/MM)
                $dateNow = new \DateTime();
                $sqlRepository = $dateNow->format("Y/m");
                $repository = "./uploads/images/{$dateNow->format("Y/m")}";
                if(!is_dir($repository)) {
                    mkdir($repository, 0777, true);
                }
                // Renommage du fichier (d'où l'intéret d'avoir isolé l'extension
                $nomImage = md5(uniqid()).".".$extension;
                //Encodage de l'image en base64
                $ifp = fopen( $repository.'/'.$nomImage, 'wb' );
                $data = explode( ',', $_POST["ImageData"] );
                fwrite( $ifp, base64_decode( $data[0] ) );
                fclose( $ifp );

            }
        }

        $concert = new Concert();
        $concert->setNom($_POST["Nom"])
            ->setDescription($_POST["Description"])
            ->setDateConcert(new \DateTime($_POST["DateConcert"]))
            ->setPrix($_POST["Prix"])
            ->setLongitude($_POST["Longitude"])
            ->setLatitude($_POST["Latitude"])
            ->setPersonneAContacter($_POST["PersonneAContacter"])
            ->setEmailAContacter($_POST["EmailAContacter"])
            ->setImageRepository($sqlRepository)
            ->setImageData($_POST["ImageData"])
            ->setImageFileName($nomImage);

        $result = $concert->SqlAdd();
        return json_encode($result);
    }



    public function update(int $id){

        if($_SERVER["REQUEST_METHOD"] != "POST"){
            header("HTTP/1.1 404 Not Found");
            return json_encode("Erreur de méthode (POST attendu)");
        }

        $result = JwtService::checkToken();
        if($result["code"] == 401){
            return json_encode($result);
        }

        $concert = Concert::SqlGetById($id);
        if($concert!=null) {
            if (isset($_POST["Nom"]) && isset($_POST["Description"]) && isset($_POST["DateConcert"]) && isset($_POST["Prix"]) && isset($_POST["Longitude"]) && isset($_POST["Latitude"]) && isset($_POST["PersonneAContacter"])) {
                $sqlRepository = null;
                $nomImage = null;

                if (isset($_POST["ImageFileName"])) {
                    $tabExt = ['jpg', 'gif', 'png', 'jpeg'];    // Extensions autorisees
                    $extension = pathinfo($_POST["ImageFileName"], PATHINFO_EXTENSION);
                    // strtolower = on compare ce qui est comparage (JPEG =! jpeg)
                    if (in_array(strtolower($extension), $tabExt)) {
                        // Fabrication du répertoire d'accueil façon "Wordpress" (YYYY/MM)
                        $dateNow = new \DateTime();
                        $sqlRepository = $dateNow->format('Y/m');
                        $repository = './uploads/images/' . $dateNow->format('Y/m');
                        if (!is_dir($repository)) {
                            mkdir($repository, 0777, true);
                        }

                        // Renommage du fichier (d'où l'intéret d'avoir isolé l'extension
                        $nomImage = md5(uniqid()).".".$extension;
                        //Encodage de l'image en base64

                        $ifp = fopen( $repository.'/'.$nomImage, 'wb' );
                        $data = explode( ',', $_POST["ImageData"] );
                        fwrite( $ifp, base64_decode( $data[0] ) );
                        fclose( $ifp );

                        // Supprimer l'ancienne image si elle existe
                        if(file_exists('../ProjetPersoPhp'.'/'.$repository.'/'.$concert->getImageFileName())) {
                            unlink($repository.'/'.$concert->getImageFileName());
                        }
                    }
                }
                $date = new \DateTime($_POST["DateConcert"]);
                $concert->setNom($_POST["Nom"])
                    ->setDescription($_POST["Description"])
                    ->setDateConcert($date)
                    ->setPrix($_POST["Prix"])
                    ->setLongitude($_POST["Longitude"])
                    ->setLatitude($_POST["Latitude"])
                    ->setPersonneAContacter($_POST["PersonneAContacter"])
                    ->setEmailAContacter($_POST["EmailAContacter"])
                    ->setImageRepository($sqlRepository)
                    ->setImageFileName($nomImage)
                    ->setImageData($_POST["ImageData"]);
                $result = $concert->SqlUpdate();
                return json_encode($result);
            }
        }else {
            header("HTTP/1.1 404 Not Found");
            return json_encode("Concert non trouvé");
        }

    }


    public function delete(int $id){
        if($_SERVER["REQUEST_METHOD"] != "DELETE"){
            header("HTTP/1.1 404 Not Found");
            return json_encode("Erreur de méthode (DELETE attendu)");
        }

        $result = JwtService::checkToken();
        if($result["code"] == 401){
            return json_encode($result);
        }

        $concert = Concert::SqlGetById($id);
        if(!$concert){
            header("HTTP/1.1 404 Not Found");
            return json_encode("Concert non trouvé");
        }
        // Supprimer l'image associée au concert
        $repository = "./uploads/images/{$concert->getImageRepository()}";
        if(file_exists('../ProjetPersoPhp'.'/'.$repository.'/'.$concert->getImageFileName())) {
            unlink($repository.'/'.$concert->getImageFileName());
        }

        Concert::SqlDelete($id);
        header("HTTP/1.1 200 OK");
        return json_encode("ok");
    }
}










