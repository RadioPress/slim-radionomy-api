<?php

use Slim\Http\Request;
use Slim\Http\Response;
use LSS\XML2Array;

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

	// Return error if radio not found
	if (!$fl_radio_found) {
		$response = $response->withStatus(404);
		$resJSON['status'] = 404;
		$resJSON['message'] = 'Radio not found';
		return $response->withJson($resJSON);
	}

	$fol_cache = SLIM_ROOT_DIR . '/cache/';
	$fol_radio_cache = $fol_cache . $args['slug'] . '/current_song/';
	$fl_folder_created = true;

	// Create folder
	if (is_dir($fol_cache) && !file_exists($fol_radio_cache)) {
		$fl_folder_created = mkdir($fol_radio_cache, 0755, true);
	} elseif (!is_dir($fol_cache)) {
		$fl_folder_created = mkdir($fol_radio_cache, 0755, true);
	}

	// Retur error if not created
	if (!$fl_folder_created) {
		$resJSON['status'] = "ERR_FOLDER_COULD_NOT_CREATED";
		return $response->withJson($resJSON);
	}

	$fil_json = $fol_radio_cache . "cache_api.json";
	$fl_file_created = true;

	// Create the cache file
	if (!file_exists($fil_json)) {
		$fl_file_created = touch($fil_json);
	}

	if (!$fl_file_created) {
		$resJSON['status'] = "ERR_FILE_COULD_NOT_CREATED";
		return $response->withJson($resJSON);
	}

	// Get cache file content
	$cache_json = file_get_contents($fil_json);

	if ($cache_json === false || empty($cache_json)) {
		$cache_json = currentsongRadionomy($thisRadio, $fil_json);
	} else {
		$cache_json = json_decode($cache_json, true);

		if (time() > $cache_json['expire_at']) {
			$cache_json = currentsongRadionomy($thisRadio, $fil_json);
		}
	}

	// Retur the JSON
	$resJSON['data'] = $cache_json;
	$resJSON['status'] = 'ok';
	return $response->withJson($resJSON);
});

function currentsongRadionomy($thisRadio, $fil_json){
	// Build Radionomy api URL
	// $xmlURL  = "http://api.radionomy.com/currentsong.cfm?";
	// $xmlURL .= "radiouid=" . $thisRadio['radioUId'];
	// $xmlURL .= "&apikey=" . $this->config['radionomy_api_key'];
	// $xmlURL .= "&callmeback=yes&type=xml&cover=yes";

	$xmlURL = "https://radionomy.letoptop.fr/currentsong/get_api.php?radiouid=" . $thisRadio['radioUId'];

	$get_data = callAPI('GET', $xmlURL, [], false);

	$api_result = XML2Array::createArray($get_data);

	$cache_json = $api_result;
	$cache_json["generated_at"] = time();

	// Calculate experation timestamp
	$callmeback = intval($cache_json['tracks']['track']['callmeback']);
	$callmeback = intval($callmeback / 1000);
	if ($callmeback < 60) $callmeback = 60;

	$cache_json['expire_at'] = $cache_json["generated_at"] + $callmeback;
	file_put_contents($fil_json, json_encode($cache_json));

	return $cache_json;
}

function callAPI($method, $url, $header = [], $data = false){
	$curl = curl_init();

	switch ($method){
		case "POST":
			curl_setopt($curl, CURLOPT_POST, 1);
			if ($data)
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			break;
		case "PUT":
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
			if ($data)
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			break;
		default:
			if ($data)
				$url = sprintf("%s?%s", $url, http_build_query($data));
	}

	// OPTIONS:
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);

	// EXECUTE:
	$result = curl_exec($curl);
	if(!$result){ die("Connection Failure"); }
	curl_close($curl);
	return $result;
}
