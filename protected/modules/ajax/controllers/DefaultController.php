<?php

class DefaultController extends Controller
{
    public function init(){
        parent::init();
        $this->layout = '/layout/main';
    }

	public function actionIndex()
	{
		$this->render('index');
	}

    public function actionMenu(){
        $groups = Group::model()->findAll();
        foreach ($groups as $group) {
            $this->widget('application.modules.ajax.widgets.GroupBoxWidget', array('group' => $group));
        }
    }

    public function actionHome(){
        $this->redirect('/ajax/request/create');
    }

    public function actionTest(){
        echo 'test';
    }

    public function actionGroups(){
        $groups = Group::model()->findAll();
        foreach ($groups as $group) {
            $this->widget('application.modules.ajax.widgets.GroupBoxWidget', array('group' => $group));
        }
    }
}