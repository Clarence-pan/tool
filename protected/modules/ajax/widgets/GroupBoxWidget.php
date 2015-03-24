<?php


class GroupBoxWidget extends CWidget{
    /**
     * @var Group
     */
    public $group;

    public $requests;

    public function init(){
        $this->requests = $this->group->requests;
    }

    public function run(){
        $this->render('group_box');
    }

} 