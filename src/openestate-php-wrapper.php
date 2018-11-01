<?php
/*
Plugin Name: OpenEstate PHP-Wrapper
Plugin URI: https://wordpress.org/plugins/openestate-php-wrapper/
Description: This plugin integrates OpenEstate-PHP-Export 1.x into a WordPress blog.
Version: 0.4-SNAPSHOT
Author: Andreas Rudolph, Walter Wagner (OpenEstate.org)
Author URI: https://openestate.org/
*/

/** @noinspection PhpUnusedParameterInspection, HtmlUnknownTarget, HtmlFormInputWithoutLabel, ES6ConvertVarToLetConst */

use \OpenEstate\PhpExport\Environment;
use \OpenEstate\PhpExport\MyConfig;
use \OpenEstate\PhpExport\Utils;
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

	/**
	 * Extended configuration for integration into the website.
	 */
	class WrapperConfig extends MyConfig {
		public function __construct( $basePath, $baseUrl = '.' ) {
			parent::__construct( $basePath, $baseUrl );
		}

		public function getExposeUrl( $parameters = null ) {
			if ( $parameters == null ) {
				$parameters = array();
			}

			$parameters['wrap'] = 'expose';
			foreach ( $_REQUEST as $key => $value ) {
				if ( ! isset( $parameters[ $key ] ) ) {
					$parameters[ $key ] = $value;
				}
			}

			$baseUrl = explode( '?', $_SERVER['REQUEST_URI'] );

			return $baseUrl[0] . Utils::getUrlParameters( $parameters );
		}

		public function getFavoriteUrl( $parameters = null ) {
			if ( $parameters == null ) {
				$parameters = array();
			}

			$parameters['wrap'] = 'fav';
			foreach ( $_REQUEST as $key => $value ) {
				if ( ! isset( $parameters[ $key ] ) ) {
					$parameters[ $key ] = $value;
				}
			}

			$baseUrl = explode( '?', $_SERVER['REQUEST_URI'] );

			return $baseUrl[0] . Utils::getUrlParameters( $parameters );
		}

		public function getListingUrl( $parameters = null ) {
			if ( $parameters == null ) {
				$parameters = array();
			}

			$parameters['wrap'] = 'index';
			foreach ( $_REQUEST as $key => $value ) {
				if ( ! isset( $parameters[ $key ] ) ) {
					$parameters[ $key ] = $value;
				}
			}

			$baseUrl = explode( '?', $_SERVER['REQUEST_URI'] );

			return $baseUrl[0] . Utils::getUrlParameters( $parameters );
		}

		public function setupExposeHtml( \OpenEstate\PhpExport\View\ExposeHtml $view ) {
			parent::setupExposeHtml( $view );
			$view->setBodyOnly( true );
		}

		public function setupFavoriteHtml( \OpenEstate\PhpExport\View\FavoriteHtml $view ) {
			parent::setupFavoriteHtml( $view );
			$view->setBodyOnly( true );
		}

		public function setupListingHtml( \OpenEstate\PhpExport\View\ListingHtml $view ) {
			parent::setupListingHtml( $view );
			$view->setBodyOnly( true );
		}

		public function setupTheme( \OpenEstate\PhpExport\Theme\AbstractTheme $theme ) {
			parent::setupTheme( $theme );

			// register disabled components
			$disabledComponents = explode( ',', trim( get_option( 'openestate_wrapper_disabledComponents' ) ) );
			foreach ( $disabledComponents as $componentId ) {
				$theme->setComponentEnabled( $componentId, false );
			}
		}
	}

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

	// don't init the environment, if the shortcode is not present on the current page
	//if ( ! shortcode_exists( 'OpenEstatePhpWrapper' ) ) {
	//	return;
	//}

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

	// search page content for the short code and extract its attributes
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

	try {
		// set requested language
		$lang = ( isset( $settings['lang'] ) ) ? strtolower( trim( $settings['lang'] ) ) : null;
		if ( $lang != null && $environment->isSupportedLanguage( $lang ) ) {
			$environment->setLanguage( $lang );
		}

		// process the requested action, if necessary
		$environment->processAction();

		// determine the script to load
		$wrap = ( isset( $_REQUEST['wrap'] ) ) ? $_REQUEST['wrap'] : null;
		if ( ! is_string( $wrap ) && isset( $settings['wrap'] ) ) {
			$wrap = $settings['wrap'];
		}

		// wrap expose.php
		if ( strtolower( $wrap ) == 'expose' ) {
			$view = $environment->newExposeHtml();
		} // wrap fav.php
		else if ( strtolower( $wrap ) == 'fav' ) {
			$view = $environment->newFavoriteHtml();
		} // wrap index.php by default
		else {
			$view = $environment->newListingHtml();
		}

		// register generated content for later inclusion
		$GLOBALS['openestate']['wrapper']['view']    = $view;
		$GLOBALS['openestate']['wrapper']['content'] = $view->process();

	} catch ( \Exception $e ) {

		//Utils::logError($e);
		Utils::logWarning( $e );

		// register generated content for later inclusion
		$GLOBALS['openestate']['wrapper']['content'] = '<h2>An internal error occurred!</h2>'
		                                               . '<p>' . $e->getMessage() . '</p>'
		                                               . '<pre>' . $e . '</pre>';
	} finally {

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
 */
function openestate_wrapper_admin_init() {
	// register script path setting
	// see https://codex.wordpress.org/Function_Reference/register_setting
	register_setting(
		'openestate-wrapper-setup',
		'openestate_wrapper_script_path'
	);

	// register script url setting
	// see https://codex.wordpress.org/Function_Reference/register_setting
	register_setting(
		'openestate-wrapper-setup',
		'openestate_wrapper_script_url'
	);

	// register disabled components setting
	// see https://codex.wordpress.org/Function_Reference/register_setting
	register_setting(
		'openestate-wrapper-theme',
		'openestate_wrapper_disabledComponents'
	);

	// register custom css setting
	// see https://codex.wordpress.org/Function_Reference/register_setting
	register_setting(
		'openestate-wrapper-theme',
		'openestate_wrapper_customCss'
	);
}

/**
 * Show setup form in the administration area.
 *
 * @see https://codex.wordpress.org/Administration_Menus
 * @see https://codex.wordpress.org/Function_Reference/add_options_page
 */
function openestate_wrapper_admin_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You\'re not authorized to setup this plugin.', 'openestate-php-wrapper' ) );
	}

	// get information about this plugin
	// see https://codex.wordpress.org/Function_Reference/get_plugin_data
	$pluginData    = get_plugin_data( __FILE__ );
	$pluginVersion = ( is_array( $pluginData ) && isset( $pluginData['Version'] ) ) ?
		$pluginData['Version'] : '???';

	// get configured script path
	$scriptPath = trim( get_option( 'openestate_wrapper_script_path' ) );
	if ( strlen( $scriptPath ) > 0 && substr( $scriptPath, - 1 ) != '/' ) {
		$scriptPath .= '/';
	}

	// get configured script URL
	$scriptUrl = trim( get_option( 'openestate_wrapper_script_url' ) );
	if ( strlen( $scriptUrl ) > 0 && substr( $scriptUrl, - 1 ) != '/' ) {
		$scriptUrl .= '/';
	}

	// get disabled components
	$disabledComponents = explode( ',', trim( get_option( 'openestate_wrapper_disabledComponents' ) ) );

	// init environment of OpenEstate-PHP-Export with configured script path / URL
	$errors      = array();
	$environment = openestate_wrapper_env( $scriptPath, $scriptUrl, false, $errors );

	// set current language, if available
	$locale = get_locale();
	if ( in_array( $locale, $environment->getLanguageCodes() ) ) {
		//echo 'SET LANGUAGE: ' . $locale;
		$environment->setLanguage( $locale );
	} else {
		$l    = explode( '_', $locale );
		$lang = strtolower( $l[0] );
		if ( in_array( $lang, $environment->getLanguageCodes() ) ) {
			//echo 'SET LANGUAGE: ' . $lang;
			$environment->setLanguage( $lang );
		}
	}

	// show the admin page for the plugin
	?>
    <div class="wrap">
        <div style="clear:both; float:right; width:175px; background-color: #F0F0F0; padding:5px 5px 3px 5px; margin-top: 0.5em;">
            <h3 style="padding:0; margin:0;"><?= esc_html__( 'Module', 'openestate-php-wrapper' ) ?></h3>
            <div style="text-align:center;">
                OpenEstate PHP-Wrapper<br/>
				<?= html( $pluginVersion ) ?>
            </div>
            <h3><?= esc_html__( 'License', 'openestate-php-wrapper' ) ?></h3>
            <div style="text-align:center;">
                <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">
                    GNU General Public License v2 (or later)
                </a>
            </div>
            <h3><?= esc_html__( 'Authors', 'openestate-php-wrapper' ) ?></h3>
            <div style="text-align:center;">
                <a href="http://www.openestate.org/" target="_blank">
                    <img src="<?= get_bloginfo( 'url' ) ?>/wp-content/plugins/openestate-php-wrapper/openestate.png"
                         border="0" alt="0"/>
                    <div style="margin-top:0.5em;">Andreas Rudolph, Walter Wagner</div>
                </a>
            </div>
            <h3><?= esc_html__( 'Support us!', 'openestate-php-wrapper' ) ?></h3>
            <div style="text-align:center;">
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="hosted_button_id" value="11005790">
                    <input type="image" src="https://www.paypal.com/de_DE/DE/i/btn/btn_donateCC_LG.gif" name="submit"
                           alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal.">
                    <img alt="" border="0" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1">
                </form>
            </div>
        </div>

        <div style="margin-right:200px;">
            <h2>OpenEstate PHP-Wrapper</h2>

            <hr>
            <h3><?= esc_html__( 'Configure exported scripts', 'openestate-php-wrapper' ) ?></h3>
            <form method="post" action="options.php">
				<?php settings_fields( 'openestate-wrapper-setup' ); ?>
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                        <td style="text-align:right; width:100px; vertical-align:top;">&nbsp;</td>
                        <td>
							<?php
							if ( $environment !== null ) {
								echo '<h3 style="color:green; margin:0;">'
								     . esc_html__( 'The exported scripts are correctly configured.', 'openestate-php-wrapper' ) . '<br/>'
								     . '<span style="font-size:0.7em;">'
								     . esc_html__( 'version', 'openestate-php-wrapper' ) . ' ' . html( VERSION )
								     . '</span>' .
								     '</h3>';
							} else {
								echo '<h3 style="color:red; margin-top:0;">' . esc_html__( 'The exported scripts are NOT correctly configured.', 'openestate-php-wrapper' ) . '</h3>'
								     . '<ul>'
								     . '<li style="color:red;">&raquo; ' . esc_html__( 'Export your properties from ImmoTool to your website via PHP.', 'openestate-php-wrapper' ) . '</li>'
								     . '<li style="color:red;">&raquo; ' . esc_html__( 'Configure path and URL, that points to the exported scripts, and click \'Save\' to perform a new validation.', 'openestate-php-wrapper' ) . '</li>'
								     . '</ul>'
								     . '<h3 style="color:red;">' . esc_html__( 'Error messages', 'openestate-php-wrapper' ) . '</h3>'
								     . '<ul>';
								foreach ( $errors as $error ) {
									echo '<li style="color:red;">&raquo; ' . html( $error ) . '</li>';
								}
								echo '</ul>';
							}
							?>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
                            <label for="openestate_wrapper_script_path">
								<?= esc_html__( 'script path', 'openestate-php-wrapper' ) ?>
                            </label>
                        </td>
                        <td>
                            <input type="text" id="openestate_wrapper_script_path" name="openestate_wrapper_script_path"
                                   value="<?= html( $scriptPath ) ?>" style="width:100%;"/><br/>
                            <i><?= esc_html__( 'Enter the path on your server, that points to the exported scripts. The path of this CMS installation is:', 'openestate-php-wrapper' ) ?></i>
                            <span style="font-weight:bold; white-space:nowrap;"><?= get_home_path() ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
                            <label for="openestate_wrapper_script_url">
								<?= esc_html__( 'script URL', 'openestate-php-wrapper' ) ?>
                            </label>
                        </td>
                        <td>
                            <input type="text" id="openestate_wrapper_script_url" name="openestate_wrapper_script_url"
                                   value="<?= html( $scriptUrl ) ?>" style="width:100%;"/><br/>
                            <i><?= esc_html__( 'Enter the URL on your server, that points to the exported scripts. The URL of this CMS installation is:', 'openestate-php-wrapper' ) ?></i>
                            <span style="font-weight:bold; white-space:nowrap;"><?= get_bloginfo( 'url' ) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:center; padding:1em;">
                            <input type="submit" class="button-primary"
                                   value="<?= esc_html__( 'Save', 'openestate-php-wrapper' ) ?>"/>
                        </td>
                    </tr>
                </table>
            </form>

			<?php if ( $environment !== null ) { ?>
                <script type="text/javascript">
                    <!--
                    function openestate_update_settings(value) {
                        document.getElementById('openestate_wrap_index_settings').style.visibility = (value === 'index') ?
                            'visible' : 'collapse';
                        document.getElementById('openestate_wrap_expose_settings').style.visibility = (value === 'expose') ?
                            'visible' : 'collapse';
                        document.getElementById('openestate_wrap_fav_settings').style.visibility = (value === 'fav') ?
                            'visible' : 'collapse';
                    }

                    function openestate_update_shortcode() {
                        //alert( 'openestate_update_shortcode' );
                        var shortcodeField = document.getElementById('openestate_shortcode');
                        if (shortcodeField == null) return;

                        var indexWrapField = document.getElementById('openestate_wrap_index');
                        var exposeWrapField = document.getElementById('openestate_wrap_expose');
                        var favWrapField = document.getElementById('openestate_wrap_fav');

                        var inputField = null;
                        var params = '';

                        if (indexWrapField.checked === true) {
                            openestate_update_settings('index');

                            params += ' wrap="' + indexWrapField.value + '"';

                            inputField = document.getElementById('openestate_wrap_index_view');
                            if (inputField != null) params += ' view="' + inputField.value + '"';

                            inputField = document.getElementById('openestate_wrap_index_order_by');
                            if (inputField != null) params += ' order_by="' + inputField.value + '"';

                            inputField = document.getElementById('openestate_wrap_index_order_dir');
                            if (inputField != null) params += ' order_dir="' + inputField.value + '"';

                            var filters = [];
							<?php
							$i = 0;
							foreach ( $environment->getConfig()->getFilterObjects() as $filterObj ) {
								/**
								 * filter instance
								 * @var \OpenEstate\PhpExport\Filter\AbstractFilter $filterObj
								 */
								echo '                            ';
								echo 'filters[' . $i . '] = \'' . html( $filterObj->getName() ) . '\';' . "\n";
								$i ++;
							}
							?>
                            for (var i = 0; i < filters.length; i++) {
                                inputField = document.getElementById('openestate_wrap_index_filter_' + filters[i]);
                                if (inputField == null) continue;
                                var val = '';
                                //alert( filters[i] + ': ' + obj2.checked );
                                if (inputField.checked === true || inputField.checked === false) {
                                    if (inputField.checked === true) val = inputField.value;
                                }
                                else {
                                    val = inputField.value;
                                }
                                if (val !== '' && inputField != null) params += ' filter_' + filters[i] + '="' + val + '"';
                            }

                            inputField = document.getElementById('openestate_wrap_index_lang');
                            if (inputField != null) params += ' lang="' + inputField.value + '"';
                        }

                        else if (favWrapField.checked === true) {
                            openestate_update_settings('fav');

                            params += ' wrap="' + favWrapField.value + '"';

                            inputField = document.getElementById('openestate_wrap_fav_view');
                            if (inputField != null) params += ' view="' + inputField.value + '"';

                            inputField = document.getElementById('openestate_wrap_fav_order_by');
                            if (inputField != null) params += ' order_by="' + inputField.value + '"';

                            inputField = document.getElementById('openestate_wrap_fav_order_dir');
                            if (inputField != null) params += ' order_dir="' + inputField.value + '"';

                            inputField = document.getElementById('openestate_wrap_fav_lang');
                            if (inputField != null) params += ' lang="' + inputField.value + '"';
                        }

                        else if (exposeWrapField.checked === true) {
                            openestate_update_settings('expose');

                            params += ' wrap="' + exposeWrapField.value + '"';

                            inputField = document.getElementById('openestate_wrap_expose_id');
                            if (inputField != null && inputField.value !== '') params += ' id="' + inputField.value + '"';

                            inputField = document.getElementById('openestate_wrap_expose_lang');
                            if (inputField != null) params += ' lang="' + inputField.value + '"';
                        }

                        shortcodeField.innerHTML = '[OpenEstatePhpWrapper' + params + ']';
                    }

                    //-->
                </script>
                <hr>
                <h3><?= esc_html__( 'Configure generated view', 'openestate-php-wrapper' ) ?></h3>
                <p><?= esc_html__( 'Use the following form to generate a shortcode, that can be inserted into any article or page of your WordPress blog.', 'openestate-php-wrapper' ) ?></p>
                <textarea id="openestate_shortcode" readonly="readonly" onclick="this.select();" cols="50" rows="2"
                          style="border:1px solid red; background-color:#FFFFE0; padding:0.5em; font-family:monospace; width:100%;">
                    [OpenEstatePhpWrapper]
                </textarea>
                <h4>
                    <input id="openestate_wrap_index" type="radio" name="openestate_wrap" value="index"
                           onchange="openestate_update_shortcode();" checked="checked"/>
                    <label for="openestate_wrap_index">
						<?= esc_html__( 'Property listing', 'openestate-php-wrapper' ) ?> / index.php
                    </label>
                </h4>
                <table id="openestate_wrap_index_settings" cellpadding="0" cellspacing="0" border="0"
                       style="width:100%;">

                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
                            <label for="openestate_wrap_index_view">
								<?= esc_html__( 'view', 'openestate-php-wrapper' ) ?>
                            </label>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <select id="openestate_wrap_index_view" onchange="openestate_update_shortcode();">
                                <option value="detail"><?= esc_html__( 'Tabular mode', 'openestate-php-wrapper' ) ?></option>
                                <option value="thumb"><?= esc_html__( 'Gallery mode', 'openestate-php-wrapper' ) ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
                            <label for="openestate_wrap_index_order_by">
								<?= esc_html__( 'order by', 'openestate-php-wrapper' ) ?>
                            </label>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <select id="openestate_wrap_index_order_by" onchange="openestate_update_shortcode();">
								<?php
								$orders = array();
								$titles = array();
								foreach ( $environment->getConfig()->getOrderObjects() as $orderObj ) {
									/**
									 * order instance
									 * @var \OpenEstate\PhpExport\Order\AbstractOrder $orderObj
									 */
									$name            = $orderObj->getName();
									$titles[ $name ] = strtolower( $orderObj->getTitle( $environment->getLanguage() ) );
									$orders[ $name ] = $orderObj;
								}
								asort( $titles );
								foreach ( array_keys( $titles ) as $name ) {
									$selected = ( $name == 'ObjectId' ) ? 'selected="selected"' : '';
									echo '<option value="' . html( $name ) . '" ' . $selected . '>'
									     . html( $orders[ $name ]->getTitle( $environment->getLanguage() ) )
									     . '</option>';
								}
								?>
                            </select><br/>
                            <select id="openestate_wrap_index_order_dir" onchange="openestate_update_shortcode();">
                                <option value="asc"><?= esc_html__( 'ascending', 'openestate-php-wrapper' ) ?></option>
                                <option value="desc"><?= esc_html__( 'descending', 'openestate-php-wrapper' ) ?></option>
                            </select>
                        </td>
                    </tr>

					<?php
					$filters = array();
					$titles  = array();
					foreach ( $environment->getConfig()->getFilterObjects() as $filterObj ) {
						/**
						 * filter instance
						 * @var \OpenEstate\PhpExport\Filter\AbstractFilter $filterObj
						 */
						$name             = $filterObj->getName();
						$filters[ $name ] = $filterObj;
						$titles[ $name ]  = strtolower( $filterObj->getTitle( $environment->getLanguage() ) );
					}
					asort( $titles );
					foreach ( array_keys( $titles ) as $name ) {
						/**
						 * filter instance
						 * @var \OpenEstate\PhpExport\Filter\AbstractFilter $filterObj
						 */
						$filterObj   = $filters[ $name ];
						$filterValue = ( isset( $settings ) && isset( $settings['immotool_index']['filter'][ $name ] ) ) ?
							$settings['immotool_index']['filter'][ $name ] : '';

						// create filter widget
						$filterWidget           = $filterObj->getWidget( $environment, $filterValue );
						$filterWidget->id       = 'openestate_wrap_index_filter_' . $name;
						$filterWidget->onChange = 'openestate_update_shortcode();';
						?>
                        <tr>
                            <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
                                <label for="<?= html( $filterWidget->id ) ?>">
									<?= sprintf(
										esc_html__( 'filter by %s', 'openestate-php-wrapper' ),
										'<q>' . html( $filterObj->getTitle( $environment->getLanguage() ) ) . '</q>'
									) ?>
                                </label>
                            </td>
                            <td style="padding-bottom:0.8em;">
								<?= $filterWidget->generate() ?>
                            </td>
                        </tr>
						<?php
					}
					?>

                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
                            <label for="openestate_wrap_index_lang">
								<?= esc_html__( 'language', 'openestate-php-wrapper' ) ?>
                            </label>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <select id="openestate_wrap_index_lang" onchange="openestate_update_shortcode();">
								<?php
								foreach ( $environment->getLanguageCodes() as $code ) {
									echo '<option value="' . html( $code ) . '">'
									     . html( $environment->getLanguageName( $code ) )
									     . '</option>';
								}
								?>
                            </select>
                        </td>
                    </tr>
                </table>

                <h4>
                    <input type="radio" id="openestate_wrap_expose" name="openestate_wrap" value="expose"
                           onchange="openestate_update_shortcode();"/>
                    <label for="openestate_wrap_expose">
						<?= esc_html__( 'Property details', 'openestate-php-wrapper' ) ?> / expose.php
                    </label>
                </h4>
                <table cellpadding="0" cellspacing="0" border="0" id="openestate_wrap_expose_settings"
                       style="width:100%;visibility:collapse;">
                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
                            <label for="openestate_wrap_expose_id">
								<?= esc_html__( 'property ID', 'openestate-php-wrapper' ) ?>
                            </label>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <input id="openestate_wrap_expose_id" type="text" maxlength="100" value=""
                                   onchange="openestate_update_shortcode();"/>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
                            <label for="openestate_wrap_expose_lang">
								<?= esc_html__( 'language', 'openestate-php-wrapper' ) ?>
                            </label>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <select id="openestate_wrap_expose_lang" onchange="openestate_update_shortcode();">
								<?php
								foreach ( $environment->getLanguageCodes() as $code ) {
									$selected = ( isset( $settings ) && $settings['immotool_expose']['lang'] == $code ) ?
										'selected="selected"' : '';
									echo '<option value="' . html( $code ) . '" ' . $selected . '>'
									     . html( $environment->getLanguageName( $code ) )
									     . '</option>';
								}
								?>
                            </select>
                        </td>
                    </tr>
                </table>

                <h4>
                    <input type="radio" id="openestate_wrap_fav" name="openestate_wrap" value="fav"
                           onchange="openestate_update_shortcode();"/>
                    <label for="openestate_wrap_fav">
						<?= esc_html__( 'Favourites', 'openestate-php-wrapper' ) ?> / fav.php
                    </label>
                </h4>
                <table cellpadding="0" cellspacing="0" border="0" id="openestate_wrap_fav_settings"
                       style="width:100%;visibility:collapse;">
                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
                            <label for="openestate_wrap_fav_view">
								<?= esc_html__( 'view', 'openestate-php-wrapper' ) ?>
                            </label>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <select id="openestate_wrap_fav_view" onchange="openestate_update_shortcode();">
                                <option value="detail"><?= esc_html__( 'Tabular mode', 'openestate-php-wrapper' ) ?></option>
                                <option value="thumb"><?= esc_html__( 'Gallery mode', 'openestate-php-wrapper' ) ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
                            <label for="openestate_wrap_fav_order_by">
								<?= esc_html__( 'order by', 'openestate-php-wrapper' ) ?>
                            </label>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <select id="openestate_wrap_fav_order_by" onchange="openestate_update_shortcode();">
								<?php
								$orders = array();
								$titles = array();
								foreach ( $environment->getConfig()->getOrderObjects() as $orderObj ) {
									$name            = $orderObj->getName();
									$titles[ $name ] = strtolower( $orderObj->getTitle( $environment->getLanguage() ) );
									$orders[ $name ] = $orderObj;
								}
								asort( $titles );
								foreach ( array_keys( $titles ) as $name ) {
									$selected = ( $name == 'ObjectId' ) ? 'selected="selected"' : '';
									echo '<option value="' . html( $name ) . '" ' . $selected . '>'
									     . html( $orders[ $name ]->getTitle( $environment->getLanguage() ) )
									     . '</option>';
								}
								?>
                            </select><br/>
                            <select id="openestate_wrap_fav_order_dir" onchange="openestate_update_shortcode();">
                                <option value="asc"><?= esc_html__( 'ascending', 'openestate-php-wrapper' ) ?></option>
                                <option value="desc"><?= esc_html__( 'descending', 'openestate-php-wrapper' ) ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
                            <label for="openestate_wrap_fav_lang">
								<?= esc_html__( 'language', 'openestate-php-wrapper' ) ?>
                            </label>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <select id="openestate_wrap_fav_lang" onchange="openestate_update_shortcode();">
								<?php
								foreach ( $environment->getLanguageCodes() as $code ) {
									echo '<option value="' . html( $code ) . '">'
									     . html( $environment->getLanguageName( $code ) )
									     . '</option>';
								}
								?>
                            </select>
                        </td>
                    </tr>
                </table>


                <script type="text/javascript">
                    <!--
                    openestate_update_shortcode();
                    //-->
                </script>
			<?php } ?>

			<?php if ( $environment !== null ) { ?>
                <script type="text/javascript">
                    <!--
                    function openestate_update_components() {
                        var components = [];
						<?php
						$i = 0;
						foreach ( $environment->getTheme()->getComponentIds() as $componentId ) {
							echo '						';
							echo 'components[' . $i . '] = \'' . html( $componentId ) . '\';' . "\n";
							$i ++;
						}
						?>
                        var params = '';
                        for (var i = 0; i < components.length; i++) {
                            var inputField = document.getElementById('openestate_wrapper_component_' + components[i]);
                            if (inputField == null) continue;
                            if (inputField.checked === false) {
                                if (params !== '') params += ',';
                                params += components[i];
                            }
                            document.getElementById('openestate_wrapper_disabledComponents').value = params;
                        }
                    }

                    //-->
                </script>

                <hr>
                <h3><?= esc_html__( 'Further options', 'openestate-php-wrapper' ) ?></h3>
                <form method="post" action="options.php">
					<?php settings_fields( 'openestate-wrapper-theme' ); ?>
                    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                        <h4><?= esc_html__( 'Custom stylesheet', 'openestate-php-wrapper' ) ?></h4>
                        <p>
							<?= esc_html__( 'You can provide custom stylesheets, that are loaded together with the PHP export.', 'openestate-php-wrapper' ) ?>
                        </p>
                        <textarea id="openestate_wrapper_customCss" name="openestate_wrapper_customCss"
                                  style="width:100%; height:8em;"><?= html( get_option( 'openestate_wrapper_customCss' ) ) ?></textarea>
                        <h4><?= esc_html__( 'Embedded components', 'openestate-php-wrapper' ) ?></h4>
                        <p>
							<?= esc_html__( 'The PHP export integrates the following third party components into your WordPress blog.', 'openestate-php-wrapper' ) ?>
							<?= esc_html__( 'If your website already uses some of these components, you can disable them accordingly through the following options.', 'openestate-php-wrapper' ) ?>
                        </p>
                        <input id="openestate_wrapper_disabledComponents" name="openestate_wrapper_disabledComponents"
                               type="text" readonly="readonly" style="display:none;"
                               value="<?= html( implode( ',', $disabledComponents ) ) ?>">
						<?php foreach ( $environment->getTheme()->getComponentIds() as $componentId ) { ?>
                            <tr>
                                <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
                                    &nbsp;
                                </td>
                                <td>
                                    <input type="checkbox" id="openestate_wrapper_component_<?= html( $componentId ) ?>"
                                           value="<?= html( $componentId ) ?>"
										<?= ( ! in_array( $componentId, $disabledComponents ) ) ? 'checked="checked"' : '' ?>
                                           onchange="openestate_update_components();"/>
                                    <label for="openestate_wrapper_component_<?= html( $componentId ) ?>">
                                        <i><?= html( $componentId ) ?></i>
                                    </label>
                                </td>
                            </tr>
						<?php } ?>
                        <tr>
                            <td colspan="2" style="text-align:center; padding:1em;">
                                <input type="submit" class="button-primary"
                                       value="<?= esc_html__( 'Save', 'openestate-php-wrapper' ) ?>"/>
                            </td>
                        </tr>
                    </table>
                </form>


			<?php } ?>

        </div>
    </div>
	<?php
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
