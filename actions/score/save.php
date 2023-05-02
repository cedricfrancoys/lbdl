<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use lbdl\Score;

list($params, $providers) = eQual::announce([
    'description'   => 'Stores a new score for current user on a given map.',
    'params'        => [
        'map_id' => [
            'type'              => 'many2one',
            'foreign_object'    => 'lbdl\Map',
            'description'       => "Identifier of the Map.",
            'required'          => true
        ],
        'tries' => [
            'type'              => 'integer',
            'description'       => "Number of attempts.",
            'required'          => true
        ],
        'time' => [
            'type'              => 'integer',
            'description'       => "Race time of the score in milliseconds.",
            'required'          => true
        ]
    ],
    'constants'     => ['DEFAULT_LANG', 'AUTH_SECRET_KEY'],
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
 * @var \equal\auth\AuthenticationManager   $auth
 */
list($context, $auth) = [ $providers['context'], $providers['auth'] ];

$user_id = $auth->userId();


if( !$auth->verifyToken($params['token'], constant('AUTH_SECRET_KEY')) ){
    throw new Exception('invalid_token', QN_ERROR_NOT_ALLOWED);
}
else {
    $decoded = $auth->decodeToken($params['token']);
    if($decoded['exp'] > time()) {
        throw new Exception('expired_token', QN_ERROR_NOT_ALLOWED);
    }
    if($decoded['user_id'] != $user_id || $decoded['map_id'] != $params['map_id']) {
        throw new Exception('mismatch_token', QN_ERROR_NOT_ALLOWED);
    }
}

$values = [
    'user_id'   => $user_id,
    'map_id'    => $params['map_id'],
    'time'      => $params['time'],
    'tries'     => $params['tries']
];

$result = Score::create($values)->read(['player', 'user_id', 'map_id']);

$context->httpResponse()
    ->status(201)
    ->body($result)
    ->send();
