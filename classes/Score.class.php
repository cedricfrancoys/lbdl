<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace lbdl;

use equal\orm\Model;

class Score extends Model {

    public static function getColumns() {
        return [

            'player' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => "Name of the player.",
                'function'          => 'calcPlayer',
                'store'             => true
            ],

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'description'       => "Player identifier (0 means guest user).",
                'default'           => 0
            ],

            'map_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'lbdl\Map',
                'description'       => "Map.",
                'default'           => 0
            ],

            'tries' => [
                'type'              => 'integer',
                'description'       => "Number of attempts.",
                'required'          => true
            ],

            'time' => [
                'type'              => 'integer',
                'description'       => "Duration of the score.",
                'required'          => true
            ]

        ];
    }


    public static function calcPlayer($self) {
        $result = [];
        $self->read(['user_id' => ['firstname', 'lastname']]);
        foreach($self as $id => $score) {
            if($score['user_id']) {
                $result[$id] = $score['user_id']['firstname'].( (strlen($score['user_id']['lastname']))?' '.$score['user_id']['lastname']:'');
            }
        }
        return $result;
    }

}