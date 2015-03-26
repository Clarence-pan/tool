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

    public function actionQuery($id){
        if (Yii::app()->request->requestType != 'POST'){
            throw new CHttpException(500);
        }

        /** @var Request $request */
        $request = Request::model()->findByPk($id);
        if (empty($request)){
            throw new CHttpException(404);
        }

        $response = $request->doQuery();

        $this->render('detail', array('request' => $request, 'response' => $response));
    }

    public function actionCreate($groupId=Group::DEFAULT_GROUP_ID, $url='', $params='', $paramsFormat='', $method=''){
        $group = Group::model()->findByPk($groupId);
        if ($group == null){
            throw new HttpInvalidParamException("Invalid group ID: ".$groupId);
        }

        $request = Request::model()->find('url = :url ' .
            'AND params = :params ' .
            'AND paramsFormat = :paramsFormat ' .
            'AND method = :method ' .
            'AND groupId = :groupId',
            array(
                ':url' => $url,
                ':params' => $params,
                ':paramsFormat' => $paramsFormat,
                ':method' => $method,
                ':groupId' => $groupId
            ));

        if ($request == null){
            $request = new Request(array(
                'url' => $url,
                'params' => $params,
                'paramsFormat' => $paramsFormat,
                'method' => $method,
                'groupId' => $groupId
            ));
        }

        $executeQuery = (Yii::app()->request->requestType == 'POST');

        if ($executeQuery){
            $saveSuccess = $request->save();
            if ($saveSuccess){
                $response = $request->doQuery();
            }
        }

        $this->render('create', array(
            'request' => $request,
            'groupList' => Group::model()->findAll(),
            'executeQuery' => $executeQuery,
            'saveSuccess' => $saveSuccess,
            'response' => $response
        ));


    }
} 