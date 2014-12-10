<?php


$data = array(
    'a' => array(
        'id' => 1000,
        'b' => array(
            array( 'b.id' => 1 ),
            array( 'b.id' => 2 ),
            array( 'b.id' => 3 ),
        )
    ),
    'b' => array(
        array(
            'id' => 1,
            'value' => 'oo'
        ) ,
        array(
            'id' => 2,
            'value' => 'pp'
        ) ,
        array(
            'id' => 3,
            'value' => 'qq'
        ) ,
    )
);

$result = array(
    1000 =>
        array(
            1 => 'oo',
            2 => 'pp',
            3 => 'qq'
        )
);
$result[a.id][b.id]


