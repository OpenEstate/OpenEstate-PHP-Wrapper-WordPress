<?php
/*
Plugin Name: OpenEstate PHP-Wrapper
Plugin URI: https://wordpress.org/plugins/openestate-php-wrapper/
Description: This plugin integrates OpenEstate-PHP-Export 1.x into a WordPress blog.
Version: 0.4-SNAPSHOT
Author: Andreas Rudolph, Walter Wagner (OpenEstate.org)
Author URI: https://openestate.org/
*/

/** @noinspection PhpUnusedParameterInspection */

use \OpenEstate\PhpExport\Environment;
use \OpenEstate\PhpExport\Utils;
use \OpenEstate\PhpExport\WrapperConfig;
use const \OpenEstate\PhpExport\VERSION;
use function htmlspecialchars as html;

/**
 * Init script environment.
 *
 * @param string $scriptPath Path, that contains to the script environment.
 * @param string $scriptUrl URL, that points to the script environment.
 * @param boolean $initSession Initialize the user session.
 * @param array $errors Errors during initialization.
 *
 * @return Environment The initialized environment or null, if initialization failed.
 */
function openestate_wrapper_env( $scriptPath, $scriptUrl, $initSession, &$errors ) {
	if ( ! is_dir( $scriptPath ) ) {
		$errors[] = __( 'Please enter a valid script path.', 'openestate-php-wrapper' );

		return null;
	}

	//echo '<pre>'.print_r($_SERVER, true).'</pre>';
	//echo '<pre>'.print_r($_COOKIE, true).'</pre>';

	if ( is_file( $scriptPath . 'include/functions.php' ) ) {
		/** @noinspection PhpIncludeInspection */
		require_once( $scriptPath . 'include/functions.php' );

		$oldVersionNumber = ( defined( 'IMMOTOOL_SCRIPT_VERSION' ) ) ? IMMOTOOL_SCRIPT_VERSION : '???';
		$errors[]         = __( 'It seems, that you\'re using an unsupported version of PHP export.', 'openestate-php-wrapper' )
		                    . ' (' . $oldVersionNumber . ')';
	} else if ( ! is_file( $scriptPath . 'index.php' ) ||
	            ! is_file( $scriptPath . 'expose.php' ) ||
	            ! is_file( $scriptPath . 'fav.php' ) ||
	            ! is_file( $scriptPath . 'config.php' ) ||
	            ! is_dir( $scriptPath . 'include' ) ||
	            ! is_dir( $scriptPath . 'include/OpenEstate' ) ||
	            ! is_file( $scriptPath . 'include/init.php' )
	) {
		$errors[] = __( 'It seems, that there is no PHP export available within the script path.', 'openestate-php-wrapper' );
	}
	if ( count( $errors ) > 0 ) {
		return null;
	}

	/** @noinspection PhpIncludeInspection */
	require_once( $scriptPath . 'include/init.php' );

	/** @noinspection PhpIncludeInspection */
	require_once( $scriptPath . 'config.php' );

	if ( ! defined( 'OpenEstate\PhpExport\VERSION' ) ) {
		$errors[] = __( 'Can\'t detect the script version.', 'openestate-php-wrapper' );

		return null;
	}

	require_once( __DIR__ . '/config.php' );

	try {
		$config = new WrapperConfig( $scriptPath, $scriptUrl );

		//echo '<pre>' . print_r( $config, true ) . '</pre>';
		return new Environment( $config, $initSession );
	} catch ( \Exception $e ) {
		$errors[] = __( 'Can\'t init script environment.', 'openestate-php-wrapper' ) . ' ' . $e->getMessage();;

		return null;
	}
}

// Init the plugin.
// see https://codex.wordpress.org/Plugin_API/Action_Reference/init
add_action( 'init', 'openestate_wrapper_init' );

/**
 * Init the plugin.
 *
 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/init
 */
function openestate_wrapper_init() {

	// init translations
	// see https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
	load_plugin_textdomain(
		'openestate-php-wrapper',
		false,
		'openestate-php-wrapper/languages'
	);
}

// Init the script environment.
// see https://codex.wordpress.org/Plugin_API/Action_Reference/wp
add_action( 'wp', 'openestate_wrapper_wp' );

/**
 * Init the script environment.
 *
 * @return void
 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/wp
 */
function openestate_wrapper_wp() {
	//$vars = array_keys( $GLOBALS );
	//die( '<pre>' . print_r( $vars, true ) . '</pre>' );

	// don't init the environment in the admin section
	if ( is_admin() ) {
		return;
	}

	// don't init the environment, if we're not on a singular page view
	if ( ! is_singular() ) {
		return;
	}

	// don't init the environment for certain pages
	if ( isset( $GLOBALS['pagenow'] ) && in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) {
		return;
	}

	// don't init the environment, if it was already loaded
	if ( isset( $GLOBALS['openestate']['wrapper'] ) ) {
		return;
	}

	/**
	 * current posting
	 * @var WP_Post
	 */
	$post = get_post();

	// don't init the environment, if no post was found
	if ( ! is_object( $post ) ) {
		return;
	}
	//die( '<pre>' . print_r( $post, true ) . '</pre>' );

	// don't init the environment, if the shortcode is not present on the current page
	$shortcodeMatches = array();
	if ( ! preg_match_all( '/' . get_shortcode_regex() . '/s', $post->post_content, $shortcodeMatches ) ) {
		return;
	}
	if ( ! array_key_exists( 2, $shortcodeMatches ) || ! array_key_exists( 3, $shortcodeMatches ) ) {
		return;
	}
	$shortcodeIndex = array_search( 'OpenEstatePhpWrapper', $shortcodeMatches[2] );
	if ( $shortcodeIndex === false ) {
		return;
	}

	// extract shortcode attributes
	$shortcodeAttribs = shortcode_parse_atts( $shortcodeMatches[3][ $shortcodeIndex ] );
	//die( '<pre>' . print_r( $shortcodeAttribs, true ) . '</pre>' );

	// setup path to the scripts of OpenEstate-PHP-Export
	$scriptPath = trim( get_option( 'openestate_wrapper_script_path' ) );
	if ( strlen( $scriptPath ) > 0 && substr( $scriptPath, - 1 ) != '/' ) {
		$scriptPath .= '/';
	}

	// setup URL to the scripts of OpenEstate-PHP-Export
	$scriptUrl = trim( get_option( 'openestate_wrapper_script_url' ) );
	if ( strlen( $scriptUrl ) > 0 && substr( $scriptUrl, - 1 ) != '/' ) {
		$scriptUrl .= '/';
	}

	// init script environment
	if ( ! isset( $GLOBALS['openestate'] ) || ! is_array( $GLOBALS['openestate'] ) ) {
		$GLOBALS['openestate'] = array();
	}
	$errors      = array();
	$environment = openestate_wrapper_env(
		$scriptPath,
		$scriptUrl,
		true,
		$errors
	);

	// register script environment
	$GLOBALS['openestate']['wrapper']['environment'] = $environment;

	// make sure, that the script environment was properly loaded
	if ( $environment === null || count( $errors ) > 0 ) {
		$content = '<h2>' . esc_html__( 'The exported scripts are NOT correctly configured.', 'openestate-php-wrapper' ) . '</h2>';
		if ( count( $errors ) > 0 ) {
			$content .= '<ul><li>' . implode( '</li><li>', $errors ) . '</li></ul>';
		}

		// register generated content for later inclusion
		$GLOBALS['openestate']['wrapper']['content'] = $content;

		return;
	}

	// make sure, that the script environment is not currently updated
	if ( is_file( Utils::joinPath( $environment->getConfig()->basePath, 'immotool.php.lock' ) ) ) {
		$content = '<h3>' . esc_html__( 'The properties are currently updated.', 'openestate-php-wrapper' ) . '</h3>' .
		           '<p>' . esc_html__( 'Please revisit this page after some minutes.', 'openestate-php-wrapper' ) . '</p>';

		// register generated content for later inclusion
		$GLOBALS['openestate']['wrapper']['content'] = $content;

		return;
	}

	// load attributes from the shortcode
	$settings = array();
	foreach ( $shortcodeAttribs as $key => $value ) {
		$key = trim( $key );
		if ( substr( $key, 0, 7 ) == 'filter_' ) {
			if ( ! isset( $settings['filter'] ) ) {
				$settings['filter'] = array();
			}
			$settings['filter'][ substr( $key, 7 ) ] = $value;
		} else {
			$settings[ $key ] = $value;
		}
	}
	//die( '<pre>' . print_r( $settings, true ) . '</pre>' );

	// determine the script to load
	$wrap = ( isset( $_REQUEST['wrap'] ) ) ? $_REQUEST['wrap'] : null;
	if ( ! is_string( $wrap ) && isset( $settings['wrap'] ) ) {
		$wrap = $settings['wrap'];
	}

	$wrapAction = $wrap == 'action';
	try {
		// set requested language
		if ( ! isset( $_REQUEST['wrap'] ) ) {
			$lang = ( isset( $settings['lang'] ) ) ? strtolower( trim( $settings['lang'] ) ) : null;
			if ( $lang != null && $environment->isSupportedLanguage( $lang ) ) {
				$environment->setLanguage( $lang );
			}
		}

		// process the requested action, if necessary
		$actionResult = $environment->processAction();

		// send the result of the requested action
		if ( $wrapAction ) {
			ob_start();
			if ( $actionResult === null ) {
				\http_response_code( 501 );
				echo Utils::getJson( array( 'error' => 'No action was executed!' ) );
			} else {
				echo Utils::getJson( $actionResult );
			}

			return;
		}

		// wrap expose view
		if ( strtolower( $wrap ) == 'expose' ) {

			$view = $environment->newExposeHtml();

			if ( $view->getObjectId() == null ) {
				$view->setObjectId( isset( $settings['id'] ) ? $settings['id'] : null );
			}

		} // wrap favorite view
		else if ( strtolower( $wrap ) == 'fav' ) {

			$view = $environment->newFavoriteHtml();
			if ( ! isset( $_REQUEST['wrap'] ) && ! isset( $_REQUEST['update'] ) ) {
				$environment->getSession()->setFavoritePage( null );
				$environment->getSession()->setFavoriteView(
					( isset( $settings['view'] ) ) ? $settings['view'] : null );
				$environment->getSession()->setFavoriteOrder(
					( isset( $settings['order_by'] ) ) ? $settings['order_by'] : null );
				$environment->getSession()->setFavoriteOrderDirection(
					( isset( $settings['order_dir'] ) ) ? $settings['order_dir'] : null );
			}

		} // wrap listing view by default
		else {

			$view = $environment->newListingHtml();
			if ( ! isset( $_REQUEST['wrap'] ) && ! isset( $_REQUEST['update'] ) ) {
				$environment->getSession()->setListingPage( null );
				$environment->getSession()->setListingView(
					( isset( $settings['view'] ) ) ? $settings['view'] : null );
				$environment->getSession()->setListingFilters(
					( isset( $settings['filter'] ) ) ? $settings['filter'] : null );
				$environment->getSession()->setListingOrder(
					( isset( $settings['order_by'] ) ) ? $settings['order_by'] : null );
				$environment->getSession()->setListingOrderDirection(
					( isset( $settings['order_dir'] ) ) ? $settings['order_dir'] : null );
			}
		}

		// register generated content for later inclusion
		$GLOBALS['openestate']['wrapper']['view']    = $view;
		$GLOBALS['openestate']['wrapper']['content'] = $view->process();

	} catch ( \Exception $e ) {

		//Utils::logError($e);
		Utils::logWarning( $e );

		// send error as action result
		if ( $wrapAction ) {
			// ignore previously buffered output
			\ob_end_clean();
			\ob_start();

			if ( ! \headers_sent() ) {
				\http_response_code( 500 );
			}
			echo Utils::getJson( array( 'error' => $e->getMessage() ) );
			exit( 0 );
		}

		// register generated content for later inclusion
		$GLOBALS['openestate']['wrapper']['content'] = '<h2>An internal error occurred!</h2>'
		                                               . '<p>' . $e->getMessage() . '</p>'
		                                               . '<pre>' . $e . '</pre>';
	} finally {

		// send action result
		if ( $wrapAction ) {
			$actionResult = ob_get_clean();
			$environment->shutdown();
			echo $actionResult;
			exit( 0 );
		}

		$environment->shutdown();
	}
}


// Add setup form to the administration menu.
// see https://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu
add_action( 'admin_menu', 'openestate_wrapper_admin_menu' );

/**
 * Add setup form to the administration menu.
 *
 * @see https://codex.wordpress.org/Administration_Menus
 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu
 */
function openestate_wrapper_admin_menu() {

	// add an OpenEstate-Wrapper into WordPress administration
	// see https://codex.wordpress.org/Function_Reference/add_options_page
	add_options_page(
		'OpenEstate PHP-Wrapper',
		'OpenEstate-Wrapper',
		'manage_options',
		'openestate_wrapper_admin_options',
		'openestate_wrapper_admin_options'
	);

	// call register settings function
	// see https://codex.wordpress.org/Plugin_API/Action_Reference/admin_init
	add_action( 'admin_init', 'openestate_wrapper_admin_init' );
}

/**
 * Register settings for the wrapper plugin.
 *
 * @see https://codex.wordpress.org/Administration_Menus
 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/admin_init
 * @see https://codex.wordpress.org/Function_Reference/register_setting
 */
function openestate_wrapper_admin_init() {
	// register script path setting
	register_setting(
		'openestate-wrapper-setup',
		'openestate_wrapper_script_path'
	);

	// register script url setting
	register_setting(
		'openestate-wrapper-setup',
		'openestate_wrapper_script_url'
	);

	// register disabled components setting
	register_setting(
		'openestate-wrapper-theme',
		'openestate_wrapper_disabledComponents'
	);

	// register custom css setting
	register_setting(
		'openestate-wrapper-theme',
		'openestate_wrapper_customCss'
	);

	// register charset setting
	register_setting(
		'openestate-wrapper-theme',
		'openestate_wrapper_charset'
	);

	// register favorites setting
	register_setting(
		'openestate-wrapper-theme',
		'openestate_wrapper_favorites'
	);

	// register language selection setting
	register_setting(
		'openestate-wrapper-theme',
		'openestate_wrapper_languages'
	);

	// register filtering setting
	register_setting(
		'openestate-wrapper-theme',
		'openestate_wrapper_filtering'
	);

	// register ordering setting
	register_setting(
		'openestate-wrapper-theme',
		'openestate_wrapper_ordering'
	);
}

/**
 * Show setup form in the administration area.
 *
 * @see https://codex.wordpress.org/Administration_Menus
 * @see https://codex.wordpress.org/Function_Reference/add_options_page
 */
function openestate_wrapper_admin_options() {
	include( __DIR__ . '/admin.php' );
}

/**
 * Load HTML headers for the wrapped environment.
 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/wp_head
 */
add_action( 'wp_head', 'openestate_wrapper_header' );

/**
 * Load HTML headers for the wrapped environment.
 */
function openestate_wrapper_header() {

	if ( ! isset( $GLOBALS['openestate']['wrapper']['view'] ) ) {
		return;
	}

	/**
	 * currently generated view
	 *
	 * @var \OpenEstate\PhpExport\View\AbstractHtmlView $view
	 */
	$view = $GLOBALS['openestate']['wrapper']['view'];

	$headers = $view->getHeaders();
	if ( ! is_array( $headers ) || count( $headers ) < 1 ) {
		return;
	}

	echo "\n\n<!-- OpenEstate-Wrapper v" . VERSION . " (begin) -->";

	foreach ( $view->getHeaders() as $header ) {
		if ( $header instanceof \OpenEstate\PhpExport\Html\Javascript ) {
			echo "\n" . $header->generate();
		} else if ( $header instanceof \OpenEstate\PhpExport\Html\Stylesheet ) {
			echo "\n" . $header->generate();
		} else if ( $header instanceof \OpenEstate\PhpExport\Html\Meta ) {
			if ( $header->name == 'description' || $header->name == 'keywords' ) {
				echo "\n" . $header->generate();
			}
		}
	}

	// add custom css
	$customCss = trim( get_option( 'openestate_wrapper_customCss' ) );
	if ( $customCss !== '' ) {
		echo "\n" . '<style type="text/css">';
		echo "\n" . html( $customCss );
		echo "\n" . '</style>';
	}

	echo "\n<!-- OpenEstate-Wrapper v" . VERSION . " (end) -->\n\n";
}

/**
 * Register the [OpenEstatePhpWrapper] shortcode.
 * see https://codex.wordpress.org/Function_Reference/add_shortcode
 */
add_shortcode( 'OpenEstatePhpWrapper', 'openestate_wrapper_shortcode' );

/**
 * Replace [OpenEstatePhpWrapper] shortcode with wrapped content.
 *
 * @param array $attributes Attributes in the [OpenEstatePhpWrapper] shortcode.
 *
 * @return string Wrapped content.
 *
 * @see https://codex.wordpress.org/Shortcode_API
 */
function openestate_wrapper_shortcode( $attributes ) {

	if ( isset( $GLOBALS['openestate']['wrapper']['content'] ) ) {
		return $GLOBALS['openestate']['wrapper']['content'];
	} else if ( ! is_singular() ) {
		return '<em>' . esc_html__( 'Properties are only loaded on a singular page view.' ) . '</em>';
	} else {
		return '<em>' . esc_html__( 'Can\'t load properties.' ) . '</em>';
	}
}

// update the page title
// see https://developer.wordpress.org/reference/hooks/document_title_parts/
add_filter( 'document_title_parts', 'openestate_wrapper_document_title_parts' );

/**
 * Update the page title.
 *
 * @param array $title
 * The document title parts.
 *
 * @return array
 * Modified title parts.
 *
 * @see https://developer.wordpress.org/reference/hooks/document_title_parts/
 */
function openestate_wrapper_document_title_parts( $title ) {

	if ( ! isset( $GLOBALS['openestate']['wrapper']['view'] ) ) {
		return $title;
	}

	/**
	 * @var \OpenEstate\PhpExport\View\AbstractHtmlView $view
	 */
	$view = $GLOBALS['openestate']['wrapper']['view'];

	// change page title for expose views
	if ( $view instanceof \OpenEstate\PhpExport\View\ExposeHtml ) {
		$title['title'] = $view->getTitle();
	}

	// change page title for favorite views
	if ( $view instanceof \OpenEstate\PhpExport\View\FavoriteHtml ) {
		$title['title'] = $view->getTitle();
	}

	// change page title for listing views
	//if ( $view instanceof \OpenEstate\PhpExport\View\ListingHtml ) {
	//	$title['title'] = $view->getTitle();
	//}

	return $title;
}
