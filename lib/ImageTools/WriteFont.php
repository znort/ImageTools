<?php
/* 
	writefont.php v0.6
	______________________________________________________________________ 
	Creates text rendered in a particular font based on info passed to it via $_GET. 
	
	Images are cached on the server, so server processing overhead is only 
	needed for the first time the script runs on a particular image.
	______________________________________________________________________
	Requires:
		GD Library
		Freetype library
	______________________________________________________________________
	Copyright: 
		(C) 2005 Chris Tomlinson. christo@mightystuff.net
		http://mightystuff.net
		
		This library is free software; you can redistribute it and/or
		modify it under the terms of the GNU Lesser General Public
		License as published by the Free Software Foundation; either
		version 2.1 of the License, or (at your option) any later version.
		
		This library is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
		Lesser General Public License for more details.
	
		http://www.gnu.org/copyleft/lesser.txt
	______________________________________________________________________
	Configuration:
	______________________________________________________________________	
	Usage:
	______________________________________________________________________
	Changes:
		0.1 - first release
		0.2 - doesn't fail if the cache file can't be created
		0.3 - now resamples a larger image to give a better anti-aliased effect.
		0.4 - added angle rotation
		0.5 - conversion to a class
		0.6 - attempts to allow for multiple lines if a set width exceeded
*/
require_once(dirname(__FILE__) . "/ImageHandler.php");

class  ImageTools_WriteFont extends ImageTools_ImageHandler
{
	public $text;
	public $font;
	public $size;	
	public $margin = 0;
	public $colour = "000000";
	public $bgcolour = "ffffff";
	public $lineSpacing = 1;

	protected $lineHeight;
	protected $fontRoot;
	protected $type = 'png';
	
	public function prepare() {
		// find font
		if ($this->font = parent::getFile($this->font)) {
			return parent::prepare();
		}
	}
	
	/**
	 * Construct the image resource and place the text within it
	 *
	 */
	public function build() {
		
		if (!$this->lineSpacing) {
			$this->lineSpacing = 1;
		}

	    $this->lines = array ( );
		if (isset($this->width)) {
			// determine number of lines
		    $words = split ( ' ', $this->text );

		    $line  = '';		
		    foreach ( $words as $word )
		    {
		        $box  = imagettfbbox ( $this->size, 0, $this->font, $line . $word );
		        $width = $box[4] - $box[0];
		        if ( $width > $this->width )
		        {
		            $this->lines[] = trim ( $line );
		            $line = '';
		        }
		        $line .= $word . ' ';
		    }
		    $this->lines[] = trim ( $line );		
		} else {
			$this->lines[] = $this->text;
			$box = @imagettfbbox( $this->size, 0, $this->font, $this->text );
			($box[2] > $box[4]) ? $mw = $box[2] : $mw = $box[4];
			$this->width = ($mw) + $this->margin; // + abs($mw*0.05); // weird condition of not being long enough
		}

		// calculate line height for this font by using common characters
	    $dimensions = imagettfbbox( $this->size, 0, $this->font, 'AJLMYabdfghjklpqry019`@$^&*(,' );
	    $this->lineHeight = (($dimensions[1] - $dimensions[5]) * $this->lineSpacing);
		$log["lineHeight"] = $this->lineHeight;

		// calculate baseline offset
	    $dimensions = imagettfbbox( $this->size, 0, $this->font, 'X' );
	    $this->baseLineOffset = ($dimensions[1] - $dimensions[5]) * $this->lineSpacing;
	    $log["baseLineOffset"] = $this->baseLineOffset;

	    // calculate tail offset
	    $dimensions = imagettfbbox( $this->size, 0, $this->font, 'X' );
	    $this->tailOffset = ($dimensions[1] - $dimensions[5]);
	    $log["baseLineOffset"] = $this->baseLineOffset;
			    
		$this->height = $this->lineHeight * count($this->lines); # distance from top to bottom			
   
		$log["width"] = $this->width;
		$log["height"] = $this->height;
		$log["lines"] = count($this->lines);	    

		$this->width += $this->tailOffset;
		$this->inputImage = imagecreatetruecolor($this->width * 3, $this->height * 3);
		
		// create background colour
		imagefill ( $this->inputImage, 0, 0, $this->allocateColour($this->bgcolour) );
		
		// create text colour
		$this->alloCol = $this->allocateColour($this->colour); //imagecolorallocate($this->inputImage, $bincolour[0], $bincolour[1], $bincolour[2]);
		
		/*
		
		$hexcolour = chunk_split(str_replace("#","",$this->colour), 2,":");
		$hexcolour = explode(":",$hexcolour);
		
		$bincolour[0] = hexdec("0x{$hexcolour[0]}");
		$bincolour[1] = hexdec("0x{$hexcolour[1]}");
		$bincolour[2] = hexdec("0x{$hexcolour[2]}");
		
		$this->colour = imagecolorallocate($this->inputImage, $bincolour[0], $bincolour[1], $bincolour[2]);
		*/
		
		# other colours
		$grey = imagecolorallocate($this->inputImage, 128, 128, 128);
		$black = imagecolorallocate($this->inputImage, 0, 0, 0);
		
		//if (@$_GET['shadow'])
		//{
		//	# add some shadow to the text
		//	imagettftext($this->inputImage, ($_GET['size']) * 3, 0, 2, 2, $grey, $this->font, $this->text);
		//}
		
		// Add the text
		$locX = $this->margin / 2;
		//$locY = $this->lineHeight - ($this->lineHeight - $this->baseLineOffset); //0; //($this->size * 3) + ($this->margin / 2);
		foreach($this->lines as $index => $line) {
			
			$log['line: ' . $index] =  $line;

			$locY = ($index * $this->lineHeight ) + $this->lineHeight - ($this->lineHeight - $this->baseLineOffset) + 1;
			imagettftext($this->inputImage, $this->size * 3, 0, $locX, $locY * 3, $this->alloCol, $this->font, $line);
		}
		
		// output image 3 times smaller
		$this->outputImage = imagecreatetruecolor($this->width, $this->height);
		imagecopyresampled($this->outputImage, $this->inputImage, 0, 0, 0, 0, $this->width, $this->height, $this->width * 3, $this->height * 3);
		
		// rotate?
		$this->outputImage = imagerotate($this->outputImage, intval(@$_GET['angle']), 0);
		
		$this->width = imagesx($this->outputImage);
		$this->height = imagesy($this->outputImage);
	}
	
	protected function getImageType() {
		if ($this->type=='jpg' || $this->type=='jpeg') { return 'jpeg'; } else { return 'png'; }
	}
}
