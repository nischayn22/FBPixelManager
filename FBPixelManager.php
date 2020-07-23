<?php

class FBPixelManager {

	/**
	 *
	 * @param DatabaseUpdater $updater
	 * @return boolean
	 */
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$updater->addExtensionTable( 'fbpixelid_map',
			__DIR__ . '/fbpixelid_map.sql', true );
		return true;
	}

	public static function onBeforeInitialize( &$title, &$article, &$output, &$user, $request, $mediaWiki ) {
		global $wgFBPixel;
		$dbr = wfGetDB( DB_REPLICA );

		if ( !empty( $wgFBPixel ) ) {
			$pixel_id = $wgFBPixel;
		}

		$existingValue = $dbr->selectField(
			'fbpixelid_map',
			'pixel_id',
			[ 'page_id' => $title->getArticleID() ],
			__METHOD__
		);

		if ( $existingValue ) {
			$pixel_id = $existingValue;
		} else {
			$existingValue = $dbr->selectField(
				'fbpixelid_map',
				'pixel_id',
				[ 'page_id' => Title::newMainPage()->getArticleID() ],
				__METHOD__
			);
			if ( $existingValue ) {
				$pixel_id = $existingValue;
			}
		}

		if ( empty( $pixel_id ) ) {
			return;
		}

		$pixel = '<!-- Facebook Pixel Code -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version="2.0";
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,"script",
  "https://connect.facebook.net/en_US/fbevents.js");
  fbq("init", "'. $pixel_id .'");
  fbq("track", "PageView");
</script>
<noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id='. $pixel_id .'&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->
';

		$output->addHeadItem( 'pixel', $pixel );
	}
}
