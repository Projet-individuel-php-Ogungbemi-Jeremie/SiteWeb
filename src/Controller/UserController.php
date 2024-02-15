<?php
namespace src\Controller;

use src\Model\User;
use src\Service\JwtService;

class UserController extends AbstractController {

    public function create(){
        if(isset($_POST["nomprenom"]) && isset($_POST["mail"]) && isset($_POST["password"])){
            $user = new User();
            $hashpass = password_hash($_POST["password"], PASSWORD_BCRYPT, ["cost"=>12]);
            $user->setNomPrenom($_POST["nomprenom"])
                ->setMail($_POST["mail"])
                ->setPassword($hashpass);
            $result = User::SqlAdd($user);

            header("location:/ProjetPersoPhp/User/login");
        }
        return $this->getTwig()->render("User/create.html.twig");
    }

    public function login(){
        if(isset($_POST["mail"]) && isset($_POST["password"])) {
            $user = User::SqlGetByMail($_POST["mail"]);
            if($user!=null){
                //Comparaison des mots de passe
                if (password_verify($_POST["password"], $user->getPassword())) {
                    $_SESSION["login"] = [
                        "mail" => $user->getMail(),
                        "nomprenom" => $user->getNomPrenom()
                    ];
                    header("location:/ProjetPersoPhp/Concert/all");
                } else {
                    throw new \Exception("Erreur User/Password");
                }
            }else{
                throw new \Exception("Aucun user avec ce mail");
            }
        }else{
            return $this->getTwig()->render("User/login.html.twig");
        }

    }

    public static function protect(){
        if(!isset($_SESSION["login"])){
            throw new \Exception("Vous devez vous authentifier pour acceder à cette page");
        }

    }

    public function logout(){
        if(isset($_SESSION["login"])){
            unset($_SESSION["login"]);
        }
        header("location:/ProjetPersoPhp");
    }

    public function loginJwt(){
        header('Content-Type: application/json; charset=utf-8');

        if($_SERVER["REQUEST_METHOD"] != "POST"){
            header("HTTP/1.1 404 Not Found");
            return json_encode("Erreur de méthode (POST attendu)");
        }

        if(!isset($_POST["mail"]) || !isset($_POST["password"])){
            header("HTTP/1.1 404 Not Found");
            return json_encode("Erreur il manque des données)");
        }

        $user = User::SqlGetByMail($_POST["mail"]);
        if($user==null){
            return json_encode("Erreur user inconu");
        }

        if (!password_verify($_POST["password"], $user->getPassword())) {
            return json_encode("Erreur User / Password");
        }

        echo JwtService::createToken([
           "mail" => $user->getMail(),
           "roles" => $user->getRoles(),
           "nomprenom" => $user->getNomPrenom()
        ]);
    }

}