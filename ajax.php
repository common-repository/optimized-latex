<?php
/**
 * This file prints a img-tag with the image url, alt, title etc... 
 * 
 */
$filename=urldecode($_GET['image']);
$title=urldecode($_GET['title']);
$alt=urldecode($_GET['alt']);
$height=urldecode($_GET['height']);
$width=urldecode($_GET['width']);
$class=urldecode($_GET['class']);
$path=urldecode($_GET['path']);

echo "<img src='$path/image.php?image=$filename' alt=\"$alt\" title=\"$title\" class='$class' width='$width' height='$height' />";
