<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VisitEditReason extends Model
{
    protected $guarded = ['id'];

    const NONEVVVDEFAULT = 910;

    /**
     * the default action for non-verified shifts
     *
     * @return array
     */
    public static function nonEvvDefault()
    {
        return self::NONEVVVDEFAULT;
    }

    // /**
    //  * get an array of the codes for every edit reason
    //  *
    //  * @return array
    //  */
    // public static function acceptedCodes()
    // {
    //     return array_map( function( $r ){ return $r[ 'code' ]; }, self::REASONS );
    // }

    // /**
    //  * get an array of the descriptions for every edit reason
    //  *
    //  * @return array
    //  */
    // public static function acceptedDescriptions()
    // {
    //     return array_map( function( $r ){ return $r[ 'description' ]; }, self::REASONS );
    // }

    // /**
    //  * take a known code and return the description
    //  *
    //  * @return array
    //  */
    // public static function findDescriptionByCode( $code )
    // {
    //     $index = array_search( $code, self::acceptedCodes() );
    //     return $index ? self::REASONS[ $index ][ 'description' ] : false;
    // }

    // /**
    //  * take a known description and return the code
    //  *
    //  * @return array
    //  */
    // public static function findCodeByDescription( $description )
    // {
    //     $index = array_search( $description, self::acceptedDescriptions() );
    //     return $index ? self::REASONS[ $index ][ 'code' ] : false;
    // }


    public function getFormattedNameAttribute()
    {
        return $this->code . ": " . $this->description;
    }
}
