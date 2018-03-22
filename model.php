<?php
namespace Framework;
//数据库操作类
class Model
{
	//链接
	protected $link;
	//主机名
	protected $host;
	//用户名
	protected $user;
	//密码
	protected $pwd;
	//字符集
	protected $charset;
	//库名字
	protected $dbName;
	//表名
	protected $table;  //表名怎么处理？一张表对应一个模型  userModel  goodsModel orderModel  detailModel catecoryModel
	//字段
	protected $fields;
	//表前缀
	protected $prefix;
	//选项
	protected $options;
	//sql
	protected $sql;
	
	//拿构造方法来实现初始化一批成员属性
	public function __construct($config = null)  //通过配置文件来赋值
	{
		if (is_null($config)) {
			$config = $GLOBALS['config'];
		}
		$this->host = $config['DB_HOST'];
		$this->user = $config['DB_USER'];
		$this->pwd = $config['DB_PWD'];
		$this->charset = $config['DB_CHARSET'];
		$this->dbName = $config['DB_NAME'];
		$this->prefix = $config['DB_PREFIX'];
		
		//通过一个内部的方法来实现数据库链接
		
		$this->link = $this->connect();
		
		//var_dump($this->link);
		
		//处理表名字的问题
		$this->table = $this->getTable();
		
		//var_dump($this->table);
		
		//开始准备处理字段
		
		$this->fields = $this->getFields();
		
		
		
		
	}
	//处理字段的方法
	protected function getFields()
	{
		$cacheFile = 'cache/' . $this->table . '.php';
		
		if (file_exists($cacheFile)) {
			return include $cacheFile;
		} else {
			
			$sql = 'DESC ' . $this->prefix . $this->table;
			
		
			$data = $this->query($sql);
			
			
			$fields = [];
			foreach ($data as $key => $val) {
				$fields[] = $val['Field'];
				
				//在把主键单独存一下
				if ($val['Key'] == 'PRI') {
					$fields['_pk'] = $val['Field'];
				}
			}
			
			$string = "<?php \n return ".var_export($fields , true).'?>';
			
	
			file_put_contents('cache/' . $this->table . '.php' , $string );
			
			return $fields;
		}
		
		
		
	}
	
	//发送sql语句的方法
	protected function query($sql)
	{
		$result = mysqli_query($this->link , $sql);
		
		$data = [];
		
		if ($result) {
			while ($rows = mysqli_fetch_assoc($result)) {
				$data[] = $rows;
			}
		} else {
			return false;
		}
		
		return $data;
	}
	
	
	
	
	
	//处理表名的方法
	protected function getTable()
	{
		
		if (isset($this->table)) {
			return $this->prefix . $this->table;
		} else {
			
			//对你的类名去截取 来获取 表名
			$table = strtolower(substr(get_class($this) , 6 , -5));  //usermodel
			//var_dump($table);
			
			return $table;
		}
	}
	
	
	
	//实现数据库链接方法
	
	protected function connect()
	{
		$link = mysqli_connect($this->host , $this->user , $this->pwd);
		
		if (!$link) {
			exit('数据库链接失败');
		}
		
		mysqli_set_charset($link , $this->charset);
		
		mysqli_select_db($link , $this->dbName);
		
		return $link;
	}
	//通过使用魔术方法call 来实现连贯操作
	public function __call($func , $args)
	{
		$arr = ['fields' , 'table' , 'where' , 'group' , 'having' , 'order' , 'limit'];
		
		if (in_array($func , $arr)) {
			
			//$func 是你请求不存在的方法的名字  它是下标
			//$arg 是你是你请求不存在的方法的名字传过来的实参 它是值
			$this->options[$func] = $args;
			
			return $this;
			
		} else {
			exit('你个王八犊子 你调用的方法没有滚蛋吧娃娃仔');
		}
	}
	
	//写数据库的查询的方法
	
	public function select()
	{
		//$sql = select 字段1， 字段2 。。。 from 表名 where group by having order by limit
		
		$sql = "SELECT %FIELDS% FROM %TABLE% %WHERE% %GROUP% %HAVING% %ORDER% %LIMIT%";
		
		//echo $sql;
		//str_replace();
		
		$sql = str_replace(
			array('%FIELDS%','%TABLE%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%' ,'%LIMIT%'),
			array(
				$this->parseFields(isset($this->options['fields']) ? $this->options['fields'] : null),
				$this->parseTable(),
				$this->parseWhere(),
				$this->parseGroup(),
				$this->parseHaving(),
				$this->parseOrder(),
				$this->parseLimit()
			),
			$sql
		);
		//var_dump($sql);
		
		$data = $this->query($sql);
		unset($this->options);
		return $data;
		
	}
	//处理limit
	protected function parseLimit()
	{
		
		$limit = '';
		
		if (empty($this->options['limit'])) {
			$limit = '';
		} else {
			//一种数组
			if (is_array($this->options['limit'][0])) {
				$limit = 'LIMIT ' . join(',' , $this->options['limit'][0]);
			}
			//一种是字符串
			
			if (is_string($this->options['limit'][0])) {
				$limit = 'LIMIT ' . $this->options['limit'][0];
			}
			
		}
		 
		return $limit;
	}
	
	
	
	
	//处理order
	protected function parseOrder()
	{
		$av = '';
		
		if (empty($this->options['order'])) {
			return '';
		} else {
			$av = 'ORDER BY ' . $this->options['order'][0];
		}
		
		return $av;
	}
	//处理haviing
	
	protected function parseHaving()
	{
		$av = '';
		
		if (empty($this->options['having'])) {
			return '';
		} else {
			$av = 'HAVING ' . $this->options['having'][0];
		}
		
		return $av;
	}
	
	//处理分组
	protected function parseGroup()
	{
		$group = '';
		
		if (empty($this->options['group'])) {
			return '';
		} else {
			$group = 'GROUP BY ' . $this->options['group'][0];
		}
		
		return $group;
	}
	//处理where 条件
	
	protected function parseWhere()
	{
		if (isset($this->options['where'])) {
			return $where = 'WHERE ' . $this->options['where'][0];
		} else {
			return '';
		}
	}
	
	//处理表名
	protected function parseTable()
	{
		if (!empty($this->options['table'])) {
			return $this->prefix . $this->options['table'][0];
		} else {
			return $this->prefix . $this->table;
		}
	}
	
	//处理查询时候的字段
	protected function parseFields($options)
	{
		
		if (empty($options)) {
			return '*';
		} else {
			
			if (is_string($options[0])) {
				$fields = explode(',' , $options[0]);
				//var_dump($fields);
				$tmpArr = array_intersect($fields , $this->fields);
				
				$fields = join(',' , $tmpArr);
				
				
				
			}
			
			if (is_array($options[0])) {
				$fields = join(',' , array_intersect($options[0] , $this->fields));
			}
			
			return $fields;
			
		}
	}
	
	
	//修改方法
	public function update($data)
	{
		$sql = 'UPDATE %TABLE% %SET% %WHERE%';  //username='xiugai'
		
		$sql = str_replace(
			array('%TABLE%' , '%SET%' , '%WHERE%' ),
			array(
				$this->parseTable(),
				$this->parseSet($data),
				$this->parseWhere()
			),
			$sql
		);
		
		$data = $this->exec($sql);
		//var_dump($sql);
		return $data;
	}
	
	//处理修改时候的set
	protected function parseSet($data)
	{
		
		$str = '';
		
		foreach ($data as $key => $val) {
			$str .= $key . '=' . "'$val',";
		}
		
		return 'SET ' . rtrim($str , ',');
	}
	//执行sql语句
	protected function exec($sql)
	{
		$reault = mysqli_query($this->link , $sql);
		
		if ($reault && mysqli_affected_rows($this->link)) {
		//	return mysqli_insert_id($this->link);
			//return mysqli_affected_rows($this->link);
			$update = mysqli_affected_rows($this->link);
			$add = mysqli_insert_id($this->link);
			return [$update , $add];
		} else {
			return false;
		}
	}
	//添加数据
	public function add($data)
	{
		if (!is_array($data)) {
			return false;
		}
		
		$sql = 'INSERT INTO %TABLE% (%FIELDS%) VALUES(%VALUES%)';
		
		$sql = str_replace(
			array('%TABLE%' , '%FIELDS%' , '%VALUES%'),
			array(
				$this->parseTable(),
				$this->parseAddFields(array_keys($data)),
				$this->parseAddVal(array_values($data))
			),
			$sql
		);
		//var_dump($sql);
		return $this->exec($sql);
		

		
	}
	//处理添加的字段  // 
	protected function parseAddFields($data)
	{
		return join(',' , $data);
	}
	
	//处理添加的值
	
	protected function parseAddVal($data)
	{
		$str = '';
		
		foreach ($data as $key => $val) {
			$str .= '\''.$val.'\',';  //insert into bbs_user(username , password) values('zhangkun' , 1)
		}
		
		return rtrim($str , ',');
		
	}
	
	
	public function delete()
	{
		$sql = 'DELETE FROM %TABLE% %WHERE%';
		
		$sql = str_replace(
			array('%TABLE%','%WHERE%'),
			array(
				$this->parseTable(),
				$this->parseWhere()
			),
			$sql
		);
		var_dump($sql);
		$data = $this->exec($sql);
		
		return $data;
	}
	
	//查询最大值
	public function max($fields = null)
	{
		if (empty($fields)) {
			$fields = $this->fields['_pk'];
		} 
		
		$sql = "SELECT  MAX($fields) as m FROM $this->table";
		
		$result = $this->query($sql);
		
		return $result[0]['m'];
	}
	
	
	//查询最小值
	public function min($fields = null)
	{
		if (empty($fields)) {
			$fields = $this->fields['_pk'];
		} 
		
		$sql = "SELECT  MIN($fields) as m FROM $this->table";
		
		$result = $this->query($sql);
		
		return $result[0]['m'];
	}

	 //总数
    
    public function total($fields){
        
        if(empty($fields)){
           $fields=$this->fields['_pk'];
        }

        $sql="SELECT COUNT($fields) as c FROM %TABLE% %WHERE%";

        $sql=str_replace(array('%TABLE%','%WHERE%'),array($this->parseTable(),$this->parseWhere()),$sql);

        //echo $sql;
        
        $data=$this->query($sql);

        return $data[0]['c'];
    } 
}

