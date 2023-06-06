<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use lbdl\Map;

list($params, $providers) = eQual::announce([
    'description'   => 'Returns a list of all maps, filtered on matching query if given.',
    'params'        => [
        'q' =>  [
            'description'       => 'Query for filtering results.',
            'type'              => 'string',
            'required'          => ''
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
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

$domain = [
    ['status', '=', 'published']
];

if(strlen($params['q'])) {
    $domain[] = ['name', 'ilike', "%{$params['q']}%"];
}

$maps = Map::search($domain, ['limit' => 10])
    ->read([
            'id',
            'name',
            'created',
            'creator' => ['id', 'firstname'],
            'count_games',
            'count_likes',
            'difficulty'
        ])
    ->adapt('json')
    ->get(true);


// send back basic info of the User object
$context->httpResponse()
    ->body($maps)
    ->send();
