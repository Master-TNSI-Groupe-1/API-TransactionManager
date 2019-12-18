<?php
use OpenApi\Annotations as OA;
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
 *     path="get/lieux/{idsite}",
 *     tags={"idsite"},
 *     summary="Récupère les lieux pour un site donnée",
 *     description="Récupère une liste des lieux",
 *     @OA\Parameter(
 *         name="idsite",
 *         in="path",
 *         description="ID du site",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="{"status":"success","message":"Ok.","code":200,"data":[{"id_location":"0","name":"Test POST","url_image":null,"is_enabled":"1","number_places":"900","number_user":"1","id_site":"1"},{"id_location":"3","name":"RU","url_image":"","is_enabled":"0","number_places":"200","number_user":"-55","id_site":"1"}]}",
 *     )
 * )
 *
 */
$app->get('/get/lieux/{idsite}', function (Request $request, Response $response) {
 // Initialise un objet Data avec les valeurs par défaut.
 $data = new Data();

 try {
  $id = $request->getAttribute('idsite');
  $db = Database::getInstance()->getDb();

  // Récupère les lieux
  $query = $db->prepare("SELECT * FROM location WHERE id_site = :idsite");
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
 return $response->withJson($data, $data->code);
});

/**
 * @OA\Get(
 *     path="get/lieu/{id}",
 *     tags={"id"},
 *     summary="Récupère la capacité actuelle, la capacité max et le nom du lieu",
 *     description="Récupère la capacité actuelle, la capacité max et le nom du lieu",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID du lieu",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *             example="3"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="{"status":"success","message":"Ok.","code":200,"data":{"id_location":"3","name":"RU","url_image":"","is_enabled":"0","number_places":"200","number_user":"-55","id_site":"1"}}",
 *     )
 * )
 *
 */
$app->get('/get/lieu/{id}', function (Request $request, Response $response) {
 // Initialise un objet Data avec les valeurs par défaut.
 $data = new Data();

 try {
  $id = $request->getAttribute('id');
  $db = Database::getInstance()->getDb();

  // Récupère les lieux
  $query = $db->prepare("SELECT * FROM location WHERE id_location = :id");
  $query->bindParam(':id', $id, PDO::PARAM_INT);
  $query->execute();

  $lieu = $query->fetch(PDO::FETCH_OBJ);

  if ($lieu) {
   // Les données de la requête sont affectées à la var $data.
   $data->data    = $lieu;
   $data->status  = "success";
   $data->message = "Ok.";
   $data->code    = 200;
  } else {
   $data->message = "Pas de lieu.";
  }

 } catch (Exception $e) {
  $data->message = $e->getMessage();
 }

 // Renvoie du résultat sous format JSON avec le code de retour HTTP
 return $response->withJson($data, $data->code);
});

/**
 * @OA\Get(
 *     path="get/sites/",
 *     summary="Récupère les sites",
 *     description="Récupère les sites",
 *     @OA\Response(
 *         response=200,
 *         description="{"status":"success","message":"Ok.","code":200,"data":[{"id_location":"0","name":"Test POST","url_image":null,"is_enabled":"1","number_places":"900","number_user":"1","id_site":"1"},{"id_location":"3","name":"RU","url_image":"","is_enabled":"0","number_places":"200","number_user":"-55","id_site":"1"}]}",
 *     )
 * )
 *
 */
/**
 * Récupère les sites
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

 return $response->withJson($data, $data->code);
});

/**
 * @OA\Post(
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
 * @OA\Delete(
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
 *     path="/get/sensor/pulsation/{idsensor}",
 *     tags={"idsensor"},
 *     summary="Permet d'incrémenter ou décrémenter la valeur en fonction du l'id du sensor",
 *     description="Permet d'incrémenter ou décrémenter la valeur en fonction du l'id du sensor",
 *     @OA\Parameter(
 *         name="idsensor",
 *         in="path",
 *         description="ID du sensor",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *             example="3"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="{"status":"success","message":"Valeur mise \u00e0 jour (-1)","code":200,"data":""}",
 *     )
 * )
 */
$app->get('/get/sensor/pulsation/{idsensor}', function (Request $request, Response $response) {
 $data = new Data();

 try {
  $db = Database::getInstance()->getDb();

  // Récup l'id du sensor en paramètre
  $idsensor = $request->getAttribute('idsensor');

  // On récupère les infos du lieu et sensors associé à l'id du sensor
  $query = $db->prepare('SELECT * FROM sensors s, location l WHERE l.id_location = s.id_location AND s.id_sensor = :idsensor');
  $query->bindParam(':idsensor', $idsensor, PDO::PARAM_INT);
  $query->execute();
  $lieu = $query->fetch(PDO::FETCH_OBJ);

  if ($lieu) {
   $idlieu                                    = $lieu->id_location;
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

 return $response->withJson($data, $data->code);
});

/**
 * @OA\Get(
 *     path="/get/lieu/increment/{idlieu}",
 *     tags={"idlieu"},
 *     summary="Permet d'incrémenter la valeur en fonction de l'id du lieu",
 *     description="Permet d'incrémenter la valeur en fonction de l'id du lieu",
 *     @OA\Parameter(
 *         name="idlieu",
 *         in="path",
 *         description="ID du lieu",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *             example="3"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="{"status":"success","message":"Valeur mise \u00e0 jour (1)","code":200,"data":""}",
 *     )
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

 return $response->withJson($data, $data->code);
});

/**
 * @OA\Get(
 *     path="/get/lieu/decrement/{idlieu}",
 *     tags={"idlieu"},
 *     summary="Permet de décrémenter la valeur en fonction de l'id du lieu",
 *     description="Permet de décrémenter la valeur en fonction de l'id du lieu",
 *     @OA\Parameter(
 *         name="idlieu",
 *         in="path",
 *         description="ID du lieu",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *             example="3"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="{"status":"success","message":"Valeur mise \u00e0 jour (-1)","code":200,"data":""}",
 *     )
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

 return $response->withJson($data, $data->code);
});

$app->run();
