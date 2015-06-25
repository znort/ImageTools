<?php
/*
Thumbnail Class v1.4
______________________________________________________________________
Creates a thumbnailed image based on info passed to it and returns either the raw image data or a reference to a cached thumbnail file

Images are cached on the server, so server processing overhead is only
needed for the first time the script runs on a particular image.
______________________________________________________________________
Requires:
GD Library
______________________________________________________________________
Copyright:
(C) 2003 Chris Tomlinson. christo@mightystuff.net
http://mightystuff.net

Licensed under the MIT License.
http://opensource.org/licenses/MIT
______________________________________________________________________
Configuration:
set $thumb_size to be a default maximum width/height if not passed via get
set $image_error to be an image to be used when there is a problem parsing the image
set $site_config['path_thumbnail'] to be a write-permissable folder on your server relative to the DOCUMENT_ROOT for storing images so they cache on the server.
set $quality to be a value 0-100 for the resulting thumbnail jpeg quality
______________________________________________________________________
Usage:
<img src="image/thumbnail?file=FILE&size=SIZE">

Where:
FILE = the file to retrieve
SIZE = the maximum size of the thumbnail in pixels
______________________________________________________________________
Changes:
0.1 - first release
0.2 - converted cache thumbnail from png to jpeg
0.3 - fixed error where files weren't being cached properly
0.4 - allowed non local urls (if allow_url_fopen is on), quality and nocache switches
0.5 - allowed maximum x and y settings (for scaling images to fit non square sizes)
0.6 - allowed tagging of images (with the get query placing the text in the bottom left hand corner of the image)
0.7 - fixed gd_info error for php<4.3
0.8 - added gif support (for gd 2.0.28)
0.9 - now supports native outputting of png, jpg and gif formats
1.0 - doesn't fail if the cache file can't be created
1.1 - removed a few more notices
1.2 - rounded corners are now possible
1.3 - fixed alpha png black background
1.4 - fixed fopen problem
1.5 - doesn't enlarge and does center an image on the white background if image's requested size is greater than original
1.6 - domain locking applied -> thumb_domains.txt file is required and contains a list of domains allowed to use this script (backwards-compatible)
    - thumb_domains.txt should contain coma separated domain list i.e. (www.domain.co.uk) with no "http://" prefix
*/
if (!defined('DOCUMENT_ROOT')) define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

if (!isset($_GET['quality'])) $_GET['quality'] = 85;                    // will default to a compression level of 85 if the quality parameter is not set
if (!isset($_GET['alt'])) $_GET['alt'] = "/missing.png";   // replace this with an image that is to be used as a placeholder when the image being processed doesn't exist and the 'alt' parameter is not set.

if (isset($_GET['id'])) {
	$image = ImageTools_DatabaseImage::getImage($_GET['id'],@$_GET['size'],@$_GET['sizex'],@$_GET['sizey'], false, $_GET['quality'], $_GET['alt'], @$_GET['crop']);
} else {
	require_once("ImageTools/ThumbNail.php");
	if (!isset($_GET['file']) || is_dir($_GET['file'])) {
        $_GET['file']="";
    }
	if (substr($_GET['file'],0,1)=="/") {
        $_GET['file'] = substr($_GET['file'],1);
    }
    $parsed = parse_url($_GET['file']);
	if (!file_exists(DOCUMENT_ROOT . '/' . $_GET['file']) && empty($parsed['scheme'])) {
        $_GET['file'] = $_GET['alt'];
    }
	 
	$image = new ImageTools_ThumbNail();

	$image->file = $_GET['file'];
	if (isset($_GET['size'])) $image->size = $_GET['size'];
	if (isset($_GET['sizex'])) $image->sizex = $_GET['sizex'];
	if (isset($_GET['sizey'])) $image->sizey = $_GET['sizey'];
	$image->scaleUp = @$_GET['scaleUp'];
	$image->quality = @$_GET['quality'];
	
	if (@$_GET['cx1']) {
		$x1 = $_GET['cx1'];
		$y1 = $_GET['cy1'];
		$x2 = $_GET['cx2'];
		$y2 = $_GET['cy2'];
		$image->crop($x1,$y1,$x2,$y2);
	}
    // crop an image to specific ratios of width and height
    if (@$_GET['crop']) {
        $points=explode(",", $_GET['crop']);
        $x1 = $points[0];
        $y1 = $points[1];
        $x2 = $points[2];
        $y2 = $points[3];
        $image->crop($x1,$y1,$x2,$y2);
    }
    if (@$_GET['cropToSize']) {
        $points=explode(",", $_GET['cropToSize']);
        $x = $points[0];
        $y = $points[1];
        $image->cropToSize($x,$y);
    }
}
if (is_object($image)) {
	$image->render();
}
