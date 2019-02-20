<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
$app->get('/', function (Request $request, Response $response, array $args) {
	return $response->withJson([
		'status' => 'ok',
	]);
});

$app->get('/current_song/:slug/', function (Request $request, Response $response, array $args) {
	$resJSON = [];

	$resJSON['req'] = $request;
	$resJSON['res'] = $response;
	$resJSON['args'] = $args;

	return $response->withJson($resJSON);
});
