<?php

namespace src\Model;

class Concert implements \JsonSerializable
{
    private ?int $Id = null;
    private ?string $Nom = null;
    private ?string $Description = null;
    private ?\DateTime $DateConcert = null;
    private ?int $Prix = null;
    private ?float $Longitude = null;
    private ?float $Latitude = null;
    private ?string $PersonneAContacter = null;

    private ?string $EmailAContacter = null;
    private ?string $ImageRepository = null;
    private ?string $ImageFileName = null;

    private ?string $ImageData = null;

    public function getImageData(): ?string
    {
        return $this->ImageData;
    }

    public function setImageData(?string $ImageData): Concert
    {
        $this->ImageData = $ImageData;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->Id;
    }

    public function setId(?int $Id): Concert
    {
        $this->Id = $Id;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(?string $Nom): Concert
    {
        $this->Nom = $Nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): Concert
    {
        $this->Description = $Description;
        return $this;
    }

    public function getDateConcert(): ?\DateTime
    {
        return $this->DateConcert;
    }

    public function setDateConcert(?\DateTime $DateConcert): Concert
    {
        $this->DateConcert = $DateConcert;
        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->Prix;
    }

    public function setPrix(?int $Prix): Concert
    {
        $this->Prix = $Prix;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->Longitude;
    }

    public function setLongitude(?float $Longitude): Concert
    {
        $this->Longitude = $Longitude;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->Latitude;
    }

    public function setLatitude(?float $Latitude): Concert
    {
        $this->Latitude = $Latitude;
        return $this;
    }

    public function getPersonneAContacter(): ?string
    {
        return $this->PersonneAContacter;
    }

    public function setPersonneAContacter(?string $PersonneAContacter): Concert
    {
        $this->PersonneAContacter = $PersonneAContacter;
        return $this;
    }

    public function getEmailAContacter(): ?string
    {
        return $this->EmailAContacter;
    }

    public function setEmailAContacter(?string $EmailAContacter): Concert
    {
        $this->EmailAContacter = $EmailAContacter;
        return $this;
    }

    public function getImageRepository(): ?string
    {
        return $this->ImageRepository;
    }

    public function setImageRepository(?string $ImageRepository): Concert
    {
        $this->ImageRepository = $ImageRepository;
        return $this;
    }

    public function getImageFileName(): ?string
    {
        return $this->ImageFileName;
    }

    public function setImageFileName(?string $ImageFileName): Concert
    {
        $this->ImageFileName = $ImageFileName;
        return $this;
    }

    public function strlen()
    {
        return \strlen($this->Nom) + 1;
    }

    public function SqlAdd(): array
    {
        try {
            $bdd = BDD::getInstance();
            $requete = $bdd->prepare("INSERT INTO concerts (Nom, Description, DateConcert, Prix, Longitude, Latitude, PersonneAcontacter,EmailAContacter, 
                      ImageRepository, ImageFileName, ImageData) 
                    VALUES(:Nom, :Description, :DateConcert, :Prix, :Longitude, :Latitude, :PersonneAcontacter, :EmailAContacter, :ImageRepository, :ImageFileName, :ImageData)");

            $execute = $requete->execute([
                "Nom" => $this->getNom(),
                "Description" => $this->getDescription(),
                "DateConcert" => $this->getDateConcert()->format("Y-m-d"),
                "Prix" => $this->getPrix(),
                "Longitude" => $this->getLongitude(),
                "Latitude" => $this->getLatitude(),
                "PersonneAcontacter" => $this->getPersonneAcontacter(),
                "EmailAContacter" =>$this->getEmailAContacter(),
                "ImageRepository" => $this->getImageRepository(),
                "ImageData" => $this->getImageData(), // Ajout de l'attribut "ImageData" dans le tableau associatif
                "ImageFileName" => $this->getImageFileName(),
            ]);
            return [200, "Insertion OK", $bdd->lastInsertId()];
        } catch (\Exception $e) {
            return [1, $e->getMessage()];
        }

    }


    public static function SqlGetAll()
    {
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('SELECT * FROM concerts ORDER BY Id DESC');
        $requete->execute();
        $concertsSQL = $requete->fetchAll(\PDO::FETCH_ASSOC);
        $concertsObjet = [];
        foreach ($concertsSQL as $concertSQL) {
            $concert = new Concert();
            $DateConcert = new \DateTime($concertSQL["DateConcert"]);
            $concert->setNom($concertSQL["Nom"])
                ->setId($concertSQL["Id"])
                ->setDescription($concertSQL["Description"])
                ->setDateConcert($DateConcert)
                ->setPrix($concertSQL["Prix"])
                ->setLongitude($concertSQL["Longitude"])
                ->setLatitude($concertSQL["Latitude"])
                ->setPersonneAcontacter($concertSQL["PersonneAContacter"])
                ->setEmailAContacter($concertSQL["EmailAContacter"])
                ->setImageRepository($concertSQL["ImageRepository"])
                //->setImageData($concertSQL["ImageData"])
                ->setImageFileName($concertSQL["ImageFileName"]);
            $concertsObjet[] = $concert;
        }
        return $concertsObjet;
    }

    public static function SqlGetById(int $id): ?Concert
    {
        $bdd = BDD::getInstance();
        $req = $bdd->prepare("SELECT * FROM concerts WHERE Id=:Id");
        $req->execute([
            "Id" => $id
        ]);
        $concertSql = $req->fetch(\PDO::FETCH_ASSOC);
        if ($concertSql != false) {
            $concert = new Concert();
            $concert->setNom($concertSql["Nom"])
                ->setId($id)
                ->setDescription(($concertSql["Description"]))
                ->setDateConcert(new \DateTime($concertSql["DateConcert"]))
                ->setPrix($concertSql["Prix"])
                ->setLongitude($concertSql["Longitude"])
                ->setLatitude($concertSql["Latitude"])
                ->setPersonneAContacter($concertSql["PersonneAContacter"])
                ->setEmailAContacter($concertSql["EmailAContacter"])
                ->setImageRepository($concertSql["ImageRepository"])
                ->setImageData($concertSql["ImageData"])
                ->setImageFileName($concertSql["ImageFileName"]);
            return $concert;
        }
        return null;
    }

    public function SqlUpdate(): array
    {
        try {
            $bdd = BDD::getInstance();
            $requete = $bdd->prepare('UPDATE concerts SET Nom=:Nom, Description=:Description, DateConcert=:DateConcert, Prix=:Prix, Longitude=:Longitude, Latitude=:Latitude, 
                    PersonneAContacter=:PersonneAContacter, EmailAContacter=:EmailAContacter, ImageRepository=:ImageRepository, ImageFileName=:ImageFileName, ImageData=:ImageData WHERE Id=:Id');
            $result = $requete->execute([
                'Nom' => $this->getNom(),
                'Description' => $this->getDescription(),
                'DateConcert' => $this->getDateConcert()->format("Y-m-d"),
                'Prix' => $this->getPrix(),
                'Longitude' => $this->getLongitude(),
                'Latitude' => $this->getLatitude(),
                'PersonneAContacter' => $this->getPersonneAContacter(),
                'EmailAContacter'=>$this->getEmailAContacter(),
                'ImageRepository' => $this->getImageRepository(),
                'ImageFileName' => $this->getImageFileName(),
                'ImageData' => $this->getImageData(),
                'Id' => $this->getId()
            ]);
            return [200, "[OK] Mise à jour"];
        } catch (\Exception $e) {
            return [1, "[ERREUR] " . $e->getMessage()];
        }
    }

    public static function SqlDelete(int $id)
    {
        $bdd = BDD::getInstance();
        $req = $bdd->prepare("DELETE FROM concerts WHERE Id=:Id");
        $req->execute([
            "Id" => $id
        ]);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'Id' => $this->Id,
            'Nom' => $this->Nom,
            'Description' => $this->Description,
            'DateConcert' => $this->DateConcert->format("Y-m-d"),
            'Prix' => $this->Prix,
            'Longitude' => $this->Longitude,
            'Latitude' => $this->Latitude,
            'PersonneAContacter' => $this->PersonneAContacter,
            'EmailAContacter'=>$this->EmailAContacter,
            'ImageRepository' => $this->ImageRepository,
            'ImageFileName' => $this->ImageFileName,
            'ImageData' => $this->ImageData,
        ];
    }
}

