<?php
namespace App\GMaps;

/**
 * Class Autocomplete
 *
 */
class Autocomplete {

	/**
	 * @param string $countrycode
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function getAllCities( $countrycode = 'us' ) {

		$cities = array();

		foreach ( range( 'A', 'Z' ) as $char ) {

			$results = API::get('place/autocomplete', array(
				'input' => $char,
				'types' => '(cities)',
				'components' => 'country:'.strtolower($countrycode),
				'language' => 'de',
			));

			foreach($results['predictions'] as $prediction) {
				$city = explode(',', $prediction['description'])[0];
				if (!in_array($city, $cities)) {
					$cities[] = $city;
				}
			}

			// Sleep 50ms between letters to prevent excess load
			usleep(50000);

		}

		return $cities;
	}

}