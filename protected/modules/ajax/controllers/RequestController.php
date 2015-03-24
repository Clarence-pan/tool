<?php


class RequestController extends Controller {
    public function init(){
        parent::init();
        $this->layout = '/layout/main';
    }

    public function actionIndex(){
        $requests = Request::model()->findAll();
        $this->render('index', array('requests' => $requests));
    }

    public function actionDetail($id){
        $request = Request::model()->findByPk($id);
        if (empty($request)){
            throw new CHttpException(404);
        }

        if (Utils::isAjaxRequest()){
            $this->renderText(json_encode($request));
            return;
        }

        $this->render('detail', array('request' => $request));
    }
} 