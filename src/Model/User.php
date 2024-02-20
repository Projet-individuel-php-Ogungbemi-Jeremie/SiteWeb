<?php
namespace src\Model;


class User {
    private ?int $Id = null;
    private String $NomPrenom;
    private String $Mail;
    private String $Password;


    public function getId(): ?int
    {
        return $this->Id;
    }

    public function setId(?int $Id): User
    {
        $this->Id = $Id;
        return $this;
    }

    public function getNomPrenom(): string
    {
        return $this->NomPrenom;
    }

    public function setNomPrenom(string $NomPrenom): User
    {
        $this->NomPrenom = $NomPrenom;
        return $this;
    }

    public function getMail(): string
    {
        return $this->Mail;
    }

    public function setMail(string $Mail): User
    {
        $this->Mail = $Mail;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->Password;
    }

    public function setPassword(string $Password): User
    {
        $this->Password = $Password;
        return $this;
    }


    public static function SqlAdd(User $user) :array{
        $bdd = BDD::getInstance();
        try{
            $req = $bdd->prepare("INSERT INTO users (NomPrenom, Email, Password) VALUES(:NomPrenom, :Email, :Password)");
            $req->execute([
                "NomPrenom" => $user->getNomPrenom(),
                "Email" => $user->getMail(),
                "Password" => $user->getPassword(),
            ]);

            return [0,"Insertion OK", $bdd->lastInsertId()];
        }catch (\Exception $e){
            return [1,"ERROR => {$e->getMessage()}"];
        }
    }

    public static function SqlGetByMail($mail) : ?User{
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('SELECT * FROM users WHERE Email=:mail');
        $requete->execute([
            "mail"=> $mail
        ]);

        $userSql = $requete->fetch(\PDO::FETCH_ASSOC);
        if($userSql!= false){
            $user = new User();
            $user ->setMail($userSql ["Email"])
                ->setNomPrenom($userSql ["NomPrenom"])
                ->setId($userSql ["Id"])
                ->setPassword($userSql ["Password"]);
            return $user;
        }
        return null;
    }



}