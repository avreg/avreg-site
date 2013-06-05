<?php
	require ('/etc/avreg/site-defaults.php');
	//Загрузка исходнгого изображения
	$id = $_GET['url'];

// пробуем открыть файл для чтения
if (@fopen($id, "r")) {

	$path_info = pathinfo($id);
	switch ($path_info['extension']) {
		case 'jpeg':
		case 'jpg':
		case 'JPEG':
		case 'JPG':
			$image_type = IMG_JPEG;
			$im = @imagecreatefromjpeg($id);
			break;
		case 'png':
		case 'PNG':
			$image_type = IMG_PNG;
			$im = @imagecreatefromjpeg($id);
			break;
		default:
			die("only jpeg and png supported");
	}

	//Определение размеров исходного изображения
	$im_width=imageSX($im);
	$im_height=imageSY($im);
	
	//пропорции изображения
	$im_proportion = $im_width/$im_height;
	
	//размеры отображения
	$w =isset($_GET['w'])? (int)$_GET['w']:0;
	$h =isset($_GET['h'])? (int)$_GET['h']:0;

	//если передан параметр один к одному
	if($w=='1:1' || $h=='1:1'){
		$w =$im_width;
		$h =$im_height;
	}
	
	
	
	if($w==0 && $h==0){
		$w =$im_width;
		$h =$im_height;
	}
	elseif($w==0){
		$w = $h*$im_proportion;
	}
	elseif ($h==0){
		$h = $w/$im_proportion;
	}
	
	
	
	
	//resulted sizes
	$new_width = $w;
	$new_height = $h;
	
	$saveProp = isset($_GET['prop'])? $_GET['prop'] : true;
	//режим сохранять пропорции?
	if($saveProp=='true')
	{
		$el_proportion = $w/$h;
	
		if($im_proportion > $el_proportion )
		{
			$new_height = $w/$im_proportion;
		}
		else
		{
			$new_width = $h*$im_proportion;
		}
	}
	
	// resize
	$new_im=imagecreatetruecolor($new_width,$new_height);
	imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
	ImageCopyResized($new_im,$im,0,0,0,0,$new_width,$new_height,$im_width,$im_height);
	
	 //output
	header("Content-type: image/jpeg");
	Imagejpeg($new_im,'',80); // quality 80
	ImageDestroy($im);
	ImageDestroy($new_im);

//если файл не найден	
} else {

	//изображение банера - "file not found"
	$string1 = "IMAGE NOT FOUND";
	$w = 200;
	$h = 120;
	$im = imagecreatetruecolor($w,$h);
	$orange = imagecolorallocate($im, 220, 210, 60);
	$px = (imagesx($im) - 8.5 * strlen($string1)) / 2;
	$py = (imagesy($im))/2.5;
	$fs = 5;
	imagestring($im, $fs, $px, $py, $string1, $orange);
	
	//Определение размеров исходного изображения
	$im_width=imageSX($im);
	$im_height=imageSY($im);
	
	//размеры отображения
	$w = $_GET['w'];
	$h = $_GET['h'];
	
	//resulted sizes
	$new_width = $w;
	$new_height = $h;
	
	$saveProp = $_GET['prop'];
	//режим сохранять пропорции?
	if($saveProp=='true')
	{
		$im_proportion = $im_width/$im_height;
		$el_proportion = $w/$h;
	
		if($im_proportion > $el_proportion )
		{
			$new_height = $w/$im_proportion;
		}
		else
		{
			$new_width = $h*$im_proportion;
		}
	}
	
	// resize
	$new_im=imagecreatetruecolor($new_width,$new_height);
	imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
	ImageCopyResized($new_im,$im,0,0,0,0,$new_width,$new_height,$im_width,$im_height);
	
	//output
	header("Content-type: image/jpeg");
	Imagejpeg($new_im,'',80); // quality 80
	ImageDestroy($im);
	ImageDestroy($new_im);

}


?>