<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Lib\RadionomyUtil;

// Routes
$app->get('/', function (Request $request, Response $response, array $args) {
	return $response->withJson([
		'status' => 'ok',
	]);
});

$app->get('/current_song/{slug}', function (Request $request, Response $response, array $args) {
	$resJSON = [
		"status" => "error", "message" => "", "data" => [],
	];

	// Find current radio
	$thisRadio = RadionomyUtil::findRadio(
		$this->config['radios'],
		$args['slug']
	);

	// Return error if radio not found
	if (!$thisRadio) {
		return RadionomyUtil::radioNotFound($response, $resJSON);
	}

	// Create cache folders
	$cachePath = RadionomyUtil::getCachePath(
		$args['slug'],
		'current_song'
	);
	$fl_folder_created = RadionomyUtil::createCacheFolder(
		$cachePath
	);

	// Retur error if not created
	if (!$fl_folder_created) {
		return RadionomyUtil::folderCouldNotCreated($response, $resJSON);
	}

	$fil_json = $cachePath['fol_radio_cache'] . "cache_api.json";

	// Create the cache file
	$fl_file_created = RadionomyUtil::createCacheFile($fil_json);

	if (!$fl_file_created) {
		return RadionomyUtil::fileCouldNotCreated($response, $resJSON);
	}

	// Get cache file content
	$cache_json = file_get_contents($fil_json);

	if ($cache_json === false || empty($cache_json)) {
		$cache_json = RadionomyUtil::currentsongRadionomy($this, $thisRadio, $fil_json);
	} else {
		$cache_json = json_decode($cache_json, true);

		if (time() > $cache_json['expire_at']) {
			$cache_json = RadionomyUtil::currentsongRadionomy($this, $thisRadio, $fil_json);
		}
	}

	// Retur the JSON
	$resJSON['data'] = $cache_json;
	$resJSON['status'] = 'ok';
	return $response->withJson($resJSON);
});

$app->get('/tracklist/{slug}', function (Request $request, Response $response, array $args) {
	$resJSON = [
		"status" => "error", "message" => "", "data" => [],
	];

	// Find current radio
	$thisRadio = RadionomyUtil::findRadio(
		$this->config['radios'],
		$args['slug']
	);

	// Return error if radio not found
	if (!$thisRadio) {
		return RadionomyUtil::radioNotFound($response, $resJSON);
	}

	// Create cache folders
	$cachePath = RadionomyUtil::getCachePath(
		$args['slug'],
		'tracklist'
	);
	$fl_folder_created = RadionomyUtil::createCacheFolder(
		$cachePath
	);

	// Retur error if not created
	if (!$fl_folder_created) {
		return RadionomyUtil::folderCouldNotCreated($response, $resJSON);
	}

	$fil_json = $cachePath['fol_radio_cache'] . "cache_api.json";

	// Create the cache file
	$fl_file_created = RadionomyUtil::createCacheFile($fil_json);

	if (!$fl_file_created) {
		return RadionomyUtil::fileCouldNotCreated($response, $resJSON);
	}

	// Get cache file content
	$cache_json = file_get_contents($fil_json);

	if ($cache_json === false || empty($cache_json)) {
		$cache_json = RadionomyUtil::tracklistRadionomy($this, $thisRadio, $fil_json);
	} else {
		$cache_json = json_decode($cache_json, true);

		if (time() > $cache_json['expire_at']) {
			$cache_json = RadionomyUtil::tracklistRadionomy($this, $thisRadio, $fil_json);
		}
	}

	// Retur the JSON
	$resJSON['data'] = $cache_json;
	$resJSON['status'] = 'ok';
	return $response->withJson($resJSON);
});

$app->get('/current_audience/{slug}', function (Request $request, Response $response, array $args) {
	$resJSON = [
		"status" => "error", "message" => "", "data" => [],
	];

	// Find current radio
	$thisRadio = RadionomyUtil::findRadio(
		$this->config['radios'],
		$args['slug']
	);

	// Return error if radio not found
	if (!$thisRadio) {
		return RadionomyUtil::radioNotFound($response, $resJSON);
	}

	// Create cache folders
	$cachePath = RadionomyUtil::getCachePath(
		$args['slug'],
		'current_audience'
	);
	$fl_folder_created = RadionomyUtil::createCacheFolder(
		$cachePath
	);

	// Retur error if not created
	if (!$fl_folder_created) {
		return RadionomyUtil::folderCouldNotCreated($response, $resJSON);
	}

	$fil_json = $cachePath['fol_radio_cache'] . "cache_api.json";

	// Create the cache file
	$fl_file_created = RadionomyUtil::createCacheFile($fil_json);

	if (!$fl_file_created) {
		return RadionomyUtil::fileCouldNotCreated($response, $resJSON);
	}

	// Get cache file content
	$cache_json = file_get_contents($fil_json);

	if ($cache_json === false || empty($cache_json)) {
		$cache_json = RadionomyUtil::currentaudienceRadionomy($this, $thisRadio, $fil_json);
	} else {
		$cache_json = json_decode($cache_json, true);

		if (time() > $cache_json['expire_at']) {
			$cache_json = RadionomyUtil::currentaudienceRadionomy($this, $thisRadio, $fil_json);
		}
	}

	// Retur the JSON
	$resJSON['data'] = $cache_json;
	$resJSON['status'] = 'ok';
	return $response->withJson($resJSON);
});
