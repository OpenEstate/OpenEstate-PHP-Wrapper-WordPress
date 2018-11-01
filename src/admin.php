<?php

/** @noinspection HtmlUnknownTarget, HtmlFormInputWithoutLabel, ES6ConvertVarToLetConst */

use const \OpenEstate\PhpExport\VERSION;
use function htmlspecialchars as html;

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
if ( $environment->isSupportedLanguage( $locale ) ) {
	//echo 'SET LANGUAGE: ' . $locale;
	$environment->setLanguage( $locale );
} else {
	$l    = explode( '_', $locale );
	$lang = strtolower( $l[0] );
	if ( $environment->isSupportedLanguage( $lang ) ) {
		//echo 'SET LANGUAGE: ' . $lang;
		$environment->setLanguage( $lang );
	}
}

// show the admin page for the plugin
?>
<style type="text/css">
    table.openestate-wrapper-admin {
        border: none;
        width: 100%;
    }

    table.openestate-wrapper-admin td {
        border: none;
    }

    table.openestate-wrapper-admin td:first-child {
        vertical-align: top;
        width: 20%;
        text-align: right;
        white-space: nowrap;
        padding-right: 1em;
    }

    table.openestate-wrapper-admin td:not(first-child) {
        padding-bottom: 0.8em;
    }
</style>
<div class="wrap">
    <div style="clear:both; float:right; width:175px; background-color: #F0F0F0; padding:5px 5px 3px 5px; margin-top: 0.5em;">
        <h3 style="padding:0; margin:0;"><?= esc_html__( 'Module', 'openestate-php-wrapper' ) ?></h3>
        <div style="text-align:center;">
            OpenEstate PHP-Wrapper<br>
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
            <table class="openestate-wrapper-admin">
                <tr>
                    <td>&nbsp;</td>
                    <td>
						<?php
						if ( $environment !== null ) {
							echo '<h3 style="color:green; margin:0;">'
							     . esc_html__( 'The exported scripts are correctly configured.', 'openestate-php-wrapper' ) . '<br>'
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
                    <td>
                        <label for="openestate_wrapper_script_path">
							<?= esc_html__( 'script path', 'openestate-php-wrapper' ) ?>
                        </label>
                    </td>
                    <td>
                        <input type="text" id="openestate_wrapper_script_path" name="openestate_wrapper_script_path"
                               value="<?= html( $scriptPath ) ?>" style="width:100%;"/><br>
                        <em><?= esc_html__( 'Enter the path on your server, that points to the exported scripts. The path of this CMS installation is:', 'openestate-php-wrapper' ) ?></em>
                        <span style="font-weight:bold; white-space:nowrap;"><?= get_home_path() ?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="openestate_wrapper_script_url">
							<?= esc_html__( 'script URL', 'openestate-php-wrapper' ) ?>
                        </label>
                    </td>
                    <td>
                        <input type="text" id="openestate_wrapper_script_url" name="openestate_wrapper_script_url"
                               value="<?= html( $scriptUrl ) ?>" style="width:100%;"/><br>
                        <em><?= esc_html__( 'Enter the URL on your server, that points to the exported scripts. The URL of this CMS installation is:', 'openestate-php-wrapper' ) ?></em>
                        <span style="font-weight:bold; white-space:nowrap;"><?= get_bloginfo( 'url' ) ?></span>
                    </td>
                </tr>
            </table>
            <p style="text-align:center;">
                <input type="submit" class="button-primary"
                       value="<?= esc_html__( 'Save', 'openestate-php-wrapper' ) ?>"/>
            </p>
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
            <table id="openestate_wrap_index_settings" class="openestate-wrapper-admin">
                <tr>
                    <td>
                        <label for="openestate_wrap_index_view">
							<?= esc_html__( 'view', 'openestate-php-wrapper' ) ?>
                        </label>
                    </td>
                    <td>
                        <select id="openestate_wrap_index_view" onchange="openestate_update_shortcode();">
                            <option value="detail"><?= esc_html__( 'Tabular mode', 'openestate-php-wrapper' ) ?></option>
                            <option value="thumb"><?= esc_html__( 'Gallery mode', 'openestate-php-wrapper' ) ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>
                        <label for="openestate_wrap_index_order_by">
							<?= esc_html__( 'order by', 'openestate-php-wrapper' ) ?>
                        </label>
                    </td>
                    <td>
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
                        </select><br>
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
                        <td>
                            <label for="<?= html( $filterWidget->id ) ?>">
								<?= sprintf(
									esc_html__( 'filter by %s', 'openestate-php-wrapper' ),
									'<q>' . html( $filterObj->getTitle( $environment->getLanguage() ) ) . '</q>'
								) ?>
                            </label>
                        </td>
                        <td>
							<?= $filterWidget->generate() ?>
                        </td>
                    </tr>
					<?php
				}
				?>

                <tr>
                    <td>
                        <label for="openestate_wrap_index_lang">
							<?= esc_html__( 'language', 'openestate-php-wrapper' ) ?>
                        </label>
                    </td>
                    <td>
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
            <table id="openestate_wrap_expose_settings" class="openestate-wrapper-admin" style="visibility:collapse;">
                <tr>
                    <td>
                        <label for="openestate_wrap_expose_id">
							<?= esc_html__( 'property ID', 'openestate-php-wrapper' ) ?>
                        </label>
                    </td>
                    <td>
                        <input id="openestate_wrap_expose_id" type="text" maxlength="100" value=""
                               onchange="openestate_update_shortcode();"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="openestate_wrap_expose_lang">
							<?= esc_html__( 'language', 'openestate-php-wrapper' ) ?>
                        </label>
                    </td>
                    <td>
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
            <table id="openestate_wrap_fav_settings" class="openestate-wrapper-admin" style="visibility:collapse;">
                <tr>
                    <td>
                        <label for="openestate_wrap_fav_view">
							<?= esc_html__( 'view', 'openestate-php-wrapper' ) ?>
                        </label>
                    </td>
                    <td>
                        <select id="openestate_wrap_fav_view" onchange="openestate_update_shortcode();">
                            <option value="detail"><?= esc_html__( 'Tabular mode', 'openestate-php-wrapper' ) ?></option>
                            <option value="thumb"><?= esc_html__( 'Gallery mode', 'openestate-php-wrapper' ) ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>
                        <label for="openestate_wrap_fav_order_by">
							<?= esc_html__( 'order by', 'openestate-php-wrapper' ) ?>
                        </label>
                    </td>
                    <td>
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
                        </select><br>
                        <select id="openestate_wrap_fav_order_dir" onchange="openestate_update_shortcode();">
                            <option value="asc"><?= esc_html__( 'ascending', 'openestate-php-wrapper' ) ?></option>
                            <option value="desc"><?= esc_html__( 'descending', 'openestate-php-wrapper' ) ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>
                        <label for="openestate_wrap_fav_lang">
							<?= esc_html__( 'language', 'openestate-php-wrapper' ) ?>
                        </label>
                    </td>
                    <td>
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
                <table class="openestate-wrapper-admin">
                    <tr>
                        <td>
                            <label for="openestate_wrapper_customCss">
								<?= esc_html__( 'custom stylesheet', 'openestate-php-wrapper' ) ?>
                            </label>
                        </td>
                        <td>
                            <textarea id="openestate_wrapper_customCss" name="openestate_wrapper_customCss"
                                      style="width:100%; height:8em;"><?= html( get_option( 'openestate_wrapper_customCss' ) ) ?></textarea>
                            <br>
                            <em>
								<?= esc_html__( 'You can provide custom stylesheets, that are loaded together with the PHP export.', 'openestate-php-wrapper' ) ?>
                            </em>
                        </td>
                    </tr>
                    <tr>
                        <td>
							<?= esc_html__( 'embedded components', 'openestate-php-wrapper' ) ?>
                        </td>
                        <td>
                            <input id="openestate_wrapper_disabledComponents"
                                   name="openestate_wrapper_disabledComponents"
                                   type="text" readonly="readonly" style="display:none;"
                                   value="<?= html( implode( ',', $disabledComponents ) ) ?>">
							<?php foreach ( $environment->getTheme()->getComponentIds() as $componentId ) { ?>
                                <label style="margin-right:1em; white-space:nowrap;">
                                    <input type="checkbox" id="openestate_wrapper_component_<?= html( $componentId ) ?>"
                                           value="<?= html( $componentId ) ?>"
										<?= ( ! in_array( $componentId, $disabledComponents ) ) ? 'checked="checked"' : '' ?>
                                           onchange="openestate_update_components();"/>
                                    <i><?= html( $componentId ) ?></i>
                                </label>
							<?php } ?>
                            <br>
                            <em>
								<?= esc_html__( 'The PHP export integrates these third party components into your WordPress blog.', 'openestate-php-wrapper' ) ?>
								<?= esc_html__( 'If your website already uses some of these components, you can disable them accordingly.', 'openestate-php-wrapper' ) ?>
                            </em>
                        </td>
                    </tr>
                </table>
                <p style="text-align:center;">
                    <input type="submit" class="button-primary"
                           value="<?= esc_html__( 'Save', 'openestate-php-wrapper' ) ?>"/>
                </p>
            </form>

		<?php } ?>

    </div>
</div>
