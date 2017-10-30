<?php
/**
 * 视图基类
 *
 */
class View
{
	protected $variables = array();
	protected $_controller;
	protected $_action;
	
	public function __construct($controller, $action)
	{
		$this->_controller = strtolower($controller);
		$this->_action = strtolower($action);
	}
	
	// 分配变量
	public function assign($name, $value)
	{
		$this->variables[$name] = $value;
	}
	
	// 渲染显示
	public function render()
	{
		extract($this->variables);
		$defaultHeader = APP_PATH . 'application/views/header.php';
		$defaultFooter = APP_PATH . 'application/views/footer.php';
		
		$controllerHader = APP_PATH . 'application/views/' . $this->_controller . '/header.php';
		$controllerFooter = APP_PATH . 'application/views/' . $this->_controller . 'footer.php';
		$controllerLayout = APP_PATH . 'application/views/' . $this->_controller . '/' . $this->_action . '.php';
		
		// 页头文件
		if(file_exists($controllerHader)){
			include $controllerHader;
		}else {
			include $defaultHeader;
		}
		
		// 中间内容
		include $controllerLayout;
		
		// 页脚文件
		if(file_exists($controllerFooter)){
			include $controllerFooter;
		}else {
			include $defaultFooter;
		}		
	}
	
}