<?php

abstract class ImageTools_ImageHandler {

	public $noCache;
	public $doLog = false;

	public $inputImage;
	public $outputImage;
	
	public $newImageType;
	
	public $width;
	public $height;
	
	public $fileName = "";
	
	private static $cacheDir = '/cache/';
	private static $imageType;
	private static $instance = null;
	private $cached = false;
	
	protected $log = array();
	protected $cacheParams = array();
	
	public function __construct()
	{
		if (!defined('DOCUMENT_ROOT')) define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
		
		ini_set('memory_limit','256M');
	}

	/*
	public static function getInstance() {
		if (!self::$instance instanceof self) { 
			self::$instance = new self;
		}
		return self::$instance;
	}
	*/
	
	public function prepare() { 

		$this->imageType = $this->getImageType();

		// create the cache id
		$this->cacheFile = str_replace('//','/', DOCUMENT_ROOT . self::$cacheDir . $this->fileName . "-" . md5($this->getHash()) . '.' . $this->imageType);
		
		if (($this->noCache) && (file_exists($this->cacheFile))) {
            unlink($this->cacheFile);
        } else {
            // create cache directory if possible and it doesn't exist
            if (!is_dir(DOCUMENT_ROOT . self::$cacheDir) ) {
                if (!mkdir(DOCUMENT_ROOT . self::$cacheDir, 0700)) {
                    // couldn't create cache directory
                    die("Couldn't create cache directory ".DOCUMENT_ROOT . self::$cacheDir);
                }
            }
        }

		// get from cache
		if (file_exists($this->cacheFile)) {
			$this->cached = true;		
		}
	
		return true;
	}

	/**
	 * Resolves and finds a file passed to it
	 *
	 * @param unknown_type $file
	 * @return unknown
	 */
	protected function getFile($file) {
		$file = urldecode($file);
	
		if (!@file_get_contents($file))
		{
			if (substr($file,0,1) == "/") $file = substr($file,1);
			$file = DOCUMENT_ROOT . "/" . $file;

			if (!@file_get_contents($file)) {
				return false;
			}
		}
		return $file;
	}
	
	protected function isImage($file) {
		if (is_array(getimagesize($file))) return true;
	}
	
	/**
	 * returns the image reference after it's created the image
	 */
	public function get($params = null) {
		
		// create parameters based on array object
		if (count($params)>0) {
			$this->cacheParams = $params;
			if (is_array($params)) {
				foreach($params as $key => $value) {
					$this->$key = $value;
				}
			}
		}

		if ($this->prepare()) {
			if ($this->cached) {
				
				// get width/height for image for use in tags
				$result = getimagesize($this->cacheFile);
				$this->width = $result[0];
				$this->height = $result[1];
				
				return str_replace(DOCUMENT_ROOT,'', $this->cacheFile);
			}
			
			$this->build();
	
			if ($this->newImageType) {
				$this->imageType = $this->newImageType;
			}

			switch ($this->imageType)
			{
				case "jpeg":
					@imagejpeg($this->outputImage, $this->cacheFile, $this->quality);
					break;
					
				case "png":
					@imagepng($this->outputImage, $this->cacheFile);
					break;
					
				case "gif":
					if (function_exists('imagegif'))
					{
						imagegif($this->outputImage, $this->cacheFile);
					} else {
						imagejpeg($this->outputImage, $this->cacheFile);
					}
					break;
			}
			return str_replace(DOCUMENT_ROOT,'', $this->cacheFile);
		}
	}
	
	/**
	 * discovers parameters set in the object and uses this to create the cache identifier
	 *
	 */
	private function getHash() {
		//self::implodeWithKey(get_object_vars($this));

		if (count($this->cacheParams) > 0) {
			// singleton - get params from get method
			return $this->implodeWithKey($this->cacheParams);
		} else {
			$this->cacheParams = get_object_vars($this);
			return $this->implodeWithKey(get_object_vars($this));
		}
	}
		
	/**
	 * used to implode arrays with their keys for a hash reference
	 *
	 * @param unknown_type $assoc
	 * @param unknown_type $inglue
	 * @param unknown_type $outglue
	 * @return unknown
	 */
	private function implodeWithKey($assoc, $inglue = '=', $outglue = '&')
	{
		foreach ($assoc as $tk => $tv) 
		{
			@$return = (isset($return) ? $return . $outglue : '') . $tk . $inglue . $tv;
		}
		return $return;
	}

	/**
	 * renders the file out as a binary
	 *
	 */
	public function render() {

		if ($this->prepare()) {
			if ($this->cached) {
				header("Content-type: image/".$this->imageType);
				echo file_get_contents($this->cacheFile);
				exit;
			}

			if (DOCUMENT_ROOT . $this->get($this->cacheParams) == $this->cacheFile) {
				header("Content-type: image/".$this->imageType);
				echo file_get_contents($this->cacheFile);
				exit;
			}
		}
	}
	
	/**
	 * renders the file out as a binary to a file
	 *
	 */
	public function renderFile($filename) {

		if ($this->prepare()) {
			if ($this->cached) {
				file_put_contents($filename, file_get_contents($this->cacheFile));
			}

			if (DOCUMENT_ROOT . $this->get($this->cacheParams) == $this->cacheFile) {
				file_put_contents($filename, file_get_contents($this->cacheFile));
			}
		}
	}
	
	
	/**
	 * allocates a colour to the input image
	 *
	 * @param unknown_type $hex
	 * @return unknown
	 */
	protected function allocateColour($hex) {
		$hexcolour = chunk_split(str_replace("#","",$hex), 2, ":");
		$hexcolour = explode(":",$hexcolour);
		
		// Convert HEX values to DECIMAL
		$bincolour[0] = hexdec("0x{$hexcolour[0]}");
		$bincolour[1] = hexdec("0x{$hexcolour[1]}");
		$bincolour[2] = hexdec("0x{$hexcolour[2]}");
	
		return imagecolorallocate($this->inputImage, $bincolour[0], $bincolour[1], $bincolour[2]);		
	}
}
