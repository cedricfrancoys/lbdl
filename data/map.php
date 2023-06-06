<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use lbdl\Map;

list($params, $providers) = eQUal::announce([
    'description'   => 'Returns a map object with a unique token allowing to submit a score.',
    'params'        => [
        'id' =>  [
            'description'       => 'Identifier of the Map to fetch.',
            'type'              => 'many2one',
            'foreign_object'    => 'lbdl\Map',
            'required'          => true
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context', 'auth' ]
]);

/**
 * @var \equal\php\Context  $context
 * @var \equal\auth\AuthenticationManager   $auth
 */
list($context, $auth) = [ $providers['context'], $providers['auth'] ];

$user_id = $auth->userId();

// create a token for validating subsequent score submissions (valid for 1 day)
$token = $auth->createToken(['user_id' => $auth->userId(), 'map_id' => $params['id'], 'exp' => time() + 3600*24]);

$map = Map::id($params['id'])
    ->read([
        'id',
        'name',
        'pos_x',
        'pos_y',
        'created',
        'author',
        'board',
        'keys',
        'time',
        'count_games',
        'count_likes',
        'difficulty',
        'status'
    ])
    ->adapt('json')
    ->first(true);

if(!$map) {
    throw new Exception('unknonw_map', QN_ERROR_UNKNOWN_OBJECT);
}

// add like status according to current user
$map['liked'] = (bool) count(Map::search([['id', '=', $params['id']], ['liked_users_ids', 'contains', $user_id]])->ids());

$result = array_merge($map, [
        'direction'         => 12,
        'token'             => $token
    ]);

// send back basic info of the User object
$context->httpResponse()
    ->body($result)
    ->send();
