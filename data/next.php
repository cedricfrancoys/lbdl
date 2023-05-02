<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use lbdl\Map;

list($params, $providers) = eQUal::announce([
    'description'   => 'Returns the ID of the next available map, given a reference map and an offset.',
    'params'        => [
        'id' =>  [
            'description'       => 'Identifier of the reference Map.',
            'type'              => 'many2one',
            'foreign_object'    => 'lbdl\Map',
            'default'           => 1
        ],
        'offset' =>  [
            'description'       => 'Value givving directon and offset (positive = more recent, negative = older).',
            'type'              => 'integer',
            'default'           => 1
        ],

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

$result = [
    'id' => 1
];

$limit = abs($params['offset']);
$operator = ($params['offset'] > 0)?'>':'<';
$sort = ($params['offset'] > 0)?'asc':'desc';

$map = Map::search(['id', $operator, $params['id']], ['limit' => $limit, 'sort'  => ['id' => $sort]])->read(['id'])->last();

if($map) {
    $result['id'] = $map['id'];
}

$context->httpResponse()
    ->body($result)
    ->send();
