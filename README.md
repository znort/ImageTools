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

###Features:

* Server caching of images
* Creation of jpeg thumbnails inline, on the fly
* Can be used for non-local images, if allow_fopen_url in the php configuration is set to true

###Parameters:

<table cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td><b>Parameter</b></td>
        <td><b>Description</b></td>
        <td><b>Example</b></td>
        <td><b>Default</b></td>
        <td><b>Allowed Values</b></td>
    </tr>
    <tr>
        <td>file</td>
        <td>The file to use. This parameter is required.</td>
        <td>thumb.php?file=sample.jpg<br/><img src="http://mightystuff.net/ImageTools/thumb.php?file=ImageTools/sample.jpg&size=200"></td>
        <td>N/A</td>
        <td>- path to file

            >- url to an image resource if php.ini setting allow_url_fopen or allow_url_include is set to true</td>
    </tr>
    <tr>
        <td>size</td>
        <td>Sets the size of the image. If not set, will output the image at its native size. Maintains aspect ratio. </td>
        <td>thumb.php?file=sample.jpg&size=100<br/><img src="http://mightystuff.net/ImageTools/thumb.php?file=ImageTools/sample.jpg&size=100"></td>
        <td>Native image size</td>
        <td>&gt;0</td>
    </tr>
    <tr>
        <td>sizex</td>
        <td>Sets the maximum width of the image. Maintains aspect ratio.</td>
        <td>thumb.php?file=sample.jpg&size=100<br/><img src="http://mightystuff.net/ImageTools/thumb.php?file=ImageTools/sample.jpg&sizex=100"></td>
        <td>Not set</td>
        <td>&gt;0</td>
    </tr>
    <tr>
        <td>sizey</td>
        <td>Sets the maximum height of the image. Maintains aspect ratio.</td>
        <td>thumb.php?file=sample.jpg&size=100<br/><img src="http://mightystuff.net/ImageTools/thumb.php?file=ImageTools/sample.jpg&sizey=100"></td>
        <td>Not set</td>
        <td>&gt;0</td>
    </tr>
    <tr>
        <td>cropToSize</td>
        <td>Crop an image to a specific size. eg. 100,100 will crop the image to be a square 100 pixels wide and high.</td>
        <td>thumb.php?file=sample.jpg&cropToSize=100,100<br/><img src="http://mightystuff.net/ImageTools/thumb.php?file=ImageTools/sample.jpg&cropToSize=100,100"></td>
        <td>Not set</td>
        <td valign="middle"></td>
    </tr>
    <tr>
        <td>crop</td>
        <td>Crop an image to specific ratios of width and height. eg. 0,0,0.5,0.5 will crop the image to the top left quarter, 0.5,0.5,1,1 will crop the image to the bottom right quarter. Maintains aspect ratio and original size of image if not set.</td>
        <td>thumb.php?file=sample.jpg&crop=0,0,0.5,0.5<br/><img src="http://mightystuff.net/ImageTools/thumb.php?file=ImageTools/sample.jpg&crop=0,0,0.5,0.5"></td>
        <td>Not set</td>
        <td>[left crop],[top crop],[right crop],[bottom crop]

            &nbsp;

            >where 

            &nbsp;

            >[left crop] &gt;0 &lt;1

            >[top crop] &gt;0 &lt;1

            >[right crop] &gt;0 &lt;1

            >[bottom crop] &gt;0 &lt;1</td>
    </tr>
    <tr>
        <td>scaleUp</td>
        <td>If true, will scale an image up to fit the required size if it's native size is smaller. If this is false and the size is bigger than the native size the image will render at the native size.</td>
        <td>thumb.php?file=sample.jpg&size=400&scaleUp=true<br/><img src="http://mightystuff.net/ImageTools/thumb.php?file=ImageTools/sample.jpg&size=400&scaleUp=true"></td>
        <td>FALSE</td>
        <td>true / false</td>
    </tr>
    <tr>
        <td>quality</td>
        <td>Sets the jpeg compression level</td>
        <td>thumb.php?file=sample.jpg&size=100&quality=20<br/><img src="http://mightystuff.net/ImageTools/thumb.php?file=ImageTools/sample.jpg&size=100&quality=20"></td>
        <td>85</td>
        <td>&gt;0

            >&lt;100</td>
    </tr>
    <tr>
        <td>alt</td>
        <td>Image file to use if the one requested with the file parameter doesn't exist.</td>
        <td>thumb.php?file=i-dont-exist.jpg&alt=missing.jpg<br/><img src="http://mightystuff.net/ImageTools/thumb.php?file=ImageTools/idontexist.jpg&size=100&alt=ImageTools/missing.png"></td>
        <td valign="middle"></td>
        <td valign="middle"></td>
    </tr>
    </tbody>
</table>

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
