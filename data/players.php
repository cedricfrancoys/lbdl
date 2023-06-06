<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use lbdl\User;

list($params, $providers) = eQual::announce([
    'description'   => 'Returns the players to display in the Hall of fame.',
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context' ]
]);

list($context) = [ $providers['context'] ];

$result = User::search([], ['sort'  => ['count_plays' => 'desc'], 'limit' => 20])
    ->read(['firstname', 'count_plays', 'count_maps'])
    ->adapt('json')
    ->get(true);

// send back basic info of the User object
$context->httpResponse()
    ->body($result)
    ->send();
