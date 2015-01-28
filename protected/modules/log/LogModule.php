<?php

class LogModule extends CWebModule
{
	public function init()
	{
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'log.models.*',
			'log.components.*',
		));

        $moduleBaseDir = dirname(__FILE__);
        $moduleName = basename($moduleBaseDir);

        /**
         * 为了避免冲突，模块内的class使用用模块名作为namespace，此时根据此规则来加载类
         */
        spl_autoload_register(function ($class) use ($moduleBaseDir, $moduleName){
            $class = ltrim($class, "\\");
            if (!preg_match('|^'.$moduleName.'|', $class)){
                return;
            }
            $class = substr($class, strlen($moduleName));
            $tryDirs = explode(' ', '. components controllers models scripts'); //  可以在这些路径下去探测
            foreach ($tryDirs as $dir) {
                $tryFile = str_replace("\\", '/', $moduleBaseDir.'/'.$dir.'/'.$class.'.php');
                if (is_file($tryFile)){
                    require_once($tryFile);
                }
            }
        });
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			// this method is called before any module controller action is performed
			// you may place customized code here
			return true;
		}
		else
			return false;
	}
}
