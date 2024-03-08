<?php
namespace src\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService{
    public static string $secretKey = "projetperso";

    public static function createToken(array $datas) : string {
        $issuedAt = new \DateTime(); //Date de publication
        $expire = new \DateTime();
        $expire->modify('+6 minutes');

        $data = [
            "iat" => $issuedAt->getTimestamp(), // Date création
            "iss" => "cesi.local",
            "nbf" => $issuedAt->getTimestamp(), //Utilisable pas avant ...
            "exp" => $expire->getTimestamp(),
            "datas" => $datas
        ];

        $jwt = JWT::encode($data, self::$secretKey, "HS512");

        return $jwt;

    }

    public static function checkToken() : array {

        if (!isset($_SERVER['HTTP_AUTHORIZATION']) || !preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            $result = [
                "code" => 401,
                "body" => "Token non trouvé dans la requête"
            ];
            header("HTTP/1.1 401 Unauthorized");
            return $result;
        }

        $jwt = $matches[1];
        if (!$jwt) {
            $result = [
                "code" => 401,
                "body" => "Aucun jeton n'a pu être extrait de l'en-tête d'autorisation."
            ];
            header("HTTP/1.1 401 Unauthorized");
            return $result;
        }

        try{
            //ça remonte une exception dès qu'il trouve une erreur on veut catch l'erreur pour la donner en JSON
            $token = JWT::decode($jwt, new Key(self::$secretKey, 'HS512'));
        }catch (\Exception $e){
            $result = [
                "code" => 401,
                "body" => "Les données du jeton ne sont pas compatibles : {$e->getMessage()}"
            ];
            header("HTTP/1.1 401 Unauthorized");
            return $result;
        }

        $now = new \DateTimeImmutable();
        $serverName = "cesi.local";

        if ($token->iss !== $serverName ||
            $token->nbf > $now->getTimestamp() ||
            $token->exp < $now->getTimestamp())
        {
            $result = [
                "code" => 401,
                "body" => "Les données du jeton ne sont pas compatibles"
            ];
            header("HTTP/1.1 401 Unauthorized");
            return $result;
        }

        $result = [
            "code" => 200,
            "body" => "Token OK"
        ];
        return $result;

    }

}
