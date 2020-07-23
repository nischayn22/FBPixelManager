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
			'fbpixelid_map',
			[ 'page_id', 'pixel_id'],
			true,
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
						<td>'. Title::newFromID( $row->page_id )->getFullText() .'</td>
						<td>'. $row->pixel_id .'</td>
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

			$title = str_replace( ' ', "_", end( explode( '/', urldecode( $pagename ) ) ) );
			$title = Title::newFromText( $title );

			if ( !$title || !$title->getArticleID() ) {
				continue;
			}

			$existingValue = $dbr->selectField(
				'fbpixelid_map',
				'pixel_id',
				[ 'page_id' => $title->getArticleID() ],
				__METHOD__
			);
			if ( !$existingValue ) {
				$dbw->insert(
					'fbpixelid_map',
					[ 'page_id' => $title->getArticleID(), 'pixel_id' => $formData['pixel_id'] ],
					__METHOD__
				);
			} else {
				$dbw->update(
					'fbpixelid_map',
					[ 'pixel_id' => $formData['pixel_id'] ],
					[ 'page_id' => $title->getArticleID() ],
					__METHOD__
				);
			}
		}
		return 'Success';
	}

}
