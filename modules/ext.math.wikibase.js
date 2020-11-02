( function () {
	'use strict';
	// eslint-disable-next-line no-jquery/no-global-selector
	var $wbEntitySelector = $( '#wbEntitySelector' );
	if ( $wbEntitySelector.length ) {
		OO.ui.infuse( $wbEntitySelector );
	}
}() );
