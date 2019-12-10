<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$app = new \Slim\App;

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


