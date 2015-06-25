#PHP Thumbnail Script

A script for converting images to different sizes inline, with support for server caching.

<img src="http://mightystuff.net/thumb.php?size=25&file=content/2015/06/sample.jpg" align="middle"/>&nbsp;
<img src="http://mightystuff.net/thumb.php?size=50&file=content/2015/06/sample.jpg" align="middle"/>&nbsp;
<img src="http://mightystuff.net/thumb.php?size=100&file=content/2015/06/sample.jpg" align="middle"/>&nbsp;
<img src="http://mightystuff.net/thumb.php?size=200&file=content/2015/06/sample.jpg" align="middle"/>&nbsp;


Very useful for creating multiple sizes of image urls from single images. Creates a thumbnail image depending on variables passed to it via get.

Images are cached on the server, so server processing overhead is only needed for the first time the script runs on a particular image.

###Usage:

    <img src="thumb.php?file=FILE&size=SIZE"> - to size the thumbnail  to fit a square

    <img src="thumb.php?file=FILE&sizex=SIZEX&sizey=SIZEY"> - to  size the thumbnail to fit a rectangle

    <img src="thumb.php?file=FILE&size=SIZE&quality=QUALITY&nocache=NOCACHE"> -  to set a thumbnails quality
Where:

FILE = the file to retrieve
SIZE = the maximum size of the thumbnail in pixels
SIZEX = the maximum width of the thumbnail (height adjusted accordingly)
SIZEY = the maximum height of the thumbnail (width adjusted accordingly)
QUALITY = an integer from 0-100 specifying the resulting jpeg quality of the image
NOCACHE = an integer 1 or 0. If set to 1, the cached thumbnail is deleted and recreated (use if the source image changes)
###Features:

* Server caching of images
* Creation of jpeg thumbnails inline, on the fly
* Can be used for non-local images, if allow_fopen_url in the php configuration is set to true

###Requires:

GD Library

Installation:

The script should work with GD versions 1 and 2, and Linux and Windows servers

Licensed under the MIT License.
Copyright © 2005 Chris Tomlinson.

###Version Changes:

Changes:

0.1 – first release<br/>
0.2 – converted cache thumbnail from png to jpeg<br/>
0.3 – fixed error where files weren’t being cached properly<br/>
0.4 – allowed non local urls (if allow_url_fopen is on), quality and nocache switches<br/>
0.5 – allowed maximum x and y settings (for scaling images to fit non square sizes)<br/>
0.6 – allowed tagging of images (with the get query placing the text in the bottom left hand corner of the image)<br/>
0.7 – fixed gd_info error for php<4.3<br/>
0.8 – added gif support (for gd 2.0.28)<br/>
0.9 – now supports native outputting of png, jpg and gif formats<br/>
1.0 – doesn’t fail if the cache file can’t be created<br/>
1.1 – removed a few more notices
