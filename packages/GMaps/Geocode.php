<?php
namespace Packages\GMaps;

use Packages\GMaps\Exceptions\NoGeocodeFoundException;

/**
 * Class Geocode
 *
 */
class Geocode {

    /**
     * Base Geocode Result for an Address
     *
     * @param $address
     * @return array|bool
     * @throws \Packages\GMaps\Exceptions\NoGeocodeFoundException
     */
	public static function getResult($address) {

		$arguments = [ 'address' => $address ];
        $array = API::get('geocode', $arguments);
        if (empty($array['results'])) {
            throw new NoGeocodeFoundException('No geocode information could be found.');
        }
		return $array['results'][0];
	}

    /**
     * @param $address
     *
     * @return \Packages\GMaps\GeocodeCoordinates|bool
     * @throws \Packages\GMaps\Exceptions\NoGeocodeFoundException
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

    /**
     * @param $latitude
     * @param $longitude
     * @param string $language
     * @return mixed
     * @throws \Packages\GMaps\Exceptions\NoGeocodeFoundException
     */
	public static function reverseGeocode($latitude, $longitude, $language='en') {
		$arguments = [
			'latlng' => "$latitude,$longitude",
			'language' => $language,
		];
        $array = API::get('geocode', $arguments);
        if (empty($array['results'])) {
            throw new NoGeocodeFoundException('ReverseGeocode: No geocode information could be found.');
        }
		return $array['results'];
	}

}
