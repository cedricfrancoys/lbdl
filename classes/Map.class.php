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
                'type'              => 'computed',
                'result_type'       => 'integer',
                'description'       => "Counter holding the number of times map was played.",
                'help'              => 'This is a computed field that depends on `liked_users_ids`.',
                'function'          => 'calcCountLikes',
                'store'             => true
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
            ],

            'liked_users_ids' => [
                'type'              => 'many2many',
                'foreign_object'    => 'lbdl\User',
                'foreign_field'     => 'liked_maps_ids',
                'rel_table'         => 'lbdl_rel_like_map_user',
                'rel_foreign_key'   => 'user_id',
                'rel_local_key'     => 'map_id',
                'description'       => 'List of users that liked the map.',
                'dependencies'      => ['count_likes']
            ],

            'status' => [
                'type'              => 'string',
                'selection'         => [
                    'draft',
                    'published'
                ],
                'default'           => 'draft'
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

    public static function calcCountLikes($self) {
        $result = [];
        $self->read(['liked_users_ids']);
        foreach($self as $id => $map) {
            $result[$id] = count($map['liked_users_ids']);
        }
        return $result;
    }

    public static function canupdate($om, $self, $values) {
        /** @var \equal\auth\AuthenticationManager */
        $auth = $om->getContainer()->get('auth');
        $access = $om->getContainer()->get('access');

        $user_id = $auth->userId();

        if($user_id == QN_ROOT_USER_ID || in_array(1, $access->getUserGroups($user_id))) {
            return [];
        }

        $self->read(['status', 'creator']);

        $editable_fields = ['name','pos_x','pos_y','board','keys','time','difficulty'];
        if( count(array_diff(array_keys($values), $editable_fields)) > 0 ) {
            return ['id' => ['disallowed_field' => 'Only editable fields can be updated by user.']];
        }

        $allowed = ['count_games', 'count_likes', 'count_won'];

        if(count(array_diff(array_keys($values), $allowed)) <= 0) {
            return [];
        }

        foreach($self as $id => $map) {
            if($map['creator'] != $user_id) {
                return ['id' => ['not_allowed' => 'Only owner can update a map.']];
            }
            if($map['status'] != 'draft') {
                return ['id' => ['not_allowed' => 'Only draft maps can be updated.']];
            }
        }

        return [];
    }
}