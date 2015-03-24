<?php

class DefaultController extends Controller
{
    public function init(){
        parent::init();
        $this->layout = '/layout/main';
    }

	public function actionIndex()
	{
        $groups = Group::model()->findAll();
		$this->render('index',array('groups' => $groups));
	}

}