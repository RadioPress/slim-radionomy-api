<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
$app->get('/', function (Request $request, Response $response, array $args) {
    // Render index view
    return $response->withJson([ 'status' => 'ok' ]);
});
