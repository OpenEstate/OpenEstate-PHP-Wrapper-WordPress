<?php
/*
Plugin Name: OpenEstate PHP-Wrapper
Plugin URI: https://wordpress.org/plugins/openestate-php-wrapper/
Description: This plugin integrates OpenEstate-PHP-Export 1.x into a WordPress blog.
Version: 0.4-SNAPSHOT
Author: Andreas Rudolph, Walter Wagner (OpenEstate.org)
Author URI: https://openestate.org/
License: GPL3
*/

/** @noinspection PhpUnusedParameterInspection, HtmlUnknownTarget, HtmlFormInputWithoutLabel, ES6ConvertVarToLetConst */

/**
 * Init script environment.
 *
 * @param string $scriptPath Path, that contains to the script environment.
 * @param string $scriptUrl URL, that points to the script environment.
 * @param array $environmentErrors Errors during initialization.
 *
 * @return boolean True, if the wrapper was loaded successfully.
 */
function openestate_wrapper_load( $scriptPath, $scriptUrl, &$environmentErrors ) {
	if ( ! defined( 'OPENESTATE_WRAPPER' ) ) {
		define( 'OPENESTATE_WRAPPER', '1' );
	}

	// define name of URL parameters for the wrapped scripts
	if ( ! defined( 'IMMOTOOL_PARAM_LANG' ) ) {
		define( 'IMMOTOOL_PARAM_LANG', 'wrapped_lang' );
	}
	if ( ! defined( 'IMMOTOOL_PARAM_FAV' ) ) {
		define( 'IMMOTOOL_PARAM_FAV', 'wrapped_fav' );
	}
	if ( ! defined( 'IMMOTOOL_PARAM_INDEX_PAGE' ) ) {
		define( 'IMMOTOOL_PARAM_INDEX_PAGE', 'wrapped_page' );
	}
	if ( ! defined( 'IMMOTOOL_PARAM_INDEX_RESET' ) ) {
		define( 'IMMOTOOL_PARAM_INDEX_RESET', 'wrapped_reset' );
	}
	if ( ! defined( 'IMMOTOOL_PARAM_INDEX_ORDER' ) ) {
		define( 'IMMOTOOL_PARAM_INDEX_ORDER', 'wrapped_order' );
	}
	if ( ! defined( 'IMMOTOOL_PARAM_INDEX_FILTER' ) ) {
		define( 'IMMOTOOL_PARAM_INDEX_FILTER', 'wrapped_filter' );
	}
	if ( ! defined( 'IMMOTOOL_PARAM_INDEX_FILTER_CLEAR' ) ) {
		define( 'IMMOTOOL_PARAM_INDEX_FILTER_CLEAR', 'wrapped_clearFilters' );
	}
	if ( ! defined( 'IMMOTOOL_PARAM_INDEX_VIEW' ) ) {
		define( 'IMMOTOOL_PARAM_INDEX_VIEW', 'wrapped_view' );
	}
	if ( ! defined( 'IMMOTOOL_PARAM_INDEX_MODE' ) ) {
		define( 'IMMOTOOL_PARAM_INDEX_MODE', 'wrapped_mode' );
	}
	if ( ! defined( 'IMMOTOOL_PARAM_EXPOSE_ID' ) ) {
		define( 'IMMOTOOL_PARAM_EXPOSE_ID', 'wrapped_id' );
	}
	if ( ! defined( 'IMMOTOOL_PARAM_EXPOSE_VIEW' ) ) {
		define( 'IMMOTOOL_PARAM_EXPOSE_VIEW', 'wrapped_view' );
	}
	if ( ! defined( 'IMMOTOOL_PARAM_EXPOSE_IMG' ) ) {
		define( 'IMMOTOOL_PARAM_EXPOSE_IMG', 'wrapped_img' );
	}
	if ( ! defined( 'IMMOTOOL_PARAM_EXPOSE_CONTACT' ) ) {
		define( 'IMMOTOOL_PARAM_EXPOSE_CONTACT', 'wrapped_contact' );
	}
	if ( ! defined( 'IMMOTOOL_PARAM_EXPOSE_CAPTCHA' ) ) {
		define( 'IMMOTOOL_PARAM_EXPOSE_CAPTCHA', 'wrapped_captchacode' );
	}

	// load minimal script environment
	$environmentFiles = array( 'config.php', 'private.php', 'include/functions.php', 'data/language.php' );
	if ( ! is_dir( $scriptPath ) ) {
		$environmentErrors[] = __( 'Please enter a valid script path.', 'openestate-php-wrapper' );

		return false;
	}
	if ( ! defined( 'IMMOTOOL_BASE_PATH' ) ) {
		define( 'IMMOTOOL_BASE_PATH', $scriptPath );
	}
	foreach ( $environmentFiles as $file ) {
		if ( ! is_file( IMMOTOOL_BASE_PATH . $file ) ) {
			$environmentErrors[] = __( 'The file was not found in the script path.', 'openestate-php-wrapper' ) . ': <i>' . $file . '</i>';
		}
	}
	if ( count( $environmentErrors ) == 0 ) {
		if ( ! defined( 'IN_WEBSITE' ) ) {
			define( 'IN_WEBSITE', 1 );
		}
		foreach ( $environmentFiles as $file ) {
			//echo IMMOTOOL_BASE_PATH . $file . '<hr/>';
			/** @noinspection PhpIncludeInspection */
			require_once( IMMOTOOL_BASE_PATH . $file );
		}
		if ( ! defined( 'IMMOTOOL_SCRIPT_VERSION' ) ) {
			$environmentErrors[] = __( 'Can\'t detect the script version.', 'openestate-php-wrapper' );
		}
	}

	return count( $environmentErrors ) == 0;
}

/**
 * Init script environment from the provided settings.
 */
function openestate_wrapper_load_from_settings() {
	//wp_die('<pre>' . print_r( $GLOBALS, true ) . '</pre>');
	//wp_die( $GLOBALS['pagenow'] );

	if ( ! defined( 'OPENESTATE_WRAPPER_LOADED' ) ) {

		// setup path to the scripts of OpenEstate-PHP-Export
		if ( ! defined( 'IMMOTOOL_BASE_PATH' ) ) {
			$scriptPath = trim( get_option( 'openestate_wrapper_script_path' ) );
			if ( strlen( $scriptPath ) > 0 && substr( $scriptPath, - 1 ) != '/' ) {
				$scriptPath .= '/';
			}
			define( 'IMMOTOOL_BASE_PATH', $scriptPath );
		}

		// setup URL to the scripts of OpenEstate-PHP-Export
		if ( ! defined( 'IMMOTOOL_BASE_URL' ) ) {
			$scriptUrl = trim( get_option( 'openestate_wrapper_script_url' ) );
			if ( strlen( $scriptUrl ) > 0 && substr( $scriptUrl, - 1 ) != '/' ) {
				$scriptUrl .= '/';
			}
			define( 'IMMOTOOL_BASE_URL', $scriptUrl );
		}

		// init OpenEstate-PHP-Export with the configured script path / URL
		$environmentErrors  = array();
		$environmentIsValid = openestate_wrapper_load(
			IMMOTOOL_BASE_PATH,
			IMMOTOOL_BASE_URL,
			$environmentErrors
		);
		if ( ! $environmentIsValid ) {
			define( 'OPENESTATE_WRAPPER_LOADED', '0' );
			if ( is_array( $environmentErrors ) && count( $environmentErrors ) > 0 ) {
				$GLOBALS['openestate_environment_errors'] = $environmentErrors;
			}
		} else {
			define( 'OPENESTATE_WRAPPER_LOADED', '1' );

			// init session for OpenEstate-PHP-Export
			if ( ! headers_sent() && is_callable( array( 'immotool_functions', 'init_session' ) ) ) {
				immotool_functions::init_session();
			}
		}
	}
}

// Init script environment on public pages.
// see http://codex.wordpress.org/Plugin_API/Action_Reference/init
add_action( 'init', 'openestate_wrapper_init' );

/**
 * Init script environment on public pages.
 */
function openestate_wrapper_init() {

	// init translations
	// see https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
	load_plugin_textdomain(
		'openestate-php-wrapper',
		false,
		'openestate-php-wrapper/languages'
	);

	if ( ! is_admin() && ! in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) {
		openestate_wrapper_load_from_settings();
	}
}

// Add setup form to the administration menu.
// see http://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu
add_action( 'admin_menu', 'openestate_wrapper_menu' );

/**
 * Add setup form to the administration menu.
 *
 * @see http://codex.wordpress.org/Administration_Menus
 */
function openestate_wrapper_menu() {

	// add an OpenEstate-Wrapper into WordPress administration
	// see http://codex.wordpress.org/Function_Reference/add_options_page
	add_options_page(
		'OpenEstate PHP-Wrapper',
		'OpenEstate-Wrapper',
		'manage_options',
		'openestate_wrapper_setup',
		'openestate_wrapper_setup'
	);

	// call register settings function
	// see http://codex.wordpress.org/Plugin_API/Action_Reference/admin_init
	add_action( 'admin_init', 'openestate_wrapper_settings' );
}

/**
 * Register settings for the wrapper plugin.
 *
 * @see http://codex.wordpress.org/Function_Reference/register_setting
 */
function openestate_wrapper_settings() {
	register_setting(
		'openestate-wrapper-setup',
		'openestate_wrapper_script_path'
	);
	register_setting(
		'openestate-wrapper-setup',
		'openestate_wrapper_script_url'
	);
}

/**
 * Show setup form in the administration area.
 */
function openestate_wrapper_setup() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You\'re not authorized to setup this plugin.', 'openestate-php-wrapper' ) );
	}

	// get information about this plugin
	// see http://codex.wordpress.org/Function_Reference/get_plugin_data
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

	// init environment of OpenEstate-PHP-Export with configured script path / URL
	$environmentErrors  = array();
	$environmentIsValid = openestate_wrapper_load( $scriptPath, $scriptUrl, $environmentErrors );

	// show additional admin actions,
	// if the scripts of OpenEstate-PHP-Export were correctly loaded
	$setupIndex = null;
	//$setupExpose = null;
	$setupTranslations = null;
	$setupLang         = null;
	if ( $environmentIsValid ) {

		// init configuration of OpenEstate-PHP-Export
		$setupIndex = new immotool_setup_index();
		//$setupExpose = new immotool_setup_expose();
		if ( is_callable( array( 'immotool_functions', 'init_config' ) ) ) {
			immotool_functions::init_config( $setupIndex, 'load_config_index' );
			//immotool_functions::init_config($setupExpose, 'load_config_expose');
		}

		// init translations of OpenEstate-PHP-Export
		$setupLang = immotool_functions::init_language(
			$setupIndex->DefaultLanguage,
			$setupIndex->DefaultLanguage,
			$setupTranslations
		);
		if ( ! is_array( $setupTranslations ) ) {
			$environmentErrors[] = __( 'Can\'t find translation.', 'openestate-php-wrapper' );
			$environmentIsValid  = false;
		}
	}

	// show the admin page for the plugin
	?>
    <div class="wrap">
        <div style="clear:both; float:right; width:175px; background-color: #F0F0F0; padding:5px 5px 3px 5px; margin-top: 0.5em;">
            <h3 style="padding:0; margin:0;"><?php echo __( 'Module', 'openestate-php-wrapper' ); ?></h3>
            <div style="text-align:center;">
                OpenEstate PHP-Wrapper<br/>
				<?php echo __( 'version', 'openestate-php-wrapper' ) . ' ' . $pluginVersion; ?>
            </div>
            <h3><?php echo __( 'License', 'openestate-php-wrapper' ); ?></h3>
            <div style="text-align:center;">
                <a href="<?php echo get_bloginfo( 'url' ); ?>/wp-content/plugins/openestate-php-wrapper/gpl-3.0-standalone.html"
                   target="_blank">GNU General Public License v3</a>
            </div>
            <h3><?php echo __( 'Authors', 'openestate-php-wrapper' ); ?></h3>
            <div style="text-align:center;">
                <a href="http://www.openestate.org/" target="_blank">
                    <img src="<?php echo get_bloginfo( 'url' ); ?>/wp-content/plugins/openestate-php-wrapper/openestate.png"
                         border="0" alt="0"/>
                    <div style="margin-top:0.5em;">Andreas Rudolph, Walter Wagner</div>
                </a>
            </div>
            <h3><?php echo __( 'Support us!', 'openestate-php-wrapper' ); ?></h3>
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

            <h3 style="margin-top:1.5em;"><?php echo __( 'Configure exported scripts', 'openestate-php-wrapper' ); ?></h3>
            <form method="post" action="options.php">
				<?php settings_fields( 'openestate-wrapper-setup' ); ?>
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                        <td style="text-align:right; width:100px; vertical-align:top;">&nbsp;</td>
                        <td>
							<?php
							if ( $environmentIsValid ) {
								echo '<h3 style="color:green; margin:0;">' .
								     __( 'The exported scripts are correctly configured.', 'openestate-php-wrapper' ) . '<br/>' .
								     '<span style="font-size:0.7em;">'
								     . __( 'version', 'openestate-php-wrapper' ) . ' ' . IMMOTOOL_SCRIPT_VERSION
								     . '</span>' .
								     '</h3>';
							} else {
								echo '<h3 style="color:red; margin-top:0;">' . __( 'The exported scripts are NOT correctly configured.', 'openestate-php-wrapper' ) . '</h3>';
								echo '<ul>';
								echo '<li style="color:red;">&raquo; ' . __( 'Export your properties from ImmoTool to your website via PHP.', 'openestate-php-wrapper' ) . '</li>';
								echo '<li style="color:red;">&raquo; ' . __( 'Configure path and URL, that points to the exported scripts, and click \'Save\' to perform a new validation.', 'openestate-php-wrapper' ) . '</li>';
								echo '</ul>';
								echo '<h3 style="color:red;">' . __( 'Error messages', 'openestate-php-wrapper' ) . '</h3>';
								echo '<ul>';
								foreach ( $environmentErrors as $error ) {
									echo '<li style="color:red;">&raquo; ' . $error . '</li>';
								}
								echo '</ul>';
							}
							?>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
							<?php echo __( 'Script path', 'openestate-php-wrapper' ); ?>
                        </td>
                        <td>
                            <input type="text" name="openestate_wrapper_script_path" value="<?php echo $scriptPath; ?>"
                                   style="width:100%;"/><br/><i><?php echo __( 'Enter the path on your server, that points to the exported scripts. The path of this CMS installation is:', 'openestate-php-wrapper' ); ?></i>
                            <span style="font-weight:bold; white-space:nowrap;"><?php echo get_home_path(); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
							<?php echo __( 'Script URL', 'openestate-php-wrapper' ); ?>
                        </td>
                        <td>
                            <input type="text" name="openestate_wrapper_script_url" value="<?php echo $scriptUrl; ?>"
                                   style="width:100%;"/><br/>
                            <i><?php echo __( 'Enter the URL on your server, that points to the exported scripts. The URL of this CMS installation is:', 'openestate-php-wrapper' ); ?></i>
                            <span style="font-weight:bold; white-space:nowrap;"><?php echo get_bloginfo( 'url' ); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:center; padding:1em;">
                            <input type="submit" class="button-primary"
                                   value="<?php echo __( 'Save', 'openestate-php-wrapper' ); ?>"/>
                        </td>
                    </tr>
                </table>
            </form>

			<?php if ( $environmentIsValid ) { ?>
                <script language="JavaScript" type="text/javascript">
                    <!--
                    function show_wrapper_settings($value) {
                        document.getElementById('immotool_wrap_script_index_settings').style.visibility = ($value === 'index') ? 'visible' : 'collapse';
                        document.getElementById('immotool_wrap_script_expose_settings').style.visibility = ($value === 'expose') ? 'visible' : 'collapse';
                    }

                    function build_tag() {
                        //alert( 'build_tag' );
                        var obj = document.getElementById('openestate_wrapper');
                        if (obj == null) return;
                        var wrap_index = document.getElementById('immotool_wrap_script_index');
                        var wrap_expose = document.getElementById('immotool_wrap_script_expose');

                        var obj2 = null;
                        var params = '';

                        if (wrap_index != null && wrap_index.checked === true) {
                            params += ' wrap="' + wrap_index.value + '"';

                            obj2 = document.getElementById('index_view');
                            if (obj2 != null) params += ' view="' + obj2.value + '"';

                            obj2 = document.getElementById('index_mode');
                            if (obj2 != null) params += ' mode="' + obj2.value + '"';

                            obj2 = document.getElementById('index_lang');
                            if (obj2 != null) params += ' lang="' + obj2.value + '"';

                            obj2 = document.getElementById('index_order_by');
                            if (obj2 != null) params += ' order_by="' + obj2.value + '"';

                            obj2 = document.getElementById('index_order_dir');
                            if (obj2 != null) params += ' order_dir="' + obj2.value + '"';

                            var filters = [];
							<?php
							$i = 0;
							$filters = immotool_functions::list_available_filters();
							if ( is_array( $filters ) ) {
								foreach ( $filters as $key ) {
									echo '                            ';
									echo 'filters[' . $i . '] = \'' . $key . '\';' . "\n";
									$i ++;
								}
							}?>
                            for (var i = 0; i < filters.length; i++) {
                                obj2 = document.getElementById('filter_' + filters[i]);
                                if (obj2 == null) continue;
                                var val = '';
                                //alert( filters[i] + ': ' + obj2.checked );
                                if (obj2.checked === true || obj2.checked === false) {
                                    if (obj2.checked === true) val = obj2.value;
                                }
                                else {
                                    val = obj2.value;
                                }
                                if (val !== '' && obj2 != null) params += ' filter_' + filters[i] + '="' + val + '"';
                            }
                        }

                        else if (wrap_expose != null && wrap_expose.checked === true) {
                            params += ' wrap="' + wrap_expose.value + '"';

                            obj2 = document.getElementById('expose_view');
                            if (obj2 != null) params += ' view="' + obj2.value + '"';

                            obj2 = document.getElementById('expose_lang');
                            if (obj2 != null) params += ' lang="' + obj2.value + '"';

                            obj2 = document.getElementById('expose_id');
                            if (obj2 != null && obj2.value !== '') params += ' id="' + obj2.value + '"';
                        }

                        obj.innerHTML = '[OpenEstatePhpWrapper' + params + ']';
                    }

                    //-->
                </script>
                <h3 style="margin-top:1.5em;"><?php echo __( 'Configure generated view', 'openestate-php-wrapper' ); ?></h3>
                <p><?php echo __( 'Use the following form to generate a shortcode, that can be inserted into any article or page of your WordPress blog.', 'openestate-php-wrapper' ); ?></p>
                <textarea id="openestate_wrapper" readonly="readonly" onclick="this.select();" cols="50" rows="2"
                          style="border:1px solid red; background-color:#FFFFE0; padding:0.5em; font-family:monospace; width:100%;">
                    [OpenEstatePhpWrapper]
                </textarea>
                <h4>
                    <input id="immotool_wrap_script_index" type="radio" name="immotool_wrap_script" value="index"
                           onchange="show_wrapper_settings('index');build_tag();" checked="checked"/>
                    <label for="immotool_wrap_script_index"><?php echo __( 'Property listing / index.php', 'openestate-php-wrapper' ); ?></label>
                </h4>
                <table id="immotool_wrap_script_index_settings" cellpadding="0" cellspacing="0" border="0"
                       style="width:100%;">

                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
							<?php echo __( 'View', 'openestate-php-wrapper' ); ?>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <select id="index_view" style="border:1px solid #c0c0c0;" onchange="build_tag();">
                                <option value="index"><?php echo __( 'Summary', 'openestate-php-wrapper' ); ?></option>
                                <option value="fav"><?php echo __( 'Favourites', 'openestate-php-wrapper' ); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
							<?php echo __( 'Mode', 'openestate-php-wrapper' ); ?>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <select id="index_mode" style="border:1px solid #c0c0c0;" onchange="build_tag();">
                                <option value="entry"><?php echo __( 'Tabular mode', 'openestate-php-wrapper' ); ?></option>
                                <option value="gallery"><?php echo __( 'Gallery mode', 'openestate-php-wrapper' ); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
							<?php echo __( 'Language', 'openestate-php-wrapper' ); ?>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <select id="index_lang" style="border:1px solid #c0c0c0;" onchange="build_tag();">
								<?php
								$languageCodes = immotool_functions::get_language_codes();
								if ( is_array( $languageCodes ) ) {
									foreach ( $languageCodes as $code ) {
										echo '<option value="' . $code . '">' . immotool_functions::get_language_name( $code ) . '</option>';
									}
								}
								?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
							<?php echo __( 'Order', 'openestate-php-wrapper' ); ?>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <select id="index_order_by" style="border:1px solid #c0c0c0;" onchange="build_tag();">
								<?php
								$sortedOrders    = array();
								$availableOrders = array();
								$orderNames      = array();

								// get all available order classes
								if ( is_callable( array( 'immotool_functions', 'list_available_orders' ) ) ) {
									$orderNames = immotool_functions::list_available_orders();
								}

								// get explicitly enabled order classes
								// this mechanism is a fallback for older versions of the OpenEstate-PHP-Export,
								// that don't support immotool_functions::list_available_orders()
								else if ( is_array( $setupIndex->OrderOptions ) ) {
									$orderNames = $setupIndex->OrderOptions;
								}

								foreach ( $orderNames as $key ) {
									$orderObj                = immotool_functions::get_order( $key );
									$by                      = $orderObj->getTitle( $setupTranslations, $setupLang );
									$sortedOrders[ $key ]    = $by;
									$availableOrders[ $key ] = $orderObj;
								}
								asort( $sortedOrders );

								foreach ( $sortedOrders as $key => $by ) {
									//$orderObj = $availableOrders[ $key ];
									echo '<option value="' . $key . '">' . $by . '</option>';
								}
								?>
                            </select><br/>
                            <select id="index_order_dir" style="border:1px solid #c0c0c0;" onchange="build_tag();">
                                <option value="asc"><?php echo __( 'ascending', 'openestate-php-wrapper' ); ?></option>
                                <option value="desc"><?php echo __( 'descending', 'openestate-php-wrapper' ); ?></option>
                            </select>
                        </td>
                    </tr>

					<?php
					$filters = immotool_functions::list_available_filters();
					if ( is_array( $filters ) ) {
						foreach ( $filters as $key ) {
							$filterObj = immotool_functions::get_filter( $key );
							if ( ! is_object( $filterObj ) ) {
								//echo "Can't find filter object $key<hr/>";
								continue;
							}
							$filterValue  = ( isset( $settings ) && isset( $settings['immotool_index']['filter'][ $key ] ) ) ? $settings['immotool_index']['filter'][ $key ] : '';
							$filterWidget = $filterObj->getWidget( $filterValue, $setupLang, $setupTranslations, $setupIndex );
							if ( ! is_string( $filterWidget ) || strlen( $filterWidget ) == 0 ) {
								//echo "Can't create widget for filter object $key<hr/>";
								continue;
							}
							$filterWidget = str_replace( '<select ', '<select style="border:1px solid #c0c0c0;" ', $filterWidget );
							$filterWidget = str_replace( '<select ', '<select onchange="build_tag();" ', $filterWidget );
							$filterWidget = str_replace( '<input ', '<input onchange="build_tag();" ', $filterWidget );
							?>
                            <tr>
                                <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
									<?php echo __( 'Filter', 'openestate-php-wrapper' ); ?><br/>
                                    <span style="font-style:italic;font-size:0.9em;">
                                        <?php echo $filterObj->getTitle( $setupTranslations, $setupLang ); ?>
                                    </span>
                                </td>
                                <td style="padding-bottom:0.8em;">
									<?php echo $filterWidget; ?>
                                </td>
                            </tr>
							<?php
						}
					}
					?>
                </table>

                <h4>
                    <input type="radio" id="immotool_wrap_script_expose" name="immotool_wrap_script" value="expose"
                           onchange="show_wrapper_settings('expose');build_tag();"/>
                    <label for="immotool_wrap_script_expose">
						<?php echo __( 'Property details / expose.php', 'openestate-php-wrapper' ); ?>
                    </label>
                </h4>
                <table cellpadding="0" cellspacing="0" border="0" id="immotool_wrap_script_expose_settings"
                       style="width:100%;visibility:collapse;">
                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
							<?php echo __( 'Property ID', 'openestate-php-wrapper' ); ?>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <input id="expose_id" type="text" style="border:1px solid #c0c0c0;" maxlength="15" value=""
                                   onchange="build_tag();"/>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
							<?php echo __( 'View', 'openestate-php-wrapper' ); ?>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <select id="expose_view" style="border:1px solid #c0c0c0;" onchange="build_tag();">
                                <option value="details"><?php echo __( 'Details', 'openestate-php-wrapper' ); ?></option>
                                <option value="texts"><?php echo __( 'Description', 'openestate-php-wrapper' ); ?></option>
                                <option value="gallery"><?php echo __( 'Gallery', 'openestate-php-wrapper' ); ?></option>
                                <option value="contact"><?php echo __( 'Contact', 'openestate-php-wrapper' ); ?></option>
                                <option value="terms"><?php echo __( 'Terms', 'openestate-php-wrapper' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;">
							<?php echo __( 'Language', 'openestate-php-wrapper' ); ?>
                        </td>
                        <td style="padding-bottom:0.8em;">
                            <select id="expose_lang" style="border:1px solid #c0c0c0;" onchange="build_tag();">
								<?php
								$languageCodes = immotool_functions::get_language_codes();
								if ( is_array( $languageCodes ) ) {
									foreach ( $languageCodes as $code ) {
										$selected = ( isset( $settings ) && $settings['immotool_expose']['lang'] == $code ) ?
											'selected="selected"' : '';
										echo '<option value="' . $code . '" ' . $selected . '>'
										     . immotool_functions::get_language_name( $code )
										     . '</option>';
									}
								}
								?>
                            </select>
                        </td>
                    </tr>
                </table>
                <script language="JavaScript" type="text/javascript">
                    <!--
                    build_tag();
                    //-->
                </script>
			<?php } ?>
        </div>
    </div>
	<?php
}

/**
 * Load HTML headers for the wrapped environment.
 * @see http://codex.wordpress.org/Plugin_API/Action_Reference/wp_head
 */
add_action( 'wp_head', 'openestate_wrapper_header' );

/**
 * Load HTML headers for the wrapped environment.
 */
function openestate_wrapper_header() {
	if ( defined( 'OPENESTATE_WRAPPER_LOADED' ) && OPENESTATE_WRAPPER_LOADED == '1' ) {
		echo "\n\n<!-- OpenEstate-Wrapper v" . IMMOTOOL_SCRIPT_VERSION . " (begin) -->";

		// load general stylesheet (style.php)
		echo "\n" . '<link rel="stylesheet" type="text/css" media="all" href="' . IMMOTOOL_BASE_URL . 'style.php?wrapped=1" />';

		// load additional stylesheet, if configured
		if ( class_exists( 'immotool_setup' ) ) {
			$setup = new immotool_setup();
			if ( is_callable( array( 'immotool_myconfig', 'load_config_default' ) ) ) {
				immotool_myconfig::load_config_default( $setup );
			}
			if ( is_string( $setup->AdditionalStylesheet ) && strlen( $setup->AdditionalStylesheet ) > 0 ) {
				echo "\n" . '<link rel="stylesheet" type="text/css" media="all" href="' . $setup->AdditionalStylesheet . '" />';
			}
		}
		echo "\n<!-- OpenEstate-Wrapper v" . IMMOTOOL_SCRIPT_VERSION . " (end) -->\n\n";
	}
}

/**
 * Register the [OpenEstatePhpWrapper] shortcode.
 * see http://codex.wordpress.org/Function_Reference/add_shortcode
 */
add_shortcode( 'OpenEstatePhpWrapper', 'openestate_wrapper_shortcode' );

/**
 * Replace [OpenEstatePhpWrapper] shortcode with wrapped content.
 *
 * @param array $attributes Attributes in the [OpenEstatePhpWrapper] shortcode.
 *
 * @return string Wrapped content.
 *
 * @see http://codex.wordpress.org/Shortcode_API
 */
function openestate_wrapper_shortcode( $attributes ) {

	// init OpenEstate-PHP-Export, if that was not already done
	openestate_wrapper_load_from_settings();
	if ( ! defined( 'OPENESTATE_WRAPPER_LOADED' ) || OPENESTATE_WRAPPER_LOADED == '0' ) {
		//wp_die('<h1>' . __('The exported scripts are NOT correctly configured.', 'openestate-php-wrapper') . '</h1><ul><li>' . implode('</li><li>', $environmentErrors) . '</li></ul>');
		$output = '<h2>' . __( 'The exported scripts are NOT correctly configured.', 'openestate-php-wrapper' ) . '</h2>';
		if ( isset( $GLOBALS['openestate_environment_errors'] ) ) {
			$output .= '<ul><li>'
			           . implode( '</li><li>', $GLOBALS['openestate_environment_errors'] )
			           . '</li></ul>';
		}

		return $output;
	}

	// load attributes from the shortcode
	$settings = array();
	foreach ( $attributes as $key => $value ) {
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
	//echo '<pre>' . print_r($settings, true) . '</pre>';

	if ( is_file( IMMOTOOL_BASE_PATH . 'immotool.php.lock' ) ) {
		return '<h3>' . __( 'The properties are currently updated.', 'openestate-php-wrapper' ) . '</h3>' .
		       '<p>' . __( 'Please revisit this page after some minutes.', 'openestate-php-wrapper' ) . '</p>';
	}

	// keep wrapper settings in a global variable for further use
	$GLOBALS['openestate_wrapper_settings'] = $settings;

	// determine the script to load
	$wrap = ( isset( $_REQUEST['wrap'] ) ) ? $_REQUEST['wrap'] : null;
	if ( ! is_string( $wrap ) && isset( $settings['wrap'] ) ) {
		$wrap = $settings['wrap'];
	}
	if ( $wrap == 'expose' ) {
		$wrap   = 'expose';
		$script = 'expose.php';
		//echo '<pre>' . print_r($_REQUEST, true) . '</pre>'; return;

		// set default configuration values on the first request of the page
		if ( ! isset( $_REQUEST['wrap'] ) ) {
			if ( isset( $settings['lang'] ) ) {
				$_REQUEST[ IMMOTOOL_PARAM_LANG ] = $settings['lang'];
			}
			if ( isset( $settings['id'] ) ) {
				$_REQUEST[ IMMOTOOL_PARAM_EXPOSE_ID ] = $settings['id'];
			}
			if ( isset( $settings['view'] ) ) {
				$_REQUEST[ IMMOTOOL_PARAM_EXPOSE_VIEW ] = $settings['view'];
			}
		}
	} else {
		$wrap   = 'index';
		$script = 'index.php';
		//echo '<pre>' . print_r($_REQUEST, true) . '</pre>'; return;

		// set default configuration values on the first request of the page
		if ( ! isset( $_REQUEST['wrap'] ) ) {
			$_REQUEST[ IMMOTOOL_PARAM_INDEX_FILTER_CLEAR ] = '1';
			if ( isset( $settings['lang'] ) ) {
				$_REQUEST[ IMMOTOOL_PARAM_LANG ] = $settings['lang'];
			}
			if ( isset( $settings['view'] ) ) {
				$_REQUEST[ IMMOTOOL_PARAM_INDEX_VIEW ] = $settings['view'];
			}
			if ( isset( $settings['mode'] ) ) {
				$_REQUEST[ IMMOTOOL_PARAM_INDEX_MODE ] = $settings['mode'];
			}
			if ( isset( $settings['order_by'] ) ) {
				$order = $settings['order_by'];
				if ( isset( $settings['order_dir'] ) ) {
					$order .= '-' . $settings['order_dir'];
				} else {
					$order .= '-asc';
				}
				$_REQUEST[ IMMOTOOL_PARAM_INDEX_ORDER ] = $order;
			}
		}

		// clear filter selections, if this is explicitly selected
		if ( isset( $_REQUEST[ IMMOTOOL_PARAM_INDEX_RESET ] ) ) {
			unset( $_REQUEST[ IMMOTOOL_PARAM_INDEX_RESET ] );
			$_REQUEST[ IMMOTOOL_PARAM_INDEX_FILTER ]       = array();
			$_REQUEST[ IMMOTOOL_PARAM_INDEX_FILTER_CLEAR ] = '1';
		}

		// load configured filter criteria into the request
		if ( ! isset( $_REQUEST['wrap'] ) || isset( $_REQUEST[ IMMOTOOL_PARAM_INDEX_FILTER ] ) ) {
			$filters = ( isset( $settings['filter'] ) ) ? $settings['filter'] : null;
			if ( is_array( $filters ) ) {
				foreach ( $filters as $filter => $value ) {
					if ( ! isset( $_REQUEST[ IMMOTOOL_PARAM_INDEX_FILTER ] ) || ! is_array( $_REQUEST[ IMMOTOOL_PARAM_INDEX_FILTER ] ) ) {
						$_REQUEST[ IMMOTOOL_PARAM_INDEX_FILTER ] = array();
					}
					if ( ! isset( $_REQUEST[ IMMOTOOL_PARAM_INDEX_FILTER ][ $filter ] ) ) {
						$_REQUEST[ IMMOTOOL_PARAM_INDEX_FILTER ][ $filter ] = $value;
					}
				}
			}
		}
	}

	// execute the script
	//echo 'wrap: ' . IMMOTOOL_BASE_PATH . $script;
	ob_start();
	/** @noinspection PhpIncludeInspection */
	include( IMMOTOOL_BASE_PATH . $script );
	$page = ob_get_contents();
	ob_end_clean();

	// convert and return the script output
	$stylesheets  = array();
	$hiddenParams = array();
	if ( isset( $_REQUEST['p'] ) ) {
		$hiddenParams['p'] = $_REQUEST['p'];
	}
	if ( isset( $_REQUEST['cat'] ) ) {
		$hiddenParams['cat'] = $_REQUEST['cat'];
	}
	if ( isset( $_REQUEST['page_id'] ) ) {
		$hiddenParams['page_id'] = $_REQUEST['page_id'];
	}

	return immotool_functions::wrap_page(
		$page,
		$wrap,
		get_permalink(),
		IMMOTOOL_BASE_URL,
		$stylesheets,
		$hiddenParams
	);
}
