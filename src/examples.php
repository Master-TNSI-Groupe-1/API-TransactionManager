<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use OpenApi\Annotations as OA;


require '../vendor/autoload.php';

$app = new \Slim\App;


/**
 * @OA\Post(
 *     path="/get/event/{id}",
 *     @OA\Parameter(
 *      name="id",
 *      in="path",
 *      description="ID de l'evenement",
 *      required=true,
 *      @OA\Schema(type="integer")
 *      ),
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="id",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="name",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="endroit",
 *                     type="string"
 *                 ),
 *                 example={"id": 10, "name": "Déjeuner", "endroit": "RU Mont Houy"}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successfully added"
 *     )
 * )
*/
$app->get('/get/event/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $response->getBody()->write("$id shown.");

    return $response;
});

/**
 * @OA\Get(
 *     path="/get/allevents",
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="id",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="name",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="endroit",
 *                     type="string"
 *                 ),
 *                 example={"id": 10, "name": "Déjeuner", "endroit": "RU Mont Houy"}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successfully added"
 *     )
 * )
*/

$app->get('/get/allevents', function (Request $request, Response $response) {
    $response->getBody()->write("All events");

    return $response;
});




$app->get('/get/champion/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Toi, $name tu es un champion !!");

    return $response;
});

$app->get('/get/champion', function (Request $request, Response $response) {
    $response->getBody()->write("Vous étes les champions !!!!");

    return $response;
});


$app->run();


