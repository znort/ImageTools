<?php
/*
 * retrieves a reference to a file from a database
 */
class ImageTools_DatabaseImage
{
	public static function getImage($id, $size=null, $sizex=null, $sizey=null, $scaleUp = false, $quality = 85, $alt = "public/images/sleight.gif" , $crop = null) {
		$config = Zend_Registry::get("config");
		$db = Zend_Registry::get("db");
		
		if (!$size && !$sizex && !$sizey) { 
			if (isset($config->images->large_preview_size)) {
				$previewSize = $config->images->large_preview_size;
			} else {
				$previewSize = 150;
			}
		} else {
			$previewSize = $size;
		}
		
		$table = new Zend_Db_Table('images');
		$rowset = $table->fetchAll("id = '".$id."'");
		if ($dbImage = $rowset->current()) {
			if (substr($dbImage->file,0,1)=="/") $dbImage->file = substr($dbImage->file,1);

			$image = new ImageTools_ThumbNail();
			
			$image->file = $dbImage->file;
			//$image->size = $previewSize;
			$image->size = $size;
			$image->sizex = $sizex;
			$image->sizey = $sizey;
			if ($crop == null) {
				// use database cropping information
				$image->crop = $dbImage->crop;
			} else {
				// use custom cropping from url
				$image->crop = $crop;
			}
			$image->scaleUp = $scaleUp;
			$image->quality = $quality;
			
			if (!file_exists($image->file)) $image->file = $alt; 
			$image->src = "/image/thumbnail?size=".$previewSize."&scaleUp=".$image->scaleUp."&file=".$image->file;
			
			$image->id = $id;
			
			// retrieve initial cropping settings (convert back from full size and calculate width/height)
			$factor = 1;
			$imageSize = getimagesize($image->file);
			$width = $imageSize[0];
			$height = $imageSize[1];
			($width > $height) ? $size = $width : $size = $height;
			if ($scaleUp==false) {
				($size < $previewSize) ? $factor = 1 : $factor = $previewSize / $size;
			} else {
				$factor = $previewSize / $size;
			}
			$size = $size * $factor;

			$image->width = intval($width * $factor);
			$image->height = intval($height * $factor);
			
			return $image;
		}
	}
}