<?php
/**
 * @OA\Info(
 *   title="Documentation de l'API",
 *   description="Cette API permet de gérer les sites et lieux (récupération d'informations, incrémentation/décrémentation du nombre de places).",
 *   version="1.0.0"
 * )
 *
 * @OA\Server(
 *     description="API Production",
 *     url="http://3.87.54.32"
 * )
 *
 * @OA\Server(
 *     description="API Test",
 *     url="http://localhost:8080"
 * )
 */

use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \structures\Data as Data;

require '../vendor/autoload.php';
require 'config/Database.php';

$configuration = [
 'settings' => [
  'displayErrorDetails' => true,
 ],
];
$c   = new \Slim\Container($configuration);
$app = new \Slim\App($c);


/**
 * @OA\Get(
 *   path="/get/lieux/{idsite}",
 *   summary="Récupère les lieux pour un site passé en paramètre.",
 *   tags={"Lieux"},
 *     @OA\Parameter(
 *         name="idsite",
 *         in="path",
 *         description="ID du site.",
 *         required=true,
 *         @OA\Schema(
 *           type="integer",
 *           format="int64"
 *         ),
 *     ),
 *   @OA\Response(
 *     response=200,
 *     description="Une liste de lieux.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Une erreur est survenue.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *   )
 * )
 */
$app->get('/get/lieux/{idsite}', function (Request $request, Response $response) {
 // Initialise un objet Data avec les valeurs par défaut.
 $data = new Data();

 try {
  $id = $request->getAttribute('idsite');
  $db = Database::getInstance()->getDb();

  // Récupère les lieux
  $query = $db->prepare("SELECT * FROM location WHERE id_site = :idsite AND is_enabled = 1");
  $query->bindParam(':idsite', $id, PDO::PARAM_INT);
  $query->execute();

  $lieux = $query->fetchAll(PDO::FETCH_OBJ);

  if ($lieux) {
   // Les données de la requête sont affectées à la var $data.
   $data->data    = $lieux;
   $data->status  = "success";
   $data->message = "Ok.";
   $data->code    = 200;
  } else {
   $data->message = "Pas de lieux.";
  }

 } catch (Exception $e) {
  $data->message = $e->getMessage();
 }

 // Renvoie du résultat sous format JSON avec le code de retour HTTP
 return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($data, $data->code);
});

/**
 * @OA\Get(
 *   path="/get/lieu/{idlieu}",
 *   summary="Récupère les informations d'un lieu en paramètre (infos du lieu, capteurs, coordonnées).",
 *   tags={"Lieux"},
 *     @OA\Parameter(
 *         name="idlieu",
 *         in="path",
 *         description="ID du lieu.",
 *         required=true,
 *         @OA\Schema(
 *           type="integer",
 *           format="int64"
 *         ),
 *     ),
 *   @OA\Response(
 *     response=200,
 *     description="Informations d'un lieu.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Une erreur est survenue.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *   )
 * )
 */
$app->get('/get/lieu/{idlieu}', function (Request $request, Response $response) {
    // Initialise un objet Data avec les valeurs par défaut.
    $data = new Data();

    try{
        $id = $request->getAttribute('idlieu');
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
    return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($data, $data->code);
});

/**
 * @OA\Get(
 *   path="/get/sites",
 *   summary="Récupère les sites.",
 *   tags={"Sites"},
 *   @OA\Response(
 *     response=200,
 *     description="Une liste de sites.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Une erreur est survenue.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *   )
 * )
 */
$app->get('/get/sites', function (Request $request, Response $response) {
 $data = new Data();

 try {
  $db = Database::getInstance()->getDb();

  $query = $db->query("SELECT * FROM site");

  $site = $query->fetchAll(PDO::FETCH_OBJ);

  if ($site) {
   $data->data    = $site;
   $data->status  = "success";
   $data->message = "Ok.";
   $data->code    = 200;
  } else {
   $data->message = "L'utilisateur n'existe pas.";
  }

 } catch (Exception $e) {
  $data->message = $e->getMessage();
 }

 return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($data, $data->code);
});

/**
 * NON AFFICHE
 * @OA\ Post
 *     path="/post/lieu",
 *     summary="Ajout d'un lieu avec ses pointxy et ses sensors",
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="nomCategorie",
 *                     description="nom du lieu",
 *                     type="string",
 *                 ),
 *                 @OA\Property(
 *                     property="capMax",
 *                     description="capacité max du lieu",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="idCategorie",
 *                     description="ID du site associé à ce lieu",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="positions",
 *                     description="Nombre de sensors à ajouter",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="latitude$i",
 *                     description="latitude du i-ème sensor",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="longitude$i",
 *                     description="longitude du i-ème sensor",
 *                     type="integer"
 *                 )
 *             )
 *         )
 *     )
 * )
 */
$app->post('/post/lieu', function (Request $request, Response $response) {
 $data = new Data();

 try {
  $db       = Database::getInstance()->getDb();
  $postData = $request->getParsedBody();

  $query = $db->prepare("INSERT INTO location(name, number_places, number_user, is_enabled, id_site) VALUES (:nomCategorie, :capMax, 0, 1, :idCategorie)");
  $query->bindParam(":nomCategorie", $postData["nomCategorie"], PDO::PARAM_STR_CHAR);
  $query->bindParam(":capMax", $postData["capMax"], PDO::PARAM_INT);
  $query->bindParam(":idCategorie", $postData["idCategorie"], PDO::PARAM_INT);
  $query->execute();

  $lastId = $db->lastInsertId();

  for ($i = 1; $i <= $postData["positions"]; $i++) {
   $query2 = $db->prepare("INSERT INTO pointxy(latitude, longitude, id_location) VALUES(:latitude, :longitude, :idlocation)");
   $query2->bindParam(":latitude", $postData["latitude$i"], PDO::PARAM_INT);
   $query2->bindParam(":longitude", $postData["longitude$i"], PDO::PARAM_INT);
   $query2->bindParam(":idlocation", $lastId, PDO::PARAM_INT);
   $query2->execute();
  }

  for ($i = 1; $i <= $postData["sensors"]; $i++) {
   $query3 = $db->prepare("INSERT INTO sensors(id_location, is_enabled, is_input) VALUES(:idlocation, 1, 1)");
   $query3->bindParam(":idlocation", $lastId, PDO::PARAM_INT);
   $query3->execute();
  }

  $data->status  = "success";
  $data->message = "Lieu ajouté.";
  $data->code    = 201;
  $data->data    = "";

 } catch (Exception $e) {
  $data->message = $e->getMessage();
 }

 return $response->withJson($data, $data->code);
});

/**
 * NON AFFICHE
 * @OA\ Delete
 *     path="/delete/lieu/{id}",
 *     tags={"id"},
 *     summary="Suppression d'un lieu et de ses sensors et pointxy",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID du lieu à supprimer",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         ),
 *     )
 * )
 */
$app->delete('/delete/lieu/{id}', function (Request $request, Response $response) {
 $data = new Data();

 try {
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

  $data->status  = "success";
  $data->message = "Ok.";
  $data->code    = 200;
  $data->data    = "";

 } catch (Exception $e) {
  $data->message = $e->getMessage();
 }

 return $response->withJson($data, $data->code);
});

/**
 * @OA\Get(
 *   path="/get/sensor/pulsation/{sensorname}",
 *   summary="Permet d'incrémenter ou décrémenter la valeur en fonction du sensor passé en paramètre.",
 *   tags={"Sensor"},
 *     @OA\Parameter(
 *         name="sensorname",
 *         in="path",
 *         description="Nom du sensor.",
 *         required=true,
 *         @OA\Schema(
 *           type="integer",
 *           format="int64"
 *         ),
 *     ),
 *   @OA\Response(
 *     response=200,
 *     description="Incrémentation ou décrémentation effectuée.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Une erreur est survenue.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *   )
 * )
 */
$app->get('/get/sensor/pulsation/{sensorname}', function (Request $request, Response $response) {
 $data = new Data();

 try {
  $db = Database::getInstance()->getDb();

  // Récup l'id du sensor en paramètre
  $sensorname = $request->getAttribute('sensorname');

  // On récupère les infos du lieu et sensors associé à l'id du sensor
  $query = $db->prepare('SELECT * FROM sensors s, location l WHERE l.id_location = s.id_location AND s.sensor_name = :sensor_name');
  $query->bindParam(':sensor_name', $sensorname, PDO::PARAM_INT);
  $query->execute();
  $lieu = $query->fetch(PDO::FETCH_OBJ);

  if ($lieu) {
   $idlieu = $lieu->id_location;
   (1 == $lieu->is_input) ? $valueinputsensor = 1 : $valueinputsensor = -1;

   // En fonction du type de sensor on incrémente ou décrémente la valeur instantanée de 1.
   $query2 = $db->prepare('UPDATE location SET number_user = number_user + :increment WHERE id_location = :idlocation');
   $query2->bindParam(':idlocation', $idlieu, PDO::PARAM_INT);
   $query2->bindParam(':increment', $valueinputsensor, PDO::PARAM_INT);
   $query2->execute();

   $data->code    = 200;
   $data->status  = "success";
   $data->message = "Valeur mise à jour ($valueinputsensor)";
  } else {
   $data->message = "Le lieu ou le capteur n'existe pas.";
  }

 } catch (Exception $e) {
  $data->message = $e->getMessage();
 }

 return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($data, $data->code);
});

/**
 * @OA\Get(
 *   path="/get/lieu/increment/{idlieu}",
 *   summary="Permet d'incrémenter la valeur en fonction du lieu en paramètre.",
 *   tags={"Lieux"},
 *     @OA\Parameter(
 *         name="idlieu",
 *         in="path",
 *         description="ID du lieu.",
 *         required=true,
 *         @OA\Schema(
 *           type="integer",
 *           format="int64"
 *         ),
 *     ),
 *   @OA\Response(
 *     response=200,
 *     description="Incrémentation effectuée.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Une erreur est survenue.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *   )
 * )
 */
$app->get('/get/lieu/increment/{idlieu}', function (Request $request, Response $response) {
 $data = new Data();

 try {
  $db     = Database::getInstance()->getDb();
  $idlieu = $request->getAttribute('idlieu');

  $query = $db->prepare('UPDATE location SET number_user = number_user + 1 WHERE id_location = :idlocation');
  $query->bindParam(':idlocation', $idlieu, PDO::PARAM_INT);
  $query->execute();

  $data->code    = 200;
  $data->status  = "success";
  $data->message = "Valeur mise à jour (1)";
 } catch (Exception $e) {
  $data->message = $e->getMessage();
 }

 return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($data, $data->code);
});

/**
 * @OA\Get(
 *   path="/get/lieu/decrement/{idlieu}",
 *   summary="Permet de décrémenter la valeur en fonction du lieu en paramètre.",
 *   tags={"Lieux"},
 *     @OA\Parameter(
 *         name="idlieu",
 *         in="path",
 *         description="ID du lieu.",
 *         required=true,
 *         @OA\Schema(
 *           type="integer",
 *           format="int64"
 *         ),
 *     ),
 *   @OA\Response(
 *     response=200,
 *     description="Décrémentation effectuée.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Une erreur est survenue.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *   )
 * )
 */
$app->get('/get/lieu/decrement/{idlieu}', function (Request $request, Response $response) {
 $data = new Data();

 try {
  $db     = Database::getInstance()->getDb();
  $idlieu = $request->getAttribute('idlieu');

  $query = $db->prepare('UPDATE location SET number_user = number_user - 1 WHERE id_location = :idlocation');
  $query->bindParam(':idlocation', $idlieu, PDO::PARAM_INT);
  $query->execute();

  $data->code    = 200;
  $data->status  = "success";
  $data->message = "Valeur mise à jour (-1)";
 } catch (Exception $e) {
  $data->message = $e->getMessage();
 }

 return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($data, $data->code);
});

/**
 * @OA\Get(
 *   path="/get/previsions/{idlieu}",
 *   summary="Retourne les prévisions IA.",
 *   tags={"Previsions"},
 *     @OA\Parameter(
 *         name="idlieu",
 *         in="path",
 *         description="ID du lieu.",
 *         required=true,
 *         @OA\Schema(
 *           type="integer",
 *           format="int64"
 *         ),
 *     ),
 *   @OA\Response(
 *     response=200,
 *     description="Dernière prévisions en date pour le lieu.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Une erreur est survenue.",
 *     @OA\JsonContent(ref="#/components/schemas/Data")
 *   )
 * )
 */
$app->get('/get/previsions/{idlieu}', function (Request $request, Response $response){
   $data = new Data();

   try{
       $db = Database::getInstance()->getDb();
       $idlocation = $request->getAttribute('idlieu');

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

   return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($data, $data->code);
});

$app->run();
