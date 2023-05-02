<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace lbdl;

use equal\orm\Model;

class Map extends Model {

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'string',
                'description'       => 'Name of the map.',
                'multilang'         => true,
                'required'          => true
            ],

            'author' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => 'Name of the author (relevant for non-registered users / legacy).',
                'function'          => 'calcAuthor'
            ],

            'pos_x' => [
                'type'              => 'integer',
                'description'       => "Initial position on the horizontal axis.",
                'default'           => 0
            ],

            'pos_y' => [
                'type'              => 'integer',
                'description'       => "Initial position on the vertical axis.",
                'default'           => 0
            ],

            'board' => [
                'type'              => 'string',
                'usage'             => 'text/plain',
                'description'       => "Descriptor of the map."
            ],

            'keys' => [
                'type'              => 'integer',
                'description'       => "Initial number of keys.",
                'default'           => 0
            ],

            'time' => [
                'type'              => 'integer',
                'description'       => "Duration before timeout.",
                'default'           => 0
            ],

            'count_games' => [
                'type'              => 'integer',
                'description'       => "Counter holding the number of times map was played.",
                'default'           => 0
            ],

            'count_likes' => [
                'type'              => 'integer',
                'description'       => "Counter holding the number of times map was played.",
                'default'           => 0
            ],

            'count_won' => [
                'type'              => 'integer',
                'description'       => "Counter holding the number of times map was won.",
                'default'           => 0
            ],

            /*
            'difficulty' => [
                'type'              => 'computed',
                'result_type'       => 'integer',
                'description'       => "Counter holding the number of times map was won.",
                'function'          => 'calcDifficulty',
                'store'             => true
            ]
            */

            'difficulty' => [
                'type'              => 'integer',
                'description'       => "Counter holding the number of times map was won.",
                'default'           => 1
            ]


        ];
    }

    public static function calcAuthor($self) {
        $result = [];
        $self->read(['creator' => ['firstname']]);
        foreach($self as $id => $map) {
            $result[$id] = $map['creator']['firstname'];
        }
        return $result;
    }

    public static function calcDifficulty($self) {
        $result = [];
        $self->read(['count_games', 'count_won']);

        foreach($self as $id => $map) {
            $rate = ($map['count_games'])?$map['count_won']/$map['count_games']:1;
            $result[$id] = 1;
            if ($rate <= 0.5) {
                $result[$id] = 2;
            }
            elseif ($rate <= 0.25) {
                $result[$id] = 3;
            }
            elseif ($rate <= 0.15) {
                $result[$id] = 4;
            }
            elseif ($rate <= 0.05) {
                $result[$id] = 5;
            }
        }
        return $result;
    }

}