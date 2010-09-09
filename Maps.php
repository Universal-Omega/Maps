<?php

/**
 * Initialization file for the Maps extension.
 * 
 * On MediaWiki.org: 		http://www.mediawiki.org/wiki/Extension:Maps
 * Official documentation: 	http://mapping.referata.com/wiki/Maps
 * Examples/demo's: 		http://mapping.referata.com/wiki/Maps_examples
 *
 * @file Maps.php
 * @ingroup Maps
 *
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

/**
 * This documenation group collects source code files belonging to Maps.
 *
 * Please do not use this group name for other code. If you have an extension to
 * Maps, please use your own group defenition.
 *
 * @defgroup Maps Maps
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

// Include the Validator extension if that hasn't been done yet, since it's required for Maps to work.
if ( !defined( 'Validator_VERSION' ) ) {
	@include_once( dirname( __FILE__ ) . '/../Validator/Validator.php' );
}

// Only initialize the extension when all dependencies are present.
if ( ! defined( 'Validator_VERSION' ) ) {
	echo '<b>Warning:</b> You need to have <a href="http://www.mediawiki.org/wiki/Extension:Validator">Validator</a> installed in order to use <a href="http://www.mediawiki.org/wiki/Extension:Maps">Maps</a>.';
}
else {
	define( 'Maps_VERSION', '0.7 alpha-4' );

	// The different coordinate notations.
	define( 'Maps_COORDS_FLOAT', 'float' );
	define( 'Maps_COORDS_DMS', 'dms' );
	define( 'Maps_COORDS_DM', 'dm' );
	define( 'Maps_COORDS_DD', 'dd' );

	// The symbols to use for degrees, minutes and seconds.
	define( 'Maps_GEO_DEG', '°' );
	define( 'Maps_GEO_MIN', "'" );
	define( 'Maps_GEO_SEC', '"' );

	$useExtensionPath = version_compare( $wgVersion, '1.16', '>=' ) && isset( $wgExtensionAssetsPath ) && $wgExtensionAssetsPath;
	$egMapsScriptPath 	= ( $useExtensionPath ? $wgExtensionAssetsPath : $wgScriptPath . '/extensions' ) . '/Maps';
	$egMapsDir 			= dirname( __FILE__ ) . '/';
	unset( $useExtensionPath );

	$egMapsStyleVersion = $wgStyleVersion . '-' . Maps_VERSION;

	$egMapsFeatures = array();

	// Include the settings file.
	require_once $egMapsDir . 'Maps_Settings.php';

	$wgExtensionMessagesFiles['Maps'] = $egMapsDir . 'Maps.i18n.php';

	if ( version_compare( $wgVersion, '1.16alpha', '>=' ) ) {
		$wgExtensionMessagesFiles['MapsMagic'] = $egMapsDir . 'Maps.i18n.magic.php';
	}

	// Register the initialization function of Maps.
	$wgExtensionFunctions[] = 'efMapsSetup';

	$wgHooks['AdminLinks'][] = 'efMapsAddToAdminLinks';
	
	$wgHooks['UnitTestsList'][] = 'efMapsUnitTests';
}

/**
 * Initialization function for the Maps extension.
 * 
 * @since 0.1
 * 
 * @return true
 */
function efMapsSetup() {
	global $wgExtensionCredits, $wgLang, $wgAutoloadClasses;
	global $egMapsDefaultService, $egMapsAvailableServices;
	global $egMapsDir, $egMapsUseMinJs, $egMapsJsExt;

	// Autoload the "includes/" classes and interfaces.
	$incDir = dirname( __FILE__ ) . '/includes/';
	$wgAutoloadClasses['MapsMapper'] 				= $incDir . 'Maps_Mapper.php';
	$wgAutoloadClasses['MapsCoordinateParser'] 		= $incDir . 'Maps_CoordinateParser.php';
	$wgAutoloadClasses['MapsDistanceParser'] 		= $incDir . 'Maps_DistanceParser.php';
	$wgAutoloadClasses['MapsGeoFunctions'] 			= $incDir . 'Maps_GeoFunctions.php';
	$wgAutoloadClasses['MapsGeocoders'] 			= $incDir . 'Maps_Geocoders.php';
	$wgAutoloadClasses['MapsGeocoder'] 				= $incDir . 'Maps_Geocoder.php';
	$wgAutoloadClasses['iMappingFeature'] 			= $incDir . 'iMappingFeature.php';
	$wgAutoloadClasses['iMappingParserFunction'] 	= $incDir . 'iMappingParserFunction.php'; // TODO
	$wgAutoloadClasses['iMappingService'] 			= $incDir . 'iMappingService.php';
	$wgAutoloadClasses['MapsMappingServices'] 		= $incDir . 'Maps_MappingServices.php';
	$wgAutoloadClasses['MapsMappingService'] 		= $incDir . 'Maps_MappingService.php';	
	
	// Autoload the "includes/criteria/" classes.
	$criDir = $incDir . 'criteria/';
	$wgAutoloadClasses['CriterionAreLocations'] 	= $criDir . 'CriterionAreLocations.php';
	$wgAutoloadClasses['CriterionMapDimension'] 	= $criDir . 'CriterionMapDimension.php';
	
	// Autoload the "includes/features/" classes.
	$ftDir = $incDir . '/features/';
	$wgAutoloadClasses['MapsBaseMap'] 				= $ftDir . 'Maps_BaseMap.php';
	$wgAutoloadClasses['MapsBasePointMap'] 			= $ftDir . 'Maps_BasePointMap.php';	
	
	// Autoload the "includes/geocoders/" classes.
	$geoDir = $incDir . 'geocoders/';
	$wgAutoloadClasses['MapsGeonamesGeocoder'] 		= $geoDir . 'Maps_GeonamesGeocoder.php';
	$wgAutoloadClasses['MapsGoogleGeocoder'] 		= $geoDir . 'Maps_GoogleGeocoder.php';
	$wgAutoloadClasses['MapsYahooGeocoder'] 		= $geoDir . 'Maps_YahooGeocoder.php';
	
	// Autoload the "includes/parserHooks/" classes.
	$phDir = $incDir . '/parserHooks/';
	$wgAutoloadClasses['MapsCoordinates'] 			= $phDir . 'Maps_Coordinates.php';
	$wgAutoloadClasses['MapsDisplayMap'] 			= $phDir . 'Maps_DisplayMap.php';
	$wgAutoloadClasses['MapsDisplayPoint'] 			= $phDir . 'Maps_DisplayPoint.php';
	$wgAutoloadClasses['MapsDistance'] 				= $phDir . 'Maps_Distance.php';
	$wgAutoloadClasses['MapsFinddestination'] 		= $phDir . 'Maps_Finddestination.php';
	$wgAutoloadClasses['MapsGeocode'] 				= $phDir . 'Maps_Geocode.php';
	$wgAutoloadClasses['MapsGeodistance'] 			= $phDir . 'Maps_Geodistance.php';

	// This function has been deprecated in 1.16, but needed for earlier versions.
	// It's present in 1.16 as a stub, but lets check if it exists in case it gets removed at some point.
	if ( function_exists( 'wfLoadExtensionMessages' ) ) {
		wfLoadExtensionMessages( 'Maps' );
	}
	
	// To ensure Maps remains compatible with pre 1.16.
	if ( !array_key_exists( 'Html', $wgAutoloadClasses ) ) {
		$wgAutoloadClasses['Html'] = $egMapsDir . 'compat/Html.php';
	}	
	
	wfRunHooks( 'MappingServiceLoad' );
	wfRunHooks( 'MappingFeatureLoad' );

	// Creation of a list of internationalized service names.
	$services = array();
	foreach ( MapsMappingServices::getServiceIdentifiers() as $identifier ) $services[] = wfMsg( 'maps_' . $identifier );
	$servicesList = $wgLang->listToText( $services );

	$wgExtensionCredits['parserhook'][] = array(
		'path' => __FILE__,
		'name' => wfMsg( 'maps_name' ),
		'version' => Maps_VERSION,
		'author' => array(
			'[http://www.mediawiki.org/wiki/User:Jeroen_De_Dauw Jeroen De Dauw]',
			'[http://www.mediawiki.org/wiki/Extension:Maps/Credits others]'
		),
		'url' => 'http://www.mediawiki.org/wiki/Extension:Maps',
		'description' => wfMsgExt( 'maps_desc', 'parsemag', $servicesList ),
	);

	MapsMapper::initialize();

	$egMapsJsExt = $egMapsUseMinJs ? '.min.js' : '.js';

	return true;
}

/**
 * Adds a link to Admin Links page.
 * 
 * @since 0.2
 * 
 * @return true
 */
function efMapsAddToAdminLinks( &$admin_links_tree ) {
    $displaying_data_section = $admin_links_tree->getSection( wfMsg( 'smw_adminlinks_displayingdata' ) );

    // Escape if SMW hasn't added links.
    if ( is_null( $displaying_data_section ) ) return true;
    $smw_docu_row = $displaying_data_section->getRow( 'smw' );

    $maps_docu_label = wfMsg( 'adminlinks_documentation', wfMsg( 'maps_name' ) );
    $smw_docu_row->addItem( AlItem::newFromExternalLink( 'http://mapping.referata.com/wiki/Maps', $maps_docu_label ) );

    return true;
}

/**
 * Hook to add PHPUnit test cases.
 * 
 * @since 0.6.5
 * 
 * @param array $files
 */
function efMapsUnitTests( array &$files ) {
	$testDir = dirname( __FILE__ ) . '/test/';
	//$files[] = $testDir . 'MapsCoordinateParserTest.php';
	return true;
}