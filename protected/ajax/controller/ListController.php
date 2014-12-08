<?php
/**
 * Created by PhpStorm.
 * User: clarence
 * Date: 14-12-9
 * Time: 上午1:31
 */

namespace tool\ajax\controller;
use tool\base\Controller as Controller;

class ListController extends Controller {


    public function doGetAll($param){
        $this->render('list');
    }
} 