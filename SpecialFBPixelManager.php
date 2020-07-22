<?php

class SpecialFBPixelManager extends SpecialPage {

	public function __construct() {
		parent::__construct( 'FBPixelManager', 'fbpixelmanager' );
	}

	function execute( $query ) {
		$this->checkPermissions();
		$dbr = wfGetDB( DB_REPLICA );

		$this->getOutput()->setPageTitle( 'FBPixelManager' );
		$this->setHeaders();
		$request = $this->getRequest();
		$out = $this->getOutput();

		$formDescriptor = [
			'pagenames' => [
				'type' => 'textarea',
				'label' => 'Page Names',
				'placeholder' => 'Enter one page names/URL per line',
				'rows' => 5, //  Display height of field 
			],
			'pixel_id' => [
				'label' => 'Pixel ID',
				'class' => 'HTMLTextField',
			]
		];

		$htmlForm = new HTMLForm( $formDescriptor, $this->getContext() );
		$htmlForm
			->setSubmitText( 'Update' )
			->setSubmitCallback( [ $this, 'trySubmit' ] )
			->show();

		$res = $dbr->select( 
			'page_props',
			[ 'pp_page', 'pp_value'],
			array( 'pp_propname' => 'fb_pixel_id' ),
			__METHOD__
		);

		$out->addHTML( '
			<br>
			<br>
			<h3>Existing Pixel IDs</h3>
			<table class="wikitable">
				<tr>
					<th>Page</th>
					<th>Pixel ID</th>
				</tr>
		' );
		foreach( $res as $row ) {
			$out->addHTML( '
					<tr>
						<td>'. Title::newFromID( $row->pp_page )->getFullText() .'</td>
						<td>'. $row->pp_value .'</td>
					</tr>
			' );
		}
		$out->addHTML( '
			</table>
		' );
	}

	public function trySubmit( $formData ) {
		$dbr = wfGetDB( DB_REPLICA );
		$dbw = wfGetDB( DB_MASTER );

		$page_names = explode( "\n", $formData['pagenames'] );

		foreach( $page_names as $pagename ) {
			if ( empty( $pagename ) ) {
				break;
			}

			$title = basename( $pagename );
			$title = Title::newFromText( $title );

			if ( !$title || !$title->getArticleID() ) {
				return "Invalid Page: " . $pagename;
			}

			$existingValue = $dbr->selectField(
				'page_props',
				'pp_value',
				[ 'pp_page' => $title->getArticleID(), 'pp_propname' => 'fb_pixel_id' ],
				__METHOD__
			);
			if ( !$existingValue ) {
				$dbw->insert(
					'page_props',
					[ 'pp_page' => $title->getArticleID(), 'pp_propname' => 'fb_pixel_id', 'pp_value' => $formData['pixel_id'] ],
					__METHOD__
				);
			} else {
				$dbw->update(
					'page_props',
					[ 'pp_value' => $formData['pixel_id'] ],
					[ 'pp_page' => $title->getArticleID(), 'pp_propname' => 'fb_pixel_id' ],
					__METHOD__
				);
			}
		}
		return 'Success';
	}

}
