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
}