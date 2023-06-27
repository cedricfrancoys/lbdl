<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use lbdl\Map;

list($params, $providers) = eQual::announce([
    'description'   => 'Add a map from the list of liked map for the current user.',
    'params'        => [
        'id' =>  [
            'description'       => 'Identifier of the Map liked by the user.',
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

if($user_id > 0) {
    $auth->su();
    Map::id($params['id'])->update(['liked_users_ids' => [+$user_id]]);
}

$context->httpResponse()
    ->status(204)
    ->send();
