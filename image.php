<?php
/*
 * set some headers
 */
// seconds, hours, days
$expires = 3600*24*30;
header("Pragma: public");
header("Cache-Control: maxage=".$expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');



$filename=urldecode($_GET['image']);


$imagepath=substr(dirname(__FILE__),0,strpos(dirname(__FILE__), "/wp-content"))."/wp-content/cache/latex/";
$image=imageCreateFromUnknown($imagepath.$filename);



/**
 * 
 * @param string $filename
 * @return image
 */
function imageCreateFromUnknown($filename){
	//check file type'
	if(!is_file($filename)){
		header("HTTP/1.1 400 Bad Request");
		return;
	}
	
		
	$type=getimagesize($filename);
	$type=$type[2];
	 
	 
	switch($type){
		 
		case 1://if gif
			$img = imagecreatefromgif($filename);
			header('Content-Type: image/gif');
			imagegif($img);
			break;
		case 2://if jpg
			$img = imagecreatefromjpeg($filename);
			header('Content-Type: image/jpeg');
			imagejpeg($img);
			break;
		case 3://if png
			$img = imagecreatefrompng($filename);
			header('Content-Type: image/png');
			imagepng($img);
			break;
		default:
			//thorw error
			header("HTTP/1.1 400 Bad Request");
			break;
	}
	 
	return $img;
}