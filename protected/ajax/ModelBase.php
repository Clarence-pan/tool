<?php

class ModelBase{
    protected $_db;
    public function __construct(){
        $this->_db = new AjaxDb();
    }
    public function fillSqlArgs($sql){
        $args = $this->toAssocArray();
        return $this->_db->fillSqlArgs($sql, $args);
    }
    public function toAssocArray(){
        $arr = array();
        $clazz = new ReflectionClass($this);
        $fields = $clazz->getProperties();
        foreach ($fields as $field) {
            $fieldName = $field->getName();
            if ($fieldName[0] != '_'){
                $arr[$fieldName] = $field->getValue($this);
            }
        }
        return $arr;
    }
    public function execSql($sql){
        return $this->_db->execSql($sql);
    }
    public function queryAll($sql){
        return $this->_db->queryAll($sql);
    }
    public function queryRow($sql){
        $rows = $this->queryAll($sql);
        if (count($rows) <= 0){
            throw new Exception('No rows found by SQL: '.$sql);
        }
        return $rows[0];
    }
    public function getLastInsertId(){
        return $this->_db->getLastInsertId();
    }

}