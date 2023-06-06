<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use lbdl\Map;

list($params, $providers) = eQual::announce([
    'description'   => 'Create a clone of a given map.',
    'params'        => [
        'id' =>  [
            'description'       => 'Identifier of the Map to clone.',
            'type'              => 'many2one',
            'foreign_object'    => 'lbdl\Map',
            'required'          => true
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context', 'auth' ]
]);

/**
 * @var \equal\php\Context  $context
  * @var \equal\auth\AuthenticationManager  $auth
 */
list($context, $auth) = [ $providers['context'], $providers['auth'] ];

$user_id = $auth->userId();

$map = Map::id($params['id'])->read(['id', 'name', 'pos_x', 'pos_y', 'board', 'keys', 'time', 'difficulty'])->first();

if(!$map) {
    throw new Exception('unknown_map', QN_ERROR_UNKNOWN_OBJECT);
}

$new_map = Map::create([
        'name'          => $map['name'].' - copy',
        'pos_x'         => $map['pos_x'],
        'pos_y'         => $map['pos_y'],
        'board'         => $map['board'],
        'keys'          => $map['keys'],
        'time'          => $map['time'],
        'difficulty'    => $map['difficulty']
    ])
    ->read(['id'])
    ->first(true);

$context->httpResponse()
    ->status(201)
    ->body($new_map)
    ->send();
