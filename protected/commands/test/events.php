<?php

class A extends CComponent{
    public function onClick($e){
        $this->raiseEvent(__FUNCTION__, $e);
    }
}

$a = new A();
$a->onClick = function($e){
    echo 'On clicked!';
    var_dump($e); // $e->sender = $a , $e->params = null
};

$a->onClick(new CEvent($a));