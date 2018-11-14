<?php
namespace Packages\GMaps;

use Packages\GMaps\Exceptions\UnexpectedResponseException;

class API {

	// Base URL to Google Places API
	protected static $url = "https://maps.googleapis.com/maps/api";
	protected static $backup;

	// Project API Key (required from Google Developer Console)
	protected static $key;

	// Debug purposes: get the last URL
	public static $last_url;

	public static function setBaseURL($url) {
		self::$backup = self::$url;
		self::$url = $url;
	}

	public static function resetBaseURL() {
		if (self::$backup) self::$url = self::$backup;
	}

	public static function getBaseURL() {
		return self::$url;
	}

	public static function setKey($key) {
		self::$key = $key;
	}

	public static function getKey() {
		return self::$key;
	}

    /**
     * Base GET method for communicating with Places API
     *
     * @param $resource
     * @param array $parameters
     * @param string $format
     * @param int $expectedResponse
     *
     * @return mixed
     * @throws \Packages\GMaps\Exceptions\UnexpectedResponseException
     */
	public static function get($resource, $parameters = array(), $format = 'json', $expectedResponse = 200, $addKey = true) {

		// build url from parameters
		if ($addKey) $parameters['key'] = self::$key;
		$url = self::$url . '/' . $resource . '/' . $format . '?' . http_build_query($parameters);

		// Debug
		self::$last_url = $url;

		// create curl resource
		$ch = curl_init();

		// set url
		curl_setopt($ch, CURLOPT_URL, $url);

		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Uncomment to skip verification, can cause errors on Windows Hosts
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		// $output contains the output string
		$json = curl_exec($ch);

		// get HTTP response code
		$response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// close curl resource to free up system resources
		curl_close($ch);

		if ($response != $expectedResponse) throw new UnexpectedResponseException('Expected response code '. $expectedResponse .' but received '.$response.'. Server replied: '.$json);
        
		// decode json into array and return
		return json_decode($json, true);
	}
}