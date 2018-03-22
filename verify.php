<?php
namespace Framework;
//这是一个验证码类
class Verify
{
	//宽
	protected $width;
	//高
	protected $height;
	//图片类型
	protected $imgType;
	//字体个数
	protected $num;
	//字体类型
	protected $type;
	//资源
	protected $image;
	//画布上的字符串
	protected $getCode;
	
	//初始化一批成员属性
	public function __construct($width = 100 , $height =40  , $imgtype = 'png' , $num = 4 , $type = 3)
	{
		$this->width = $width;
		$this->height = $height;
		$this->imgType = $imgtype;
		$this->num = $num;
		$this->type = $type;
		$this->getCode = $this->getCode();
		//var_dump($this->getCode);
		
		
	}
	
	//获取字符串
	protected function getCode()
	{
		$string = '';
		switch ($this->type) {
			case 1:
				$string = join('' , array_rand(range(0 , 9) , $this->num));
				break;
			case 2:
				$string = implode('' , array_rand(array_flip(range('a' , 'z')), $this->num));
				break;
			case 3:
			/*
				$str = 'abcdefghjkmnpqrstuvwxyABCEFGHIJKLMNPQRSTUVWXY';
				
				$string = substr(str_shuffle($str) , 0 , $this->num);
				break;
				
				//基础课的时候用了两种方法 这一种在基础课的时候有这点代码 自己稍微修改一下就可以  自己加上去  5遍代码里面要有
				*/
				for ($i=0;$i<$this->num;$i++) {
					$rand = mt_rand(0,2);
					
					switch ($rand) {
						case 0:
							$char = mt_rand(48 , 57);
							break;
						case 1:
							$char = mt_rand(65 , 90);
							break;
						case 2:
							$char = mt_rand(97 , 122);
							break;
					}
					$string .= sprintf('%c' , $char);
				}
				break;
		}
		return $string;
	}
	
	
	//创建画布
	protected function createImg()
	{
		$this->image = imagecreatetruecolor($this->width , $this->height);
	}
	
	//整理背景颜色
	protected function bgColor()
	{
		return imagecolorallocate($this->image , mt_rand(130 , 255) ,mt_rand(130 , 255) ,mt_rand(130 , 255));
	}
	
	//整理字体颜色 还干扰线 啊 干扰点啊
	protected function fontColor()
	{
		return imagecolorallocate($this->image , mt_rand(0 , 120) ,mt_rand(0 , 110) ,mt_rand(0 , 119));
	}
	
	//填充背景颜色
	protected function fill()
	{
		return imagefilledrectangle($this->image , 0 , 0 , $this->width , $this->height , $this->bgColor());
	}
	
	//画点
	protected function pixed()
	{
		for ($i = 0;$i<50 ; $i++) {
			imagesetpixel($this->image , mt_rand(0, $this->width) , mt_rand(0 , $this->height), $this->fontColor());
		}
	}
	
	//画线
	protected function arc()
	{
		for($i=0;$i<3;$i++) {
			imagearc($this->image , mt_rand(10 , $this->width - 10) , mt_rand(10 , $this->height - 10) , 80 , 50 , 10 ,240 , $this->fontColor());
		}
	}
	
	//开始写字
	protected function write()
	{
		for ($i=0;$i<$this->num;$i++) {
			$x = ceil($this->width/$this->num) * $i;
			$y = mt_rand(0 , $this->height - 20);
			
			imagechar($this->image , 5 , $x , $y , $this->getCode[$i] , $this->fontColor());
		}
	}
	
	//输出图片
	private function out()
	{
		$func = 'image' . $this->imgType;
		
		$header = 'Content-type:image' . $this->imgType;
		
		if (function_exists($func)) {
			$func($this->image);
			header($header);
		} else {
			exit('不支持的图片类型');
		}
		
		
	}
	
	//获取图片
	public function getImg()
	{
		$this->createImg();
		$this->fill();
		$this->pixed();
		$this->arc();
		$this->write();
		$this->out();
	}
	/*
	//销毁
	public function __destruct()
	{
		imagedestroy($this->image);
	}*/
	
	
	
	
	
	
}



