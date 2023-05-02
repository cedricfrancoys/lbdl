<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use lbdl\Map;

list($params, $providers) = eQual::announce([
    'description'   => 'Stores a new score for current user on a given map.',
    'params'        => [
        'id' =>  [
            'description'       => 'Identifier of the Map to fetch.',
            'type'              => 'many2one',
            'foreign_object'    => 'lbdl\Map',
            'required'          => true
        ],
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
 */
list($context) = [ $providers['context'] ];


// send back basic info of the User object
$context->httpResponse()
    ->body($result)
    ->send();
