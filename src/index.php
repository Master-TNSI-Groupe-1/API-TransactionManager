<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \structures\Data as Data;
// use \structures\Database as DataBase;

require '../vendor/autoload.php';
require 'config/Database.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

// $db = Database::getInstance()->getDb();

/**
 * Récupère les lieux pour un site donnée
 * {id} ID du site
 */
$app->get('/get/lieux/{idsite}', function (Request $request, Response $response) {
    // Initialise un objet Data avec les valeurs par défaut.
    $data = new Data();

    try{
        //TODO : Changer la façon dont on se connecte à la BDD, voir en fonction de ce qu'à fait Anas.
        

        $id = $request->getAttribute('idsite');
        $db = Database::getInstance()->getDb();

        // Récupère les lieux
        $query = $db->prepare("SELECT * FROM location WHERE id_site = :idsite");
        $query->bindParam(':idsite', $id, PDO::PARAM_INT);
        $query->execute();

        $lieux = $query->fetchAll(PDO::FETCH_OBJ);

        if($lieux){
            // Les données de la requête sont affectées à la var $data.
            $data->data = $lieux;
            $data->status = "success";
            $data->message = "Ok.";
            $data->code = 200;
        }else{
            $data->message = "Pas de lieux.";
        }

    }catch (Exception $e){
        $data->message = $e->getMessage();
    }

    // Renvoie du résultat sous format JSON avec le code de retour HTTP
    return $response->withJson($data, $data->code);
});

/**
 * Récupère la capacité actuelle, la capacité max et le nom du lieu
 * {id} ID du lieu
 */
$app->get('/get/lieu/{id}', function (Request $request, Response $response) {
    // Initialise un objet Data avec les valeurs par défaut.
    $data = new Data();

    try{
        //TODO : Changer la façon dont on se connecte à la BDD, voir en fonction de ce qu'à fait Anas.
        

        $id = $request->getAttribute('id');
        $db = Database::getInstance()->getDb();

        // Récupère les lieux
        $query = $db->prepare("SELECT * FROM location WHERE id_location = :id");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $lieu = $query->fetch(PDO::FETCH_OBJ);

        if($lieu){
            // Les données de la requête sont affectées à la var $data.
            $data->data = $lieu;
            $data->status = "success";
            $data->message = "Ok.";
            $data->code = 200;
        }else{
            $data->message = "Pas de lieu.";
        }

    }catch (Exception $e){
        $data->message = $e->getMessage();
    }

    // Renvoie du résultat sous format JSON avec le code de retour HTTP
    return $response->withJson($data, $data->code);
});

/**
 * Récupère les sites
 */
$app->get('/get/sites', function (Request $request, Response $response) {
    $data = new Data();

    try{
        //TODO : Changer la façon dont on se connecte à la BDD, voir en fonction de ce qu'à fait Anas.
        $db = Database::getInstance()->getDb();

        $query = $db->query("SELECT * FROM site");

        $site = $query->fetchAll(PDO::FETCH_OBJ);

        if($site){
            $data->data = $site;
            $data->status = "success";
            $data->message = "Ok.";
            $data->code = 200;
        }else{
            $data->message = "L'utilisateur n'existe pas.";
        }

    }catch (Exception $e){
        $data->message = $e->getMessage();
    }

    return $response->withJson($data, $data->code);
});

/**
 * Ajout d'un lieu avec ses pointxy et ses sensors.
 * Données attendu par le post. Les noms doivent matcher avec le formulaire d'ajout.
 * nomCategorie -> nom du lieu
 * capMax -> capacité max du lieu
 * idCategorie -> ID du site associé à ce lieu
 * positions -> Nombre de sensors à ajouter
 * latitude$i -> latitude du i-ème sensor
 * longitude$i -> longitude du i-ème sensor
 */
$app->post('/post/lieu', function (Request $request, Response $response){
    $data = new Data();

    try{
        //TODO : Changer la façon dont on se connecte à la BDD, voir en fonction de ce qu'à fait Anas.
        
        $db = Database::getInstance()->getDb();
        $postData = $request->getParsedBody();

        $query = $db->prepare("INSERT INTO location(name, number_places, id_site) VALUES (:nomCategorie, :capMax, :idCategorie)");
        $query->bindParam(":nomCategorie", $postData["nomCategorie"], PDO::PARAM_STR_CHAR);
        $query->bindParam(":capMax", $postData["capMax"], PDO::PARAM_INT);
        $query->bindParam(":idCategorie", $postData["idCategorie"], PDO::PARAM_INT);
        $query->execute();

        $lastId = $db->lastInsertId();

        for ($i = 1; $i <= $postData["positions"]; $i++){
            $query2 = $db->prepare("INSERT INTO pointxy(latitude, longitude, id_location) VALUES(:latitude, :longitude, :idlocation)");
            $query2->bindParam(":latitude", $postData["latitude$i"], PDO::PARAM_INT);
            $query2->bindParam(":longitude", $postData["longitude$i"], PDO::PARAM_INT);
            $query2->bindParam(":idlocation", $lastId, PDO::PARAM_INT);
            $query2->execute();
        }

        //TODO : Revoir la partie sensors, données à insert ??
        for ($i = 1; $i <= $postData["sensors"]; $i++){
            $query3 = $db->prepare("INSERT INTO sensors(id_location) VALUES(:idlocation)");
            $query3->bindParam(":idlocation", $lastId, PDO::PARAM_INT);
            $query3->execute();
        }

        $data->status = "success";
        $data->message = "Lieu ajouté.";
        $data->code = 201;
        $data->data = "";

    }catch (Exception $e){
        $data->message = $e->getMessage();
    }

    return $response->withJson($data, $data->code);
});

/**
 * Suppression d'un lieu et de ses sensors et pointxy.
 * {id} ID du lieu à supprimer.
 */
$app->delete('/delete/lieu/{id}', function(Request $request, Response $response){
    $data = new Data();

    try{
        //TODO : Changer la façon dont on se connecte à la BDD, voir en fonction de ce qu'à fait Anas.
        
        $db = Database::getInstance()->getDb();
        $id = $request->getAttribute('id');

        $query = $db->prepare("DELETE FROM location WHERE id_location = :idlocation");
        $query->bindParam(':idlocation', $id, PDO::PARAM_INT);
        $query->execute();

        $query2 = $db->prepare("DELETE FROM pointxy WHERE id_location = :idlocation");
        $query2->bindParam(':idlocation', $id, PDO::PARAM_INT);
        $query2->execute();

        $query3 = $db->prepare("DELETE FROM sensors WHERE id_location = :idlocation");
        $query3->bindParam(':idlocation', $id, PDO::PARAM_INT);
        $query3->execute();

        $data->status = "success";
        $data->message = "Ok.";
        $data->code = 200;
        $data->data = "";

    }catch (Exception $e){
        $data->message = $e->getMessage();
    }

    return $response->withJson($data, $data->code);
});

$app->run();


