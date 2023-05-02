<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => 'Returns the available skins based on folders present in the skins directory.',
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context' ]
]);

list($context) = [ $providers['context'] ];

$result = ['default'];

foreach(glob(QN_BASEDIR."/public/lbdl/skins/*") as $file) {
    $name = basename($file);
    if($name != 'default') {
        $result[] = $name;
    }
}

$context->httpResponse()
    ->body($result)
    ->send();
