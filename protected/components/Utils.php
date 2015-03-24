<?php

class Utils{

    public static function isAjaxRequest(){
        return Yii::app()->request->isAjaxRequest;
    }
}
 