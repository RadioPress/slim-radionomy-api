<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
$app->get('/', function (Request $request, Response $response, array $args) {
	return $response->withJson([
		'status' => 'ok',
	]);
});

$app->get('/current_song/{slug}', function (Request $request, Response $response, array $args) {
	$resJSON = [];

	// Init vars
	$radios = $this->config['radios'];
	$thisRadio = null;
	$fl_radio_found = false;

	// Try to find radio by slug
	foreach ($radios as $radio) {
		if ($radio['slug'] == $args['slug']){
			$fl_radio_found = true;
			$thisRadio = $radio;
		}
	}

	if (!$fl_radio_found) {
		// If not found return 404 error
		$response = $response->withStatus(404);
		$resJSON['status'] = 404;
		$resJSON['message'] = 'Radio not found';
	} else {
		if (is_dir(SLIM_ROOT_DIR . '/cache/' . $args['slug'])) {
			mkdir(SLIM_ROOT_DIR . '/cache/' . $args['slug'], 0755, true);
		}
		$resJSON['status'] = 'ok';
		$resJSON['message'] = SLIM_ROOT_DIR . '/cache/' . $args['slug'];
	}

	// Retur the JSON
	return $response->withJson($resJSON);
});
