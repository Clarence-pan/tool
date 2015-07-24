<?php

class TestCommand extends CConsoleCommand{
    /**
     * 制造数据
     * @param string $db 数据库名称
     * @param int $count 数据个数
     */
    public function actionMakeDb($db, $count=1000){
        /**
         * @var $db CDbConnection
         */
        $db = Yii::app()->dbAjax;
        $transaction = $db->beginTransaction();
        try{

            $transaction->commit();
        }catch (Exception $e){
            $transaction->rollback();
        }
    }

    public function actionEvents(){
        require __DIR__.'/test/'.strtolower(substr(__FUNCTION__, strlen('action'))).'.php';
    }
} 