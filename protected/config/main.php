<?php
/**
 * Created by PhpStorm.
 * User: clarence
 * Date: 14-12-9
 * Time: 上午12:16
 */

return array(
    'route' => array(
        '/eval' => array(
            'GET,HEAD' => 'util/eval/GET/view',
            'POST' => 'util/eval/POST/eval'
        ),

        // basic route:
        '/<module>/<controller>/<action>(/<id>)?' => array(
            'GET,HEAD' => '<module>/<controller>/GET/<action>',
            'POST,PUT,DELETE' => '<module>/<controller>/<method>/<action>'
        )
    )
);
