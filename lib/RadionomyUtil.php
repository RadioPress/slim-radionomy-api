<?php
namespace Lib;

use LSS\XML2Array;

class RadionomyUtil {
	public static function findRadio($radios = [], $radioSlug = "") {
		$thisRadio = null;

		foreach ($radios as $radio) {
			if ($radio['slug'] == $radioSlug){
				$thisRadio = $radio;
			}
		}

		return ($thisRadio === null) ? false : $thisRadio;
	}

	public static function getCachePath($radioSlug, $functionSlug) {
		$fol_cache = SLIM_ROOT_DIR . '/cache/';
		$fol_radio_cache = $fol_cache . $radioSlug . '/'. $functionSlug .'/';

		return [
			"fol_cache" => $fol_cache,
			"fol_radio_cache" => $fol_radio_cache
		];
	}

	public static function createCacheFolder($cachePath) {
		$fl_folder_created = true;

		// Create folder
		if (is_dir($cachePath['fol_cache']) && !file_exists($cachePath['fol_radio_cache'])) {
			$fl_folder_created = mkdir($cachePath['fol_radio_cache'], 0755, true);
		} elseif (!is_dir($cachePath['fol_cache'])) {
			$fl_folder_created = mkdir($cachePath['fol_radio_cache'], 0755, true);
		}

		return $fl_folder_created;
	}

	public static function createCacheFile($fil_json) {
		$fl_file_created = true;

		if (!file_exists($fil_json)) {
			$fl_file_created = touch($fil_json);
		}

		return $fl_file_created;
	}

	public static function radioNotFound($response, $resJSON) {
		$response = $response->withStatus(404);
		$resJSON['status'] = 404;
		$resJSON['message'] = 'Radio not found';
		return $response->withJson($resJSON);
	}

	public static function folderCouldNotCreated($response, $resJSON) {
		$resJSON['status'] = "ERR_FOLDER_COULD_NOT_CREATED";
		return $response->withJson($resJSON);
	}

	public static function fileCouldNotCreated($response, $resJSON) {
		$resJSON['status'] = "ERR_FILE_COULD_NOT_CREATED";
		return $response->withJson($resJSON);
	}

	public static function currentsongRadionomy($app, $thisRadio, $fil_json){
		// Build Radionomy api URL
		$xmlURL  = "http://api.radionomy.com/currentsong.cfm?";
		$xmlURL .= "radiouid=" . $thisRadio['radioUId'];
		$xmlURL .= "&apikey=" . $app->config['radionomy_api_key'];
		$xmlURL .= "&callmeback=yes&type=xml&cover=yes";

		// $xmlURL = "https://radionomy.letoptop.fr/currentsong/get_api.php?radiouid=" . $thisRadio['radioUId'];

		$get_data = RadionomyUtil::callAPI('GET', $xmlURL, [], false);

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

	public static function tracklistRadionomy($app, $thisRadio, $fil_json){
		// Build Radionomy api URL
		$xmlURL  = "http://api.radionomy.com/tracklist.cfm?";
		$xmlURL .= "radiouid=" . $thisRadio['radioUId'];
		$xmlURL .= "&apikey=" . $app->config['radionomy_api_key'];
		$xmlURL .= "&amount=50&callmeback=yes&type=xml&cover=yes";

		// $xmlURL = "https://radionomy.letoptop.fr/tracklist/get_api.php?radiouid=" . $thisRadio['radioUId'];

		$get_data = RadionomyUtil::callAPI('GET', $xmlURL, [], false);

		$api_result = XML2Array::createArray($get_data);

		$cache_json = $api_result;
		$cache_json["generated_at"] = time();

		// Calculate experation timestamp
		$cache_json['expire_at'] = $cache_json["generated_at"] + (5.1 * 60);
		file_put_contents($fil_json, json_encode($cache_json));

		return $cache_json;
	}

	public static function currentaudienceRadionomy($app, $thisRadio, $fil_json){
		// Build Radionomy api URL
		$xmlURL  = "http://api.radionomy.com/currentaudience.cfm?";
		$xmlURL .= "radiouid=" . $thisRadio['radioUId'];
		$xmlURL .= "&apikey=" . $app->config['radionomy_api_key'];
		$xmlURL .= "&type=xml";

		$api_result = RadionomyUtil::callAPI('GET', $xmlURL, [], false);

		$cache_json["audience"] = intval($api_result);
		$cache_json["generated_at"] = time();

		// Calculate experation timestamp
		$cache_json['expire_at'] = $cache_json["generated_at"] + (5.1 * 60);
		file_put_contents($fil_json, json_encode($cache_json));

		return $cache_json;
	}

	public static function callAPI($method, $url, $header = [], $data = false){
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
}
