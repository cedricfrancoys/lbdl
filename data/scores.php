<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use lbdl\Score;

list($params, $providers) = eQual::announce([
    'description'   => 'Returns the scores according to given map.',
    'params'        => [
        'id' =>  [
            'description'       => 'Identifier of the Map for which to fetch the scores.',
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
    'providers'     => [ 'context' ]
]);

list($context) = [ $providers['context'] ];

$result = Score::search(['map_id', '=', $params['id']], ['sort'  => ['time' => 'asc'], 'limit' => 20])
    ->read(['map_id', 'tries', 'time', 'player', 'user_id' => ['id', 'name']])
    ->adapt('json')
    ->get(true);

// send back basic info of the User object
$context->httpResponse()
    ->body($result)
    ->send();
