<?php
require_once(dirname(__FILE__) . "/ImageHandler.php");
class ImageTools_Thumbnail extends ImageTools_ImageHandler
{
	var $thumbSize;
	var $sizex;
	var $sizey;
	var $isSquared = false;
	var $isCropped = false;
	var $quality;
	var $cornerSize;
	var $backgroundColour;
	var $backgroundWidth;
	var $borderWidth;
	var $borderColour;
	var $cacheFile;
	var $scaleUp;
	
	var $noCache = false;
	var $noError = false;
	var $file;
	var $gdVersion;
	var $thumbnail;
	var $image;
	var $inputImage;
	var $outputImage;
	var $imageType;
	var $imageErrorFile;
	var $text;
	var $imageWidth;
	var $imageHeight;
	var $log;
	var $sizeFactor;
	var $inputImageErrorFile;
	var $alt; 
	
	public function __construct() {
		parent::__construct();
	}
	
	public function prepare() {
		// find file

		if ($this->file = parent::getFile($this->file)) {
			if (parent::isImage($this->file)) {
				return parent::prepare();
			}
		}
	}
	
	/**
	 * Construct the image resource and place the thumbnail within it
	 *
	 */
	public function build() {
		
		$this->quality = self::getQuality();
		$this->cornerSize = self::getCornerSize();
		
		// create image resource
        self::createImageResource();

        if ($this->inputImage) {
            // define size of original image
            $this->inputImageWidth = imagesx($this->inputImage);
            $this->inputImageHeight = imagesy($this->inputImage);
            $this->getImageSize();

            self::createCropping();
            self::createThumbnail();
            self::createText();
            self::createCorners();

            return true;
        }
	}
		
	public function crop($x1, $y1, $x2, $y2)
	{
		$this->x1 = $x1;
		$this->y1 = $y1;
		$this->x2 = $x2;
		$this->y2 = $y2;

		$this->isCropped = true;
	}
	
	private function createThumbnail() {
		// create the thumbnail
		if (($this->width)>0 && ($this->height)>0) {
			if ($this->inputImageWidth < 4000) //no point in resampling images larger than 4000 pixels wide - too much server processing overhead - a resize is more economical
			{
				if (substr_count(strtolower($this->gdVersion['GD Version']), "2.")>0)
				{
					//GD 2.0
					$this->outputImage = imagecreatetruecolor($this->width, $this->height);
					imagealphablending($this->outputImage, false);
					imagecopyresampled($this->outputImage, $this->inputImage, 0, 0, 0, 0, $this->width, $this->height, $this->inputImageWidth, $this->inputImageHeight);
					imagesavealpha($this->outputImage, true);
				} else {
					//GD 1.0
					$this->outputImage = imagecreate($this->width, $this->height);
					imagecopyresized($this->outputImage, $this->inputImage, 0, 0, 0, 0, $this->width, $this->height, $this->inputImageWidth, $this->inputImageHeight);
				}
			} else {
				if (substr_count(strtolower($this->gdVersion['GD Version']), "2.")>0)
				{
					// GD 2.0
					$this->outputImage = imagecreatetruecolor($this->width, $this->height);
					imagealphablending($this->outputImage, false);
					imagecopyresized($this->outputImage, $this->inputImage, 0, 0, 0, 0, $this->width, $this->height, $this->inputImageWidth, $this->inputImageHeight);
					imagesavealpha($this->outputImage, true);
				} else {
					// GD 1.0
					$this->outputImage = imagecreate($this->width, $this->height);
					imagecopyresized($this->outputImage, $this->inputImage, 0, 0, 0, 0, $this->width, $this->height, $this->inputImageWidth, $this->inputImageHeight);
				}
			}	
		}	
	}

	public function cropToSize($outputWidth, $outputHeight) {
		$this->croppedOutputWidth = $outputWidth;
		$this->croppedOutputHeight = $outputHeight;

		$this->isCroppedToSize = true;
	}
	
	private function createCropping() {

		if (isset($this->isCroppedToSize)) {
			$crop = array();

			$outputWidth = $this->croppedOutputWidth;
			$outputHeight = $this->croppedOutputHeight;
			
			$sourceWidth = $this->inputImageWidth;
			$sourceHeight = $this->inputImageHeight;
			
			$outRatio = $outputWidth / $outputHeight;
			$inRatio = $sourceWidth / $sourceHeight;
			
			if ($outRatio > $inRatio) {
				$commonSourceWidth = 1;
				$commonSourceHeight = ($sourceHeight / $sourceWidth) * $commonSourceWidth;
				$commonOutputWidth = 1;
				$commonOutputHeight = ($outputHeight / $outputWidth) * $commonOutputWidth;
				$crop["x1"] = 0;
				$crop["x2"] = 1;
				$crop["y1"] = ($commonSourceHeight - $commonOutputHeight) / 2 / $commonSourceHeight;
				$crop["y2"] = 1 - $crop["y1"];
			} else {
				$commonSourceHeight = 1;
				$commonSourceWidth = ($sourceWidth / $sourceHeight) * $commonSourceHeight;
				$commonOutputHeight = 1;
				$commonOutputWidth = ($outputWidth / $outputHeight) * $commonOutputHeight;
				$crop["y1"] = 0;
				$crop["y2"] = 1;
				$crop["x1"] = ($commonSourceWidth - $commonOutputWidth) / 2 / $commonSourceWidth;
				$crop["x2"] = 1 - $crop["x1"];
			}
			
			$this->x1 = $crop["x1"];
			$this->y1 = $crop["y1"];
			$this->x2 = $crop["x2"];
			$this->y2 = $crop["y2"];
			
			$this->sizex = 100;
			$this->sizey = 55;
			$this->isCropped = true;
		}

//        if (@$this->crop) {
//            $points=explode(",", $this->crop);
//
//            $this->x1 = $points[0];
//			$this->y1 = $points[1];
//			$this->x2 = $points[2];
//			$this->y2 = $points[3];
//			$this->isCropped = true; //tn->crop($x1,$y1,$x2,$y2);
//		}
		
		if ($this->isCropped) {		
			// create background image the size of sizex and sizey and place the image in the center	

			// crop is based on percentage points - so convert them back to real sizes
			//$imageSize = getimagesize($image->file);
			//($imageSize[0] > $imageSize[1]) ? $size = $imageSize[0] : $size = $imageSize[1];
			
			$this->x1 = $this->x1 * $this->inputImageWidth;
			$this->y1 = $this->y1 * $this->inputImageHeight;
			$this->x2 = $this->x2 * $this->inputImageWidth;
			$this->y2 = $this->y2 * $this->inputImageHeight;

			$resource = @imagecreatetruecolor($this->x2 - $this->x1, $this->y2 - $this->y1);

			// set background color to white
			@imagefill ( $resource, 0, 0, imagecolorallocate ( $resource, 255, 255, 255 ) );
			// now build the original image into the background image using image copy function
			@imagecopy( $resource, $this->inputImage, 0 , 0 ,$this->x1, $this->y1, $this->x2 - $this->x1, $this->y2 - $this->y1);			
		
			// assign to the image
			$this->inputImage = $resource;

			$originalImageWidth = $this->inputImageWidth;
			$originalImageHeight = $this->inputImageHeight;
			
			$this->inputImageWidth = $this->x2 - $this->x1;
			$this->inputImageHeight = $this->y2 - $this->y1;

			$this->getImageSize();

			// restore original input sizes
			//$this->inputImageWidth = $originalImageWidth;
			//$this->inputImageHeight = $originalImageHeight;
			
		} else {
			// if image is smaller than thumbnail output size...
			if (($this->width > $this->inputImageWidth && $this->height > $this->inputImageHeight) || 
				((@$this->size > $this->inputImageWidth && $this->size > $this->inputImageHeight) )) 
			{
				if (!@$this->scaleUp || @$this->scaleUp == "false") {
					$this->width = @imagesx($this->inputImage);
					$this->height = @imagesy($this->inputImage);
				}
				
				//$this->getImageSize();
				
				//Place on white background to size required
				/*
				// create background image the size of sizex and sizey and place the image in the center	
			    $resource = imagecreatetruecolor($this->width, $this->height);
			
				// set background color to white
				imagefill ( $resource, 0, 0, imagecolorallocate ( $resource, 255, 255, 255 ) );
			
				// now build the original image into the background image using image copy function
				imagecopy($resource, $this->inputImage, (int)($this->width / 2) - (int)($this->inputImageWidth / 2) ,(int)($this->height / 2) - (int)($this->inputImageHeight / 2),0,0, $this->inputImageWidth, $this->inputImageHeight);
				
				// assign to the image
				$this->inputImage = $resource;
				
				
				// output as normal size
				
				$this->width = @imagesx($this->inputImage);
				$this->height = @imagesy($this->inputImage);
				
				$this->getImageSize();
				*/
			}

		}		
	}
	
	private function getImageSize()
	{
		if (isset($this->croppedOutputWidth) && isset($this->croppedOutputHeight)) {
			$this->sizex = $this->croppedOutputWidth;
			$this->sizey = $this->croppedOutputHeight;
		}
		
		// define size of overall image
		($this->inputImageWidth > $this->inputImageHeight) ? $this->sizeFactor = $this->inputImageWidth : $this->sizeFactor = $this->inputImageHeight;
		
		// define size of thumbnail
		if ($this->sizex || $this->sizey)
		{
			if ($this->sizex && !$this->sizey) {

				// define images width only
				$this->width = $this->sizex;
				$factor = $this->inputImageWidth / $this->sizex;
				$this->height = ($this->inputImageHeight / $factor);

			} elseif ($this->sizey && !$this->sizex) {
				
				// define images height only
				$this->height = $this->sizey;
				$factor = $this->inputImageHeight / $this->sizey;
				$this->width = ($this->inputImageWidth / $factor);
				
			} elseif ($this->sizey && $this->sizex) {
				
				// define a bounding box for both
				$this->width = $this->sizex;
				$factor = $this->inputImageWidth / $this->sizex;
				$this->height = ($this->inputImageHeight / $factor);
				if ($this->height > $this->sizey)
				{
					$this->height = $this->sizey;
					$factor = $this->inputImageHeight / $this->sizey;
					$this->width = ($this->inputImageWidth / $factor);
				}
			}
		} else {
            if (@$this->size) {
                // size is specified, define image to fit in a square
                $this->width = $this->size;
                @$factor = $this->inputImageWidth / @$this->size;
                @$this->height = ($this->inputImageHeight / $factor);
                if ($this->height > $this->size)
                {
                    $this->height = $this->size;
                    $factor = $this->inputImageHeight / $this->size;
                    $this->width = ($this->inputImageWidth / $factor);
                }

            } else {
                // no size specified, use images size
                $this->width = $this->inputImageWidth;
                $this->height = $this->inputImageHeight;
                $factor = $this->width / $this->height;
            }
		}
		$this->factor = $factor;
	}
	
	private function createText()
	{
		// insert text tag?
		if ($this->text)
		{
			$font=1;
			$white = imagecolorallocate ($this->outputImage, 255, 255, 255);
			$black = imagecolorallocate ($this->outputImage, 0, 0, 0);
			imagestring ($this->outputImage, $font, 3, $this->height - 9, $this->text, $black);
			imagestring ($this->outputImage, $font, 2, $this->height - 10, $this->text, $white);
		}
	}

	private function createCorners()
	{
		#check if coners are required
		if (@$_GET['corner_size']<>"")
		{
			$col_ellipse = getBlendableColor($bg_col);
		
			if($this->borderThickness > 0)
			{
				$col_ellipse = getBlendableColor($bd_col);
		
				$this->addCorners($thumbnail, $this->cornerSize, $this->borderColour, $col_ellipse);
				$thumbnail = addBorder($thumbnail, $this->borderThickness, $this->borderColour);
			}
		
			$this->addCorners($thumbnail, $this->cornerSize, $this->backgroundColour, $col_ellipse);
		}		
	}
	
	private function createImageResource()
	{
		$createFromUrl = false;

		// If calling an external image, remove document_root
		if(substr_count($this->file, "http://") > 0)
		{
			$this->file = urldecode($this->file);
			$createFromUrl = true;
		}
		
		// determine php and gd versions
		$ver=intval(str_replace(".","",phpversion()));
		if ($ver>=430)
		{
			$this->gdVersion=@gd_info();
		}

		// define the right function for the right image types
		if (!$imageTypeArray = @getimagesize($this->file))
		{
			if ($createFromUrl)
			{
				$this->inputImage = $this->binaryReadImageFile($this->file);
				return true;
			} else {
				if($this->noError)
				{
					return false;
				} else {
					return false;
				}
			}
		}
		
		$this->inputImageType = $imageTypeArray[2];
		switch ($this->inputImageType)
		{
			case 2: // JPG
			if (!$this->inputImage = @imagecreatefromjpeg ($this->file))
			{
				// not a valid jpeg file
				$this->inputImage = @imagecreatefrompng ($this->inputImageErrorFile);
				$this->inputImageType = "jpeg";
				if (file_exists($this->cacheFile))
				{
					// remove the cached thumbnail
					unlink($this->cacheFile);
				}
			}
			break;
			case 3: // PNG

	
			if (!$this->inputImage = @imagecreatefrompng($this->file))
			{
				// not a valid png file
				$this->inputImage = imagecreatefrompng ($this->inputImageErrorFile);
				$this->inputImageType = "png";
				if (file_exists($this->cacheFile))
				{
					// remove the cached thumbnail
					unlink($cache_file);
				}
			} else {
				imageAlphaBlending($this->inputImage, false);
				imageSaveAlpha($this->inputImage, true);
			}
			break;
			
			case 1: // GIF
			if (!$this->inputImage = @imagecreatefromgif ($this->file))
			{
				// not a valid gif file
				$this->inputImage = imagecreatefrompng ($this->inputImageErrorFile);
				$this->inputImageType = "gif";
				if (file_exists($this->cacheFile))
				{
					# remove the cached thumbnail
					unlink($this->cacheFile);
				}
			}
			break;
			default:
				$this->inputImage = imagecreatefrompng($this->inputImageErrorFile);
				break;
		}
	}
	
	private function getQuality()
	{
		if ($this->quality<>0) {
			$quality = $this->quality;
		} else {
			$quality = 100;
		}
		return $quality;
	}
	
	private function getCornerSize()
	{
		if(@$this->cornerSize <> "")
		{
			$corner_size = $_GET['corner_size'];
			if($this->backgroundColour <> "")
			{
				$backgroundColour = $this->backgroundColour;
			}
		
			#check if there is to be a bordered corners added
			if(@$_GET['bd_width']<>"")
			{
				$bd_width = intval($_GET['bd_width']);
				$bd_col = '000000';
				if(@$_GET['bd_col']<>"")
				{
					$bd_col = $_GET['bd_col'];
				}
			}
		}
		//return $
	}
	
	private function addCorners(&$im, $corner_size, $bg_col, $col_ellipse=NULL, $types=array('tl', 'tr', 'br', 'bl'))
	{
		foreach($types as $type)
		{
			$corner = createCorner($type, $corner_size, $bg_col, $col_ellipse);
			$coords = getImagePosition($im, substr($type,0,1), substr($type,1,2), imagesx($corner), imagesy($corner));
			imagecopyresampled(
			$im, $corner,
			$coords['x_pos'], $coords['y_pos'],
			0, 0,
			imagesx($corner), imagesy($corner),
			imagesx($corner), imagesy($corner)
			);
		}
	}	
	
	private function createCorner($type, $cornerSize, $bgColour, $colEllipse=NULL)
	{
		$im = imagecreatetruecolor($cornerSize * 3, $cornerSize * 3);
		$rgb = hexToRGB($bgColour);
		$col = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
	
		imagefill($im,0,0,$col);
	
		$rgb = hexToRGB('FFFFFF');
		if($colEllipse != NULL)
		{
			$rgb = hexToRGB($colEllipse);
		}
		$colEllipse = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
	
		$rad = intval($cornerSize)*3;
	
		$trans = imagecolortransparent($im, $colEllipse);
		switch ($type)
		{
			case 'tl':
				imagefilledellipse($im, $rad, $rad, $rad*2, $rad*2, $trans);
				break;
	
			case 'tr':
				imagefilledellipse($im, 0, $rad, $rad*2, $rad*2, $trans);
				break;
	
			case 'bl':
				imagefilledellipse($im, $rad, 0, $rad*2, $rad*2, $trans);
				break;
	
			case 'br':
				imagefilledellipse($im, 0, 0, $rad*2, $rad*2, $trans);
				break;
		}
	
		$corner = imagecreatetruecolor(intval($cornerSize), intval($cornerSize));
	
		imagecolortransparent($corner,$trans);
	
		imagecopyresampled(
			$corner, $im, 0, 0, 0, 0,
			intval($cornerSize), intval($cornerSize),
			intval($cornerSize)*3, intval($cornerSize)*3
		);
	
		imagetruecolortopalette($corner, true, 256);
	
		imagedestroy($im);
	
		return $corner;
	}
	
	private function binaryReadImageFile($fileName)
	{
		$fileContents = '';
		if($fp = fopen($fileName, 'r'))
		{
			if($fileContents = fread($fp, filesize($fileName)))
			{
				fclose($fp);
			}
		}	
		return imagecreatefromstring($fileContents);
	}	

	private function addBorder(&$im, $bdThickness, $borderColour)
	{
		$rgb = hexToRGB($bdColour);
		$borderColour = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
	
		$borderImage = imagecreatetruecolor(imagesx($im), imagesy($im));
		imagefill($borderImage, 0, 0, $borderColour);
	
		imagecopyresampled(
			$bdImage, $im,
			$bdThickness, $bdThickness,
			0, 0,
			imagesx($borderImage) - $borderThickness *2, imagesy($borderImage) - $borderThickness*2,
			imagesx($im), imagesy($im)
		);
	
		imagedestroy($im);
	
		return $borderImage;
	}	
	
	private function getImagePosition($im, $y_pos='t', $x_pos='l', $width=0, $height=0, $offset='0')
	{
		$coords = array();
	
		switch($x_pos)
		{
			case 'c':
				$coords['x_pos'] = imagesx($im)-imagesx($im)/2 - $width/2;
				break;
	
			case 'r':
				$coords['x_pos'] = imagesx($im) - $width - $offset;
				break;
	
			default: // left
			$coords['x_pos'] = $offset;
			break;
		}
	
		switch($y_pos)
		{
			case 'm':
				$coords['y_pos'] = imagesy($im)-imagesy($im)/2 - $height/2;
				break;
	
			case 'b':
				$coords['y_pos'] = imagesy($im) - $height - $offset;
				break;
	
			default: // top
			$coords['y_pos'] = $offset;
			break;
		}
	
		$coords['x_pos'] = round($coords['x_pos'], 0);
		$coords['y_pos'] = round($coords['y_pos'], 0);
	
		return $coords;
	}
	
	private function getBlendableColor($hex_color)
	{
		$color= preg_replace('/^#?([\\da-f]{6})$/i', '\\1', $color);
		list($r,$g,$b)=str_split($hex_color,2);
	
		$cols = array($r,$g,$b);
	
		$result = '';
		for($i=0; $i<count($cols); $i++)
		{
			$pattern = "^([a-zA-Z7-9]){1,1}$";
			if(eregi($pattern, substr($cols[$i], 1, 1)))
			{
				if(substr($cols[$i], 1, 2) != 'F')
				$result .= substr($cols[$i], 0, 1) . 'F';
				else
				$result .= substr($cols[$i], 0, 1) . 'E';
			}
			else
			{
				if(substr($cols[$i], 1, 2) != '0')
				$result .= substr($cols[$i], 0, 1) . '0';
				else
				$result .= substr($cols[$i], 0, 1) . '1';
			}
		}
	
		return $result;
	}
	
	private function hexToRGB($color)
	{
		if (preg_match('/^#?[\\da-f]{6}$/i', $color))
		{
			$color= preg_replace('/^#?([\\da-f]{6})$/i', '\\1', $color);
			list($r,$g,$b)=str_split($color,2);
			$r=hexdec($r);
			$g=hexdec($g);
			$b=hexdec($b);
			return array($r,$g,$b);
		}
		else
		{
			return false;
		}
	}	
	
	protected function getImageType() {
		if ((file_exists($this->cacheFile)) && (@filemtime($this->cacheFile) > @filemtime($this->file)))
		{
			$result = getimagesize($this->cacheFile);
			$this->width = $result[0];
			$this->height = $result[1];
		} else {
			$result = getimagesize($this->file);
		}

		if (is_array($result)) {
			$types = array(
		        1 => 'GIF',
		        2 => 'JPEG',
		        3 => 'PNG',
		        4 => 'SWF',
		        5 => 'PSD',
		        6 => 'BMP',
		        7 => 'TIFF(intel byte order)',
		        8 => 'TIFF(motorola byte order)',
		        9 => 'JPC',
		        10 => 'JP2',
		        11 => 'JPX',
		        12 => 'JB2',
		        13 => 'SWC',
		        14 => 'IFF',
		        15 => 'WBMP',
		        16 => 'XBM'
	    		);
			return strtolower($types[$result[2]]);
		}
	}
}
