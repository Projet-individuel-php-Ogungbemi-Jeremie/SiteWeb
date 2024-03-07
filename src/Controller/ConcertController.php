<?php
namespace src\Controller;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use src\Model\Concert;


class ConcertController extends AbstractController {

    public function index(){
        $concerts = Concert::SqlGetAll();
        return $this->getTwig()->render('Concert/index.html.twig',[
            "concerts" => $concerts
        ]);
    }

    public function all(){
        $token = md5(random_bytes(32));
        $_SESSION["token"] = $token;
        $concerts = Concert::SqlGetAll();
        return $this->getTwig()->render('Concert/all.html.twig',[
            "concerts" => $concerts,
            "tokenCSRF" => $token
        ]);
    }

    public function delete(){
        UserController::protect();
        if(isset($_POST["id"])){
            if($_SESSION["token"] == $_POST["tokenCSRF"]){
                // Supprimer l'image associée au concert
                $concert = Concert::SqlGetById($_POST["id"]);
                $repository = "./uploads/images/{$concert->getImageRepository()}";
                if(file_exists('../ProjetPersoPhp'.'/'.$repository.'/'.$concert->getImageFileName())) {
                    unlink($repository.'/'.$concert->getImageFileName());
                }
                Concert::SqlDelete($_POST["id"]);
            }
        }
        header("Location: /ProjetPersoPhp/Concert/all");
    }

    public function add(){
        UserController::protect();
        if(isset($_POST["Nom"]) && isset($_POST["Description"])){
            $sqlRepository = null;
            $nomImage = null;
            $imageData = null;

            if(!empty($_FILES["Image"]["name"])){
                $tabExt = ["jpg", "jpeg", "gif", "png"]; // Extension autorisée
                $extension = pathinfo($_FILES["Image"]["name"], PATHINFO_EXTENSION);
                if(in_array(strtolower($extension), $tabExt)){
                    //Fabriquer le répertoire d'accueil façon Wrodpress (YYYY/MM)
                    $dateNow = new \DateTime();
                    $sqlRepository = $dateNow->format("Y/m");
                    $repository = "./uploads/images/{$dateNow->format("Y/m")}";
                    if(!is_dir($repository)) {
                        mkdir($repository, 0777, true);
                    }
                    //Renommer le fichier image à la volée
                    $nomImage = md5(uniqid()).".".$extension;
                    //Upload du fichier
                    move_uploaded_file($_FILES["Image"]["tmp_name"], $repository."/".$nomImage);
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
                ->setImageFileName($nomImage)
                ->setImageData($imageData);

            $result = $concert->SqlAdd();



            header("Location: /ProjetPersoPhp/Concert/all");
        }
        return $this->getTwig()->render("Concert/add.html.twig");
    }

    public function show(int $id){
        $concert = Concert::SqlGetById($id);
        if($concert==null){
            header("Location: /ProjetPersoPhp/Concert/all");
        }
        return $this->getTwig()->render("Concert/show.html.twig", [
            "concert" => $concert
        ]);
    }

    public function update(int $id){
        UserController::protect();
        $concert = Concert::SqlGetById($id);
        if($concert!=null){
            if(isset($_POST["Nom"]) && isset($_POST["Description"]) && isset($_POST["DateConcert"]) && isset($_POST["Prix"]) && isset($_POST["Longitude"]) && isset($_POST["Latitude"]) && isset($_POST["PersonneAContacter"])) {
                $sqlRepository = null;
                $nomImage = null;

                if(!empty($_FILES['Image']['name']) ) {
                    $tabExt = ['jpg','gif','png','jpeg'];    // Extensions autorisees
                    $extension  = pathinfo($_FILES['Image']['name'], PATHINFO_EXTENSION);
                    // strtolower = on compare ce qui est comparage (JPEG =! jpeg)
                    if(in_array(strtolower($extension),$tabExt)) {
                        // Fabrication du répertoire d'accueil façon "Wordpress" (YYYY/MM)
                        $dateNow = new \DateTime();
                        $sqlRepository = $dateNow->format('Y/m');
                        $repository = './uploads/images/'.$dateNow->format('Y/m');
                        if(!is_dir($repository)){
                            mkdir($repository,0777,true);
                        }
                        // Renommage du fichier
                        $nomImage = md5(uniqid()) .'.'. $extension;
                        move_uploaded_file($_FILES['Image']['tmp_name'], $repository.'/'.$nomImage);

                        // Suppression de l'ancienne image si existante
                        if($_POST['imageAncienne'] != '' && $_POST['imageAncienne'] != '/' && file_exists("../ProjetPersoPhp/uploads/images/{$_POST["imageAncienne"]}")){
                            unlink("./uploads/images/{$_POST['imageAncienne']}");
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
                        ->setImageFileName($nomImage);
                    $result = $concert->SqlUpdate();

                    if($result[0]=="1"){
                        if($nomImage !=null){
                            unlink($repository.'/'.$nomImage);
                        }
                    }
                    header("Location: /ProjetPersoPhp/Concert/all");
                }else {
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
                        ->setImageFileName($_POST["imageAncienne"]);
                    $result = $concert->SqlUpdate();

                    header("Location: /ProjetPersoPhp/Concert/all");
                }

            }else{
                return $this->getTwig()->render('Concert/update.html.twig',[
                    "concert"=>$concert
                ]);
            }

        }else{
            header("Location:/ProjetPersoPhp/Concert/all");
        }
    }

    public function pdf(int $id){
        $concert = Concert::SqlGetById($id);
        $mpdf = new Mpdf([
            "tempDir" => $_SERVER["DOCUMENT_ROOT"]."/../var/cache/pdf"
        ]);
        $mpdf->WriteHTML($this->getTwig()->render("Concert/pdf.html.twig", [
            "concert" => $concert
        ]));
        $mpdf->Output(name: "Concert.pdf",dest: Destination::DOWNLOAD);
    }
}
