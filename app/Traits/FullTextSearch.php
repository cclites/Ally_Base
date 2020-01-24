<?php

namespace App\Traits;

trait FullTextSearch
{

    /**
     * how to add/remove the index in a migration:
     *  - check out 2020_01_21_010325_alter_table_sms_threads_add_full_text_index for reference.php
     * 
     * how to use within a model:
     *  - check out SmsThread.php
     *  - must add $searchable array which includes all columns that will be full-searched
     * 
     * how to use within controller:
     *  - checkout CommunicaionController@threadIndex, ->fullTextSearch( $request->input( 'keyword', null ) )
    */

    function my_sanitize_string( $string )
    {
        $string = preg_replace( "/[^a-zA-Z0-9]/", "", $string );
        $string = strip_tags( $string );
        $string = addslashes( $string );
        return filter_var( $string, FILTER_SANITIZE_STRING );
    }

    /**
     * Replaces spaces with full text search wildcards
     *
     * @param string $term
     * @return string
     */
    protected function fullTextWildcards($term)
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term = str_replace($reservedSymbols, '', $this->my_sanitize_string( $term ) );

        $words = explode(' ', $term);

        foreach($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if(strlen($word) >= 3) {
                $words[$key] = '+' . $word . '*';
            }
        }

        $searchTerm = implode( ' ', $words);

        return $searchTerm;
    }

    /**
     * Scope a query that matches a full text search of term.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFullTextSearch($query, $term = null )
    {
        if( !$term ) return $query;

        $columns = implode(',',$this->searchable);

        $query->whereRaw("MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE)" , $this->fullTextWildcards($term));

        return $query;
    }
}