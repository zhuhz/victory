<?php
/**
 * easyphp框架核心
 *
 */
class Easyphp
{
	protected $_config = [];
	
	public function __construct($config)
	{
		$this->_config = $config;
	}
	
	// 运行程序
	public function run()
	{
		spl_autoload_register(array($this, 'loadClass'));
		$this->setReporting();
		$this->removeMagicQuotes();
		$this->unregisterGlobals();
		$this->setDbConfig();
		$this->route();
	}
	
	// 路由处理
	public function route()
	{
		$controllerName = $this->_config['defaultController'];
		$actionName = $this->_config['defaultAction'];
		$param = array();
		
		$url = $_SERVER['REQUEST_URI'];
		// 清除 ?之后的内容
		$position = strpos($url, '?');
		$url = $position === false ? $url : substr($url, 0, $position);
		// 删除前后的”/“
		$url = trim($url, '/');

		if($url){
			// 使用“/”分隔字符串，并保存在数组中
			$urlArray = explode('/', $url);
			// 删除空的数组元素
			$urlArray = array_filter($urlArray);
			
			// 获取控制器名
			$controllerName = ucfirst($urlArray[0]);
			
			// 获取动作名
			array_shift($urlArray);
			$actionName = $urlArray ? $urlArray[0] : $actionName;
			
			// 获取 URL参数
			array_shift($urlArray);
			$param = $urlArray ? $urlArray : array();
		}

		// 判断控制器和操作是否存在
		$controller = $controllerName . 'Controller';
		if(! class_exists($controller)){
			exit($controllerName . '控制器不存在');
		}
		if(! method_exists($controller, $actionName)){
			exit($actionName . '方法不存在');
		}
		
		// 如果控制器和操作名存在，则实例化控制器
		$dispatch = new $controller($controllerName, $actionName);
		
		// $dispatch保存控制器实例化后的对象，我们就可以调用它的方法
		// 以下等同于：$dispatch->$actionName($param)
		call_user_func_array(array($dispatch, $actionName), $param);
	}
	
	// 检测开发环境
	public function setReporting()
	{
		if(APP_DEBUG === true){
			error_reporting(E_ALL);
			ini_set('display_errors', 'on');
		}else{
			error_reporting(E_ALL);
			ini_set('display_errors', 'off');
			ini_set('log_errors', 'on');
		}
	}
	
	// 删除敏感字符
	public function stripSlashesDeep($value)
	{
		$value = is_array($value) ? array_map(array($this, 'stripslashes'), $value) : stripslashes($value);
		return $value;
	}
	
	// 检测敏感字符并删除
	public function removeMagicQuotes()
	{
		if(get_magic_quotes_gpc()){
			$_GET = isset($_GET) ? $this->stripSlashesDeep($_GET) : '';
			$_POST = isset($_POST) ? $this->stripSlashesDeep($_POST) : '';
			$_COOKIE = isset($_COOKIE) ? $this->stripSlashesDeep($_COOKIE) : '';
			$_SESSION = isset($_SESSION) ? $this->stripSlashesDeep($_SESSION) : '';
		}
	}
	
	// 检测自定义全局变量并移除
	// PHP6 register_globals选项已删除
	// 参考：http://php.net/manual/zh/faq.using.php#faq.register-globals
	public function unregisterGlobals()
	{
		if(ini_get('register_globals')){
			$array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
			foreach($array as $value){
				foreach($GLOBALS[$value] as $key => $var){
					if($var === $GLOBALS[$key]){
						unset($GLOBALS[$key]);
					}
				}
			}
		}
	}
	
	// 配置数据库信息
	public function setDbConfig()
	{
		if($this->_config['db']){
			Model::$dbConfig = $this->_config['db'];
		}
	}
	
	// 自动加载控制器和模型类
	public static function loadClass($class)
	{
		$frameworks = __DIR__ . '/' . $class . '.php';
		$controllers = APP_PATH . 'application/controllers/' . $class . '.php';
		$models = APP_PATH . 'application/models/' . $class . '.php';

		if(file_exists($frameworks)){
			// 加载框架核心类
			include $frameworks;
		}elseif(file_exists($controllers)){
			// 加载应用控制器类
			include $controllers;
		}elseif(file_exists($models)){
			// 加载应用模型类
			include $models;
		}else{
			// 错误代码
		}
	}
}