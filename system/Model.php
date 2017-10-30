<?php
/**
 * 模型基类，继承自数据库操作类 Database类
 *
 */
class Model extends Database
{
	protected $model;
	protected $table;
	public static $dbConfig = [];
	
	public function __construct()
	{
		// 连接数据库
		self::getInstance(self::$dbConfig);
			
		// 获取数据库表名
		if(! $this->table){
			// 获取模型类名称
			$this->model = get_class($this);
			// 删除类名最后的 Model字符
			$this->model = substr($this->model, 0, -5);
			
			//数据库表名与类名一致
			$this->table = strtolower($this->model);
		}
	}
}