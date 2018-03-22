<?php
namespace Framework;
//第二个分页的类
class Page
{
	//当前页
	protected $page;
	//总条数
	protected $total;
	//总页数
	protected $pageCount;
	//偏移量
	protected $offset;
	//url
	protected $url;
	//每页显示数
	protected $num;
	
	//初始化一批成员属性
	
	public function __construct($total , $num = 5)
	{
		//处理总条数的情况
		$this->total = $this->parseTotal($total);
		
		//每页的显示数
		$this->num = $num;
		
		//处理总页数
		$this->pageCount = $this->getPageCount();
		
		//获取当前页
		$this->page = $this->getPage();
		
		//处理偏移量
		$this->offset = $this->getOffset();
		
		//获取url
		$this->url = $this->getUrl();
		
		
	}
	//处理设置url
	protected function setUrl($page)
	{
		if (strstr($this->url , '?')) {
			return $this->url . '&page=' . $page;
		} else {
			return $this->url . '?page=' . $page;
		}
	}
	
	//首页
	protected function first()
	{
		return $this->setUrl(1);
	}
	//上一页
	protected function prev()
	{
		$page = (($this->page - 1) < 1) ? 1 : $this->page -1;
		
		return $this->setUrl($page);
	}
	
	//下一页
	
	protected function next()
	{
		$page = (($this->page + 1) > $this->pageCount) ? $this->pageCount : $this->page + 1;
		
		return $this->setUrl($page);
	}
	//尾页
	protected function last()
	{
		return $this->setUrl($this->pageCount);
	}
	
	//处理获取url
	
	protected function getUrl()
	{
		//var_dump($_SERVER);
		
		//获取文件地址
		//SCRIPT_NAME
		$path = $_SERVER['SCRIPT_NAME'];
		//获取主机名
		$host = $_SERVER['SERVER_NAME'];
		//获取端口号
		$port = $_SERVER['SERVER_PORT'];
		//获取协议
		$scheme = $_SERVER['REQUEST_SCHEME'];
		//获取请求的参数
		$queryString = $_SERVER['QUERY_STRING'];
		
		if (strlen($queryString)) {
			parse_str($queryString , $array);
			
			unset($array['page']);
			
			$path = $path . '?' . http_build_query($array);
			//var_dump($path);
		}
		
		//拼接url
		$url = $scheme . '://' . $host . ':' . $port . $path;
		
		return $url;
	}
	
	
	//获取偏移量
	public function getOffset()
	{
		//limit 0 , 5
	
		$start = ($this->page - 1) * $this->num;
		
		return 'LIMIT ' . $start . ' , ' . $this->num;
		
	}
	
	//获取当前页
	protected function getPage()
	{
		return isset($_GET['page']) ? $_GET['page'] : 1;
	}
	
	
	//处理总页数
	protected function getPageCount()
	{
		return ceil($this->total/$this->num);
	}
	
	
	//处理总条数
	protected function parseTotal($total)
	{
		return ($total < 1) ? 1 : $total;
	}
	//获取分页的方法
	public function rander()
	{
		return[
			'first' => $this->first(),
			'last' => $this->last(),
			'prev' => $this->prev(),
			'next' => $this->next()
		];
	}
	
}


