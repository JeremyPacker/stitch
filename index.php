<?php
/* Stitch API v1.0 */

// Define the SECURE variable to enable loading of other server files
define( 'SECURE', true );

// Globally required files
require 'functions.php';
require 'config.php';

// Get a list of available classes
$availableClasses = scandir( 'class' );

// Break up the URI
@list( $resourceClass, $resourceIdentifier, $parameters ) = explode( '/', trim( $_SERVER['REQUEST_URI'], '/' ), 3 );

$resourceClass = ucfirst( strtolower( $resourceClass ) );
$resourceType = strtolower( pathinfo( $resourceIdentifier, PATHINFO_EXTENSION ) );
$resourceIdentifier = strtolower( pathinfo( $resourceIdentifier, PATHINFO_FILENAME ) );
$method = strtolower( $_SERVER['REQUEST_METHOD'] );
$parameters = !empty( $parameters ) ? createKeyValuePairsFromString( $parameters, '/' ) : array();

// Handle the alias/id
if ( $resourceIdentifier && $resourceIdentifier !== 'all' ) {
  // Figure out if the search parameter is a valid MongoID. If not, assume it's an alias.
  try {
    $parameters['_id'] = new MongoId( $resourceIdentifier );
  } catch ( Exception $e ) {
    $parameters['alias'] = $resourceIdentifier;
  };
};

// Set Access-Control-Allow-Origin header ( prevents access to the API from other sites )
if ( defined( 'CORS_DOMAIN' ) ) {
  header( 'Access-Control-Allow-Origin: http://trezy.com' );
} else {
  header( 'Access-Control-Allow-Origin: *' );
};

if ( $method === 'OPTIONS' ) {
  // TODO: Respond with more API documentation
  header( 'HTTP/1.1 200 OK', true, 200 );
  header( 'Allow: GET, PUT, POST, DELETE, OPTIONS' );

} else if ( $resourceClass ) {
  // Load and instantiate the appropriate class
  if ( class_exists( $resourceClass ) ) {
    $resource = new $resourceClass( $parameters );
    $resource -> {$method}();

  } else { header( 'HTTP/1.1 501 Not Implemented', true, 501 ); };

} else { header( 'HTTP/1.1 400 Bad Request', true, 400 ); };
?>
