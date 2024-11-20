<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // ou http://localhost:3000
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

try {
    $dbh = new PDO('mysql:host=localhost;dbname=citation', "root", "");
} catch (PDOException $e) {
    die($e->getMessage());
}

// Récupérer la méthode HTTP utilisée
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sth = $dbh->prepare("SELECT Texte, Auteur  FROM citations");
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
        break;
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $auteur = $input["Auteur"];
        $txt = $input["Texte"];
        $req = "INSERT INTO citations (Texte, Auteur) VALUES ('$txt','$auteur')";
        $sth= $dbh->prepare($req);
        $nbInsert = $sth->execute();
        $msg = "Citation ajouté";
        $code = 200;
        if($nbInsert==0){
            $msg = "Erreur: impossible d'ajouter la citation";
            $code = 400;
        }
        $retour = ['message'=>$msg, 'code'=>$code];
        echo json_encode($retour);
        break;

    case 'PUT':
  //      $input = json_decode(file_get_contents('php://input'), true);
    //    $id = $input['id'];
        $livreMisAJour = null;

        foreach ($livres as &$livre) {
            if ($livre['id'] == $id) {
                $livre['titre'] = $input['titre'] ?? $livre['titre'];
                $livre['auteur'] = $input['auteur'] ?? $livre['auteur'];
                $livre['annee'] = $input['annee'] ?? $livre['annee'];
                $livre['stock'] = $input['stock'] ?? $livre['stock'];
                $livreMisAJour = $livre;
                break;
            }
        }
        echo json_encode($livreMisAJour ?? ["message" => "Livre non trouvé"]);
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'];
        $livreSupprime = false;

        foreach ($livres as $index => $livre) {
            if ($livre['id'] == $id) {
                array_splice($livres, $index, 1);
                $livreSupprime = true;
                break;
            }
        }
        echo json_encode(["message" => $livreSupprime ? "Livre supprimé" : "Livre non trouvé"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Méthode non autorisée"]);
}
