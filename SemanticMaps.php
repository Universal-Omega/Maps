<?php

/**
 * Initialization file for the Semantic Maps extension.
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( defined( 'SM_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

define( 'SM_VERSION', '3.0 beta' );

if ( version_compare( $GLOBALS['wgVersion'], '1.19c', '<' ) ) {
	throw new Exception( 'This version of Semantic Maps requires MediaWiki 1.18 or above;'
		. 'use Semantic Maps 1.0.x for MediaWiki 1.17 and Semantic Maps 0.7.x for older versions.' );
}

if ( !defined( 'Maps_VERSION' ) && is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	include_once( __DIR__ . '/vendor/autoload.php' );
}

if ( !defined( 'Maps_VERSION' ) ) {
	throw new Exception( 'You need to have Maps installed in order to use Semantic Maps' );
}

$wgExtensionCredits['semantic'][] = array(
	'path' => __FILE__,
	'name' => 'Semantic Maps',
	'version' => SM_VERSION,
	'author' => array(
		'[https://www.mediawiki.org/wiki/User:Jeroen_De_Dauw Jeroen De Dauw]'
	),
	'url' => 'https://semantic-mediawiki.org/wiki/Semantic_Maps',
	'descriptionmsg' => 'semanticmaps-desc'
);

$smgScriptPath 	= ( $GLOBALS['wgExtensionAssetsPath'] === false ? $GLOBALS['wgScriptPath'] . '/extensions' : $GLOBALS['wgExtensionAssetsPath'] ) . '/SemanticMaps';

// Include the settings file.
require_once 'SM_Settings.php';

# (named) Array of String. This array contains the available features for Maps.
# Commenting out the inclusion of any feature will make Maps completely ignore it, and so improve performance.

	# Query printers
	include_once __DIR__ . '/includes/queryprinters/SM_QueryPrinters.php';
	# Form imputs
	include_once __DIR__ . '/includes/forminputs/SM_FormInputs.php';

# Include the mapping services that should be loaded into Semantic Maps.
# Commenting or removing a mapping service will cause Semantic Maps to completely ignore it, and so improve performance.

	# Google Maps API v3
	include_once __DIR__ . '/includes/services/GoogleMaps3/SM_GoogleMaps3.php';

	# OpenLayers API
	include_once __DIR__ . '/includes/services/OpenLayers/SM_OpenLayers.php';

$GLOBALS['wgExtensionMessagesFiles']['SemanticMaps'] = __DIR__ . '/SemanticMaps.i18n.php';

// Hook for initializing the Geographical Data types.
$GLOBALS['wgHooks']['smwInitDatatypes'][] = 'SemanticMapsHooks::initGeoDataTypes';

// Hook for defining the default query printer for queries that ask for geographical coordinates.
$GLOBALS['wgHooks']['SMWResultFormat'][] = 'SemanticMapsHooks::addGeoCoordsDefaultFormat';

// Hook for adding a Semantic Maps links to the Admin Links extension.
$GLOBALS['wgHooks']['AdminLinks'][] = 'SemanticMapsHooks::addToAdminLinks';
