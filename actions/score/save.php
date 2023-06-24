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
        'player' => [
            'type'              => 'string',
            'description'       => "Name to use for guest user."
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
        ],
        'trace' => [
            'type'              => 'array',
            'description'       => "Trace of the game, for replay.",
            'default'           => []
        ],
        'token' => [
            'type'              => 'string',
            'description'       => "Token relayed from a request to `?get=lbdl_map`. Used to validate the submission.",
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

// creation of a score requires a validation token
if( !$auth->verifyToken($params['token'], constant('AUTH_SECRET_KEY')) ){
    throw new Exception('invalid_token', QN_ERROR_NOT_ALLOWED);
}
else {
    $decoded = $auth->decodeToken($params['token']);
    $payload = $decoded['payload'];
    if($payload['exp'] < time()) {
        throw new Exception('expired_token', QN_ERROR_NOT_ALLOWED);
    }
    if($payload['user_id'] != $user_id || $payload['map_id'] != $params['map_id']) {
        throw new Exception('mismatch_token', QN_ERROR_NOT_ALLOWED);
    }
}

$domain = [['map_id', '=', $params['map_id']]];

if($user_id == 0) {
    $domain[] = ['player', '=', $params['player']];
}
else {
    $domain[] = ['user_id', '=', $user_id];
}

// search for an existing entry for given map
$score = Score::search($domain)->read(['id', 'tries', 'time'])->first();

if($score) {
    // increment number of tries
    $values = [
        'tries' => ($score['tries'] + $params['tries'])
    ];
    // store time only if improved
    if($score['time'] > $params['time']) {
        $values['time'] = $params['time'];
        $values['trace'] = serialize($params['trace']);
    }

    $result = Score::id($score['id'])
        ->update($values)
        ->first(true);
}
else {
    $values = [
        'user_id'   => $user_id,
        'map_id'    => $params['map_id'],
        'time'      => $params['time'],
        'tries'     => $params['tries'],
        'trace'     => serialize($params['trace'])
    ];
    if($user_id == 0) {
        $values['player'] = $params['player'];
    }
    $result = Score::create($values)->first(true);
}

$context->httpResponse()
    ->status(200)
    ->body($result)
    ->send();
