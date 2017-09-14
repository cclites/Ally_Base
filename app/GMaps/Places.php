<?php
namespace App\GMaps;

use Exception;

/**
 * Class Places
 *
 * @property
 */
class Places {

	/**
	 * Base Method for Getting Details on a Specific Place
	 *
	 * @param $place_id
	 *
	 * @return array|bool
	 */
	public static function getPlace($place_id) {

		$arguments = [ 'placeid' => $place_id ];

		try {
			$array = API::get('place/details', $arguments);
			if (empty($array['result'])) {
				throw new Exception('No place information could be found.');
			}
		}
		catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
		return $array['result'];
	}

}