# FBPixelManager
A MediaWiki extension that lets you set custom FB pixels.

#Installation

For installation of this extension you need to have ssh access to your server.

* To install the extension, place the entire 'FBPixelManager' directory within your MediaWiki 'extensions' directory
* Just enter the following command in the 'extensions' directory: 'git clone https://github.com/nischayn22/FBPixelManager.git FBPixelManager'
* Add the following line to your LocalSettings.php file: 'wfLoadExtension( 'FBPixelManager' );'
* Verify you have this extension installed by visiting the /Special:Version page on your wiki.

#Usage
To set pixels visit the page Special:FBPixelManager and enter a list of pages/URLs and the pixel id for those.
To set a default pixel value for the entire website set a default pixel id for the "Main Page"


#Credits
This extension has been written by Nischay Nahata for wikiworks.com and is sponsored by WikiRefua
