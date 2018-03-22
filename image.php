<?php
//定义一个图像处理类 
//步骤为:	
//检测路径是否存在


//获取图片的信息
//判断小图的宽高是否大于大图的宽高
//获取图片的位置  1 - 9  非 1-9 的值话 就是小图片的位置在大图片随机

//打开图片

//合并图片

//保存图片

//销毁资源

class Image
{
	//路径
	public $path = './';
	
	//初始化路径
	public function __construct($path = './')
	{
		$this->path = rtrim($path , '/') . '/';
	}
	

	//这是水印的方法
	public function water($dst , $src , $preFix = 'water' , $opacity = 50 , $position=5 , $isRandName = true)
	{
		//大图
		$dst = $this->path . $dst;
		//小图
		$src = $this->path . $src;
		
		if (!file_exists($dst)) {
			exit('图片不存在');
		}
		if (!file_exists($src)) {
			exit('小图不存在');
		}
		
		
		//获取大图的图片信息
		$dstInfo = self::getImageInfo($dst);
		//获取小图的图片信息
		$srcInfo = self::getImageInfo($src);
		
		//两张图片进行比较大小
		if (!$this->checkSize($dstInfo , $srcInfo)) {
			exit('小图的宽高大于了大图的宽高');
		}
		
		//获取位置信息
		
		$position = self::getPosition($dstInfo , $srcInfo , $position);
		
	//	var_dump($position);
	
	
		//打开大图片
		$dstRes = self::openImg($dst , $dstInfo);
		
		//打开小图片
		$srcRes = self::openImg($src , $srcInfo);
		
		
		//合并图片
		$newRes = self::mergeImg($dstRes , $srcRes , $srcInfo , $position , $opacity);
		
		//处理路径的问题
		
		if ($isRandName) {
			$newPath = $this->path . $preFix . uniqid() . $dstInfo['name'];
		} else {
			$newPath = $this->path . $preFix . $dstInfo['name'];
		}
		
		//是不是保存的时候用路径
		
		self::savImg($newPath , $newRes , $dstInfo);
		
		//销毁资源
		imagedestroy($dstRes);
		imagedestroy($srcRes);
		
		
	}
	
	//这是缩略图的方法
	 
	public function thumb($image , $width , $height , $preFix = 'thumb_' , $isRandName = true)
	{
		//判断路径是否存在
		if (!file_exists($image)) {
			exit('图片路径不存在');
		}
		
		$info = self::getImageInfo($image);
		
		$newSize = self::getNewSize($width , $height , $info);
		
		$res = self::openImg($image , $info);
		
		$newRes = self::kidOfImage($res , $newSize , $info);
		
		if ($isRandName) {
			$newPath = $this->path . $preFix . uniqid() . $info['name'];
		} else {
			$newPath = $this->path . $preFix . $info['name'];
		}
		self::savImg($newPath  , $newRes , $info);
		
		imagedestroy($newRes);
		
		return $newPath;
		
		
		
	}
	
	//保存图片
	
	static public function savImg($path , $res , $info)
	{
		switch ($info['mime']) {
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				imagejpeg($res , $path);  //imagejpeg()  imagepng() imagewbmp();
				break;
			case 'image/png':
			case 'image/x-png':
				imagepng($res , $path);
				break;
			case 'image/wbmp':
			case 'image/bmp':
				imagewbmp($res , $path);
				break;
			case 'image/gif':
				imagegif($res , $path);
				break;
		}
	}
	
	//合并图片
	static public function mergeImg($dstRes , $srcRes , $srcInfo , $position , $opacity)
	{
		imagecopymerge($dstRes , $srcRes , $position['x'] , $position['y'] , 0 , 0 , $srcInfo['width'] , $srcInfo['height'] ,$opacity);
		
		return $dstRes;
	}
	
	//打开图片
	static protected function openImg($path , $info)
	{
		switch ($info['mime']) {
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				$res = imagecreatefromjpeg($path);  //imagejpeg()  imagepng() imagewbmp();
				break;
			case 'image/png':
			case 'image/x-png':
				$res = imagecreatefrompng($path);
				break;
			case 'image/wbmp':
			case 'image/bmp':
				$res = imagecreatefromwbmp($path);
				break;
			case 'image/gif':
				$res = imagecreatefromgif($path);
				break;
		}
		return $res;
	}
	
	
	//获取位置的方法
	static public function getPosition($dstInfo , $srcInfo , $position)
	{
		switch ($position) {
			case 1:
				$x = 0;
				$y = 0;
				break;
			case 2:
				$x = ($dstInfo['width'] - $srcInfo['width']) / 2;
				$y = 0;
				break;
			case 3:
				$x = $dstInfo['width'] - $srcInfo['width'];
				$y = 0;
				break;
			case 4:
				$x = 0;
				$y = ($dstInfo['height'] - $srcInfo['height']) / 2;
				break;
			case 5:
				$x = ($dstInfo['width'] - $srcInfo['width']) / 2;
				$y = ($dstInfo['height'] - $srcInfo['height']) / 2;
				break;
			case 6:
				$x = $dstInfo['width'] - $srcInfo['width'];
				$y = ($dstInfo['height'] - $srcInfo['height']) / 2;
				break;
			case 7:
				$x = 0;
				$y = $dstInfo['height'] - $srcInfo['height'];
				break;
			case 8:
				$x = ($dstInfo['width'] - $srcInfo['width']) / 2;
				$y = $dstInfo['height'] - $srcInfo['height'];
				break;
			case 9:
				$x = $dstInfo['width'] - $srcInfo['width'];
				$y = $dstInfo['height'] - $srcInfo['height'];
				break;
			default:
				$x = mt_rand(0 , $dstInfo['width'] - $srcInfo['width']);
				$y = mt_rand(0 , $dstInfo['height'] - $srcInfo['height']);
				break;
		}
		
		return [
			'x' => $x,
			'y' => $y
		];
	}
	
	//图片尺寸比较
	public function checkSize($dstInfo , $srcInfo)
	{
		if ($dstInfo['width'] < $srcInfo['width']) {
			return false;
		}
		
		if ($dstInfo['height'] < $srcInfo['height']) {
			return false;
		}
		
		return true;
	}
	
	
	
	
	
	
	
	//获取图片信息的静态方法
	
	static public function getImageInfo($path)
	{
		
		$data = getimagesize($path);
		$info['width'] = $data[0];
		$info['height'] = $data[1];
		
		$info['mime'] = $data['mime'];
		
		$info['name'] = basename($path);
		//var_dump(basename($path));
		return $info;
		
		//var_dump($data);
	}
	
	//下面的两个方法不需要写 直接复制 这是关于缩略图的方法
	private static function kidOfImage($srcImg, $size, $imgInfo)
	{
		$newImg = imagecreatetruecolor($size["width"], $size["height"]);		
		$otsc = imagecolortransparent($srcImg);
		if ( $otsc >= 0 && $otsc < imagecolorstotal($srcImg)) {
			 $transparentcolor = imagecolorsforindex( $srcImg, $otsc );
				 $newtransparentcolor = imagecolorallocate(
				 $newImg,
				 $transparentcolor['red'],
					 $transparentcolor['green'],
				 $transparentcolor['blue']
			 );

			 imagefill( $newImg, 0, 0, $newtransparentcolor );
			 imagecolortransparent( $newImg, $newtransparentcolor );
		}

	
		imagecopyresized( $newImg, $srcImg, 0, 0, 0, 0, $size["width"], $size["height"], $imgInfo["width"], $imgInfo["height"] );
		imagedestroy($srcImg);
		return $newImg;
	}
	
	private static function getNewSize($width, $height, $imgInfo)
	{	
		//将原图片的宽度给数组中的$size["width"]
		$size["width"] = $imgInfo["width"];   
		//将原图片的高度给数组中的$size["height"]
		$size["height"] = $imgInfo["height"];  
		
		if($width < $imgInfo["width"]) {
			//缩放的宽度如果比原图小才重新设置宽度
			$size["width"] = $width;             
		}

		if ($width < $imgInfo["height"]) {
			//缩放的高度如果比原图小才重新设置高度
			$size["height"] = $height;       
		}

		if($imgInfo["width"]*$size["width"] > $imgInfo["height"] * $size["height"]) {
			$size["height"] = round($imgInfo["height"] * $size["width"] / $imgInfo["width"]);
		} else {
			$size["width"] = round($imgInfo["width"] * $size["height"] / $imgInfo["height"]);
		}

		return $size;
	}
	
}



