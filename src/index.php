<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \structures\Data as Data;

require '../vendor/autoload.php';
require 'config/Database.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

/**
 * Récupère les lieux pour un site donnée
 * {id} ID du site
 */
$app->get('/get/lieux/{idsite}', function (Request $request, Response $response) {
    // Initialise un objet Data avec les valeurs par défaut.
    $data = new Data();

    try{
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
        $id = $request->getAttribute('id');
        $db = Database::getInstance()->getDb();

        // Récupère les lieux
        $query = $db->prepare("SELECT * FROM location WHERE id_location = :id");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $lieu = $query->fetch(PDO::FETCH_OBJ);

        // Récupère les sensors
        $query2 = $db->prepare('SELECT * FROM sensors WHERE id_location = :idlocation');
        $query2->bindParam(':idlocation', $id, PDO::PARAM_INT);
        $query2->execute();

        $sensors = $query2->fetchAll(PDO::FETCH_OBJ);

        // Récupère les pointxy
        $query3 = $db->prepare('SELECT * FROM pointxy WHERE id_location = :idlocation');
        $query3->bindParam(':idlocation', $id, PDO::PARAM_INT);
        $query3->execute();

        $pointxy = $query3->fetchAll(PDO::FETCH_OBJ);

        if($lieu){
            // Les données de la requête sont affectées à la var $data.
            $data->data = $lieu;
            $data->data->sensors = $sensors;
            $data->data->pointxy = $pointxy;
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
        $db = Database::getInstance()->getDb();
        $postData = $request->getParsedBody();

        $query = $db->prepare("INSERT INTO location(name, number_places, number_user, is_enabled, id_site) VALUES (:nomCategorie, :capMax, 0, 1, :idCategorie)");
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

        for ($i = 1; $i <= $postData["sensors"]; $i++){
            $query3 = $db->prepare("INSERT INTO sensors(id_location, is_enabled, is_input) VALUES(:idlocation, 1, 1)");
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

/**
 * Permet d'incrémenter ou décrémenter la valeur en fonction du l'id du sensor.
 * {idsensor} ID du sensor
 */
$app->get('/get/sensor/pulsation/{idsensor}', function(Request $request, Response $response){
    $data = new Data();

    try{
        $db = Database::getInstance()->getDb();

        // Récup l'id du sensor en paramètre
        $idsensor = $request->getAttribute('idsensor');

        // On récupère les infos du lieu et sensors associé à l'id du sensor
        $query = $db->prepare('SELECT * FROM sensors s, location l WHERE l.id_location = s.id_location AND s.id_sensor = :idsensor');
        $query->bindParam(':idsensor', $idsensor, PDO::PARAM_INT);
        $query->execute();
        $lieu = $query->fetch(PDO::FETCH_OBJ);


        if($lieu){
            $idlieu = $lieu->id_location;
            ($lieu->is_input == 1) ? $valueinputsensor = 1 : $valueinputsensor = -1;

            // En fonction du type de sensor on incrémente ou décrémente la valeur instantanée de 1.
            $query2 = $db->prepare('UPDATE location SET number_user = number_user + :increment WHERE id_location = :idlocation');
            $query2->bindParam(':idlocation', $idlieu, PDO::PARAM_INT);
            $query2->bindParam(':increment', $valueinputsensor, PDO::PARAM_INT);
            $query2->execute();

            $data->code = 200;
            $data->status = "success";
            $data->message = "Valeur mise à jour ($valueinputsensor)";
        }else{
            $data->message = "Le lieu ou le capteur n'existe pas.";
        }

    }catch (Exception $e){
        $data->message = $e->getMessage();
    }

    return $response->withJson($data, $data->code);
});

/**
 * Permet d'incrémenter la valeur en fonction de l'id du lieu
 * {idlieu} ID du lieu
 */
$app->get('/get/lieu/increment/{idlieu}', function (Request $request, Response $response){
    $data = new Data();

    try{
        $db = Database::getInstance()->getDb();
        $idlieu = $request->getAttribute('idlieu');

        $query = $db->prepare('UPDATE location SET number_user = number_user + 1 WHERE id_location = :idlocation');
        $query->bindParam(':idlocation', $idlieu, PDO::PARAM_INT);
        $query->execute();

        $data->code = 200;
        $data->status = "success";
        $data->message = "Valeur mise à jour (1)";
    }catch (Exception $e){
        $data->message = $e->getMessage();
    }

    return $response->withJson($data, $data->code);
});

/**
 * Permet de décrémenter la valeur en fonction de l'id du lieu
 * {idlieu} ID du lieu
 */
$app->get('/get/lieu/decrement/{idlieu}', function (Request $request, Response $response){
    $data = new Data();

    try{
        $db = Database::getInstance()->getDb();
        $idlieu = $request->getAttribute('idlieu');

        $query = $db->prepare('UPDATE location SET number_user = number_user - 1 WHERE id_location = :idlocation');
        $query->bindParam(':idlocation', $idlieu, PDO::PARAM_INT);
        $query->execute();

        $data->code = 200;
        $data->status = "success";
        $data->message = "Valeur mise à jour (-1)";
    }catch (Exception $e){
        $data->message = $e->getMessage();
    }

    return $response->withJson($data, $data->code);
});

/**
 * Retourner les prévisions IA.
 * {idlocation} id location.
 */
$app->get('/get/previsions/{idlocation}', function (Request $request, Response $response){
   $data = new Data();

   try{
       $db = Database::getInstance()->getDb();
       $idlocation = $request->getAttribute('idlocation');

       $query = $db->prepare('SELECT * FROM iadata WHERE id_location = :idlocation ORDER BY date_update DESC');
       $query->bindParam(':idlocation', $idlocation, PDO::PARAM_INT);
       $query->execute();

       $iadata = $query->fetch(PDO::FETCH_OBJ);

       if($iadata){
           $data->code = 200;
           $data->status = "success";
           $data->message = "Ok.";
           $data->data = $iadata;
           $data->data->json_object = json_decode($iadata->json_object);
       }else{
           $data->message = "Pas de donnees.";
       }

   }catch (Exception $e){
       $data->message = $e->getMessage();
   }

   return $response->withJson($data, $data->code);
});

$app->run();


