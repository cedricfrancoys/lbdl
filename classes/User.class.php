<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace lbdl;

class User extends \core\User {

    public static function getColumns() {
        return [

            'count_plays' => [
                'type'              => 'computed',
                'result_type'       => 'integer',
                'description'       => "Number of plays of the user.",
                'function'          => 'calcCountPlays',
                'store'             => true
            ],

            'count_maps' => [
                'type'              => 'computed',
                'result_type'       => 'integer',
                'description'       => "Number of maps solved by the user.",
                'help'              => "Number of maps that have been played by the user and for which there is a score.",
                'function'          => 'calcCountMaps',
                'store'             => true
            ]

        ];
    }

    public static function calcCountPlays($self) {
        $result = [];
        foreach($self as $id => $user) {
            $scores = Score::search(['user_id', '=', $id])->read(['tries'])->get(true);
            $result[$id] = array_reduce($scores, function ($c, $a) { return $c+$a['tries'];}, 0);
        }
        return $result;
    }

    public static function calcCountMaps($self) {
        $result = [];
        foreach($self as $id => $user) {
            $scores = Score::search(['user_id', '=', $id])->read(['map_id']);
            $map_maps_ids = [];
            foreach($scores as $score) {
                $map_maps_ids[$score['map_id']] = true;
            }
            $result[$id] = count($map_maps_ids);
        }
        return $result;
    }

}