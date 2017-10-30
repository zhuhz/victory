<?php
/**
 * 数据库单例类
 *
 */
class Database
{
	// 声明 $instance为私有静态类型，用于保存当前类实例化后的对象
	private static $instance = null;
	// 数据库连接句柄
	protected static $db = null;		
	// 查询过滤条件
	private $filter = ''; 
	
	// 构造方法声明为私有方法，禁止外部程序使用 new关键字实例化
	private function __construct($config = array())
	{
		try {
			$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8', $config['host'], $config['dbname']);
			$option = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
			self::$db = new PDO($dsn, $config['username'], $config['password'], $option);
		} catch (Exception $e) {
			exit('错误：' . $e->getMessage());
		}
	}
	
	// 这是获取当前类对象的唯一方式
	public static function getInstance($config = array())
	{
		// 检查对象是否已经存在，不存在则实例化后保存到 $instance属性
		if(self::$instance == null){
			self::$instance = new self($config);
		}
		return self::$instance;
	}
	
	// 获取数据库句柄方法
	public function db()
	{
		return self::$db;
	}
	
	// 声明成私有方法，禁止克隆对象
	private function __clone(){}
	
	// 声明成私有方法，禁止重建对象
	private function __wakeup(){}		
	
	// 查询条件
	public function where($where = array())
	{
		if(isset($where)){
			$this->filter .= ' WHERE ';
			$this->filter .= implode(' ', $where);
		}

		return $this;
	}
	
	// 排序条件
	public function order($order = array())
	{
		if(isset($order)){
			$this->filter .= ' ORDER BY ';
			$this->filter .= implode(',', $order);
		}
		
		return $this;
	}
	
	// 查询所有
	public function selectAll()
	{
		$sql = sprintf("select * from `%s` %s", $this->table, $this->filter);
		$sth = self::$db->prepare($sql);
		$sth->execute();
		
		return $sth->fetchAll();
	}
	
	// 根据条件（id）查询
	public function select($id)
	{
		$sql = sprintf("select * from `%s` where `id` = '%s'", $this->table, $id);
		$sth = self::$db->prepare($sql);
		$sth->execute();
		
		return $sth->fetch();
	}
	
	// 根据条件(id)删除
	public function delete($id)
	{
		$sql = sprintf("delete from `%s` where `id` = '%s'", $this->table, $id);
		$sth = self::$db->prepare($sql);
		$sth->execute();
		
		return $sth->rowCount();
	}
	
	// 自定义 SQL查询，返回影响的行数
	public function query($sql)
	{
		$sth = self::$db->prepare($sql);
		$sth->execute();
		
		return $sth->rowCount();
	}
	
	// 新增数据
	public function add($data)
	{
		$sql = sprintf("insert into `%s` %s", $this->table, $this->formatInsert($data));
		
		return $this->query($sql);
	}
	
	// 修改数据
	public function update($id, $data)
	{
		$sql = sprintf("update `%s` set %s where `id` = '%s'", $this->table, $this->formatUpdate($data), $id);
		
		return $this->query($sql);
	}
	
	// 将数组装换成插入格式的 sql语句
	private function formatInsert($data)
	{
		$fileds = array();
		$values = array();
		foreach($data as $key => $value){
			$fileds[] = sprintf("`%s`", $key);
			$values[] = sprintf("'%s'", $value);
		}	
		
		$field = implode(',', $fileds);
		$value = implode(',', $values);
		
		return sprintf("(%s) values (%s)", $field, $value);
	}
	
	// 将数组转换成更新格式的 sql语句
	private function formatUpdate($data)
	{
		$fields = array();
		foreach($data as $key => $value){
			$fields[] = sprintf("`%s` = '%s'", $key, $value);
		}
		
		return implode(',', $fields);
	}
}