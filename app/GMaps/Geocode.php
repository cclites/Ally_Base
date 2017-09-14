<?php
namespace App\GMaps;

/**
 * Class Geocode
 *
 */
class Geocode {

	/**
	 * Base Geocode Result for an Address
	 *
	 * @param $address
	 *
	 * @return array|bool
	 */
	public static function getResult($address) {

		$arguments = [ 'address' => $address ];
		try {
			$array = API::get('geocode', $arguments);
			if (empty($array['results'])) {
				throw new NoGeocodeFoundException('No geocode information could be found.');
			}
		}
		catch (NoGeocodeFoundException $e) {
			echo $e->getMessage();
			return false;
		}
		return $array['results'][0];
	}

	/**
	 * @param $address
	 *
	 * @return \App\GMaps\GeocodeCoordinates|bool
	 */
	public static function getCoordinates($address) {
		if ($result = self::getResult($address)) {
			if (!empty($result['geometry']['location']['lat'])) {
				return new GeocodeCoordinates(
				    $result['geometry']['location']['lat'],
                    $result['geometry']['location']['lng']
                );
			}
		}
		return false;
	}

	public static function reverseGeocode($latitude, $longitude, $language='en') {
		$arguments = [
			'latlng' => "$latitude,$longitude",
			'language' => $language,
		];
		try {
			$array = API::get('geocode', $arguments);
			if (empty($array['results'])) {
				throw new NoGeocodeFoundException('ReverseGeocode: No geocode information could be found.');
			}
		}
		catch (NoGeocodeFoundException $e) {
			echo $e->getMessage();
			return false;
		}
		return $array['results'];
	}

}
