<?php
namespace Framework;
//����һ����֤����
class Verify
{
	//��
	protected $width;
	//��
	protected $height;
	//ͼƬ����
	protected $imgType;
	//�������
	protected $num;
	//��������
	protected $type;
	//��Դ
	protected $image;
	//�����ϵ��ַ���
	protected $getCode;
	
	//��ʼ��һ����Ա����
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
	
	//��ȡ�ַ���
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
				
				//�����ε�ʱ���������ַ��� ��һ���ڻ����ε�ʱ���������� �Լ���΢�޸�һ�¾Ϳ���  �Լ�����ȥ  5���������Ҫ��
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
	
	
	//��������
	protected function createImg()
	{
		$this->image = imagecreatetruecolor($this->width , $this->height);
	}
	
	//��������ɫ
	protected function bgColor()
	{
		return imagecolorallocate($this->image , mt_rand(130 , 255) ,mt_rand(130 , 255) ,mt_rand(130 , 255));
	}
	
	//����������ɫ �������� �� ���ŵ㰡
	protected function fontColor()
	{
		return imagecolorallocate($this->image , mt_rand(0 , 120) ,mt_rand(0 , 110) ,mt_rand(0 , 119));
	}
	
	//��䱳����ɫ
	protected function fill()
	{
		return imagefilledrectangle($this->image , 0 , 0 , $this->width , $this->height , $this->bgColor());
	}
	
	//����
	protected function pixed()
	{
		for ($i = 0;$i<50 ; $i++) {
			imagesetpixel($this->image , mt_rand(0, $this->width) , mt_rand(0 , $this->height), $this->fontColor());
		}
	}
	
	//����
	protected function arc()
	{
		for($i=0;$i<3;$i++) {
			imagearc($this->image , mt_rand(10 , $this->width - 10) , mt_rand(10 , $this->height - 10) , 80 , 50 , 10 ,240 , $this->fontColor());
		}
	}
	
	//��ʼд��
	protected function write()
	{
		for ($i=0;$i<$this->num;$i++) {
			$x = ceil($this->width/$this->num) * $i;
			$y = mt_rand(0 , $this->height - 20);
			
			imagechar($this->image , 5 , $x , $y , $this->getCode[$i] , $this->fontColor());
		}
	}
	
	//���ͼƬ
	private function out()
	{
		$func = 'image' . $this->imgType;
		
		$header = 'Content-type:image' . $this->imgType;
		
		if (function_exists($func)) {
			$func($this->image);
			header($header);
		} else {
			exit('��֧�ֵ�ͼƬ����');
		}
		
		
	}
	
	//��ȡͼƬ
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
	//����
	public function __destruct()
	{
		imagedestroy($this->image);
	}*/
	
	
	
	
	
	
}



