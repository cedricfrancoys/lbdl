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
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'access'        => [
        'visibility'    => 'public'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context' ]
]);

/**
 * @var \equal\php\Context  $context
 */
list($context) = [ $providers['context'] ];

$map = Map::id($params['id'])->read(['count_games'])->first();

if($map) {
    Map::id($params['id'])->update(['count_games' => ++$map['count_games']]);
}

$context->httpResponse()
    ->status(204)
    ->send();
