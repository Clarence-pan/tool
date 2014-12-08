<?php
/**
 * Created by PhpStorm.
 * User: clarence
 * Date: 14-12-9
 * Time: 上午1:02
 */

namespace tool\base;


class Controller {

    /**
     * 处理参数
     * @param $get
     * @param $post
     * @param $cookie
     * @return array
     */
    public function parseParams($get, $post, $cookie){
        return array_merge($cookie, $post, $get);
    }

    /**
     * 初始化
     */
    public function init(){

    }

    public function render($view, array $params = array(), $return = false){
        $layout = $this->getLayout();
        if ($layout){
            $content = $this->renderPartial($view, $params, true);
            return $this->renderPartial($layout, array_merge($params, array('content' => $content)), $return);
        } else {
            return $this->renderPartial($view, $params, $return);
        }
    }

    /**
     * 渲染
     * @param $view string view文件名
     * @param $params
     * @param $return
     * @return string
     */
    public function renderPartial($_view, $_params, $_return){
        $_class = new \ReflectionClass($this);
        ob_start();
        if (is_array($_params)) {
            extract($_params);
        }

        require(dirname($_class->getFileName())."/../view/$_view.php");
        $_result = ob_get_contents();
        ob_end_clean();
        if ($_return){
            return $_result;
        }
        $this->afterRender($_view, $_result);
        echo $_result;
    }

    /**
     * 渲染过后，处理渲染后的内容
     * @param $view
     * @param $content
     */
    public function afterRender($view, &$content){

    }

    private $layout = 'layout/main';

    /**
     * 设置布局
     * @param $layout
     */
    public function setLayout($layout){
        $this->layout = $layout;
    }

    /**
     * 获取布局
     * @return string
     */
    public function getLayout(){
        return $this->layout;
    }
} 