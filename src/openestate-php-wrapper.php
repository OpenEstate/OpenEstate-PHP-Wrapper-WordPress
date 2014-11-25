<?php
/*
Plugin Name: OpenEstate PHP-Wrapper
Plugin URI: http://wiki.openestate.org/PHP-Wrapper_-_Wordpress
Description: This plugin integrates PHP-exported properties from OpenEstate-ImmoTool into WordPress.
Version: 0.2.3
Author: Andreas Rudolph, Walter Wagner (OpenEstate.org)
Author URI: http://openestate.org/
License: GPL3
Id: $Id: openestate-php-wrapper.php 1113 2011-10-21 19:11:06Z andy $
*/

add_action('init', 'openestate_wrapper_init');
function openestate_wrapper_init() {
  load_plugin_textdomain( 'openestate-php-wrapper', false, 'openestate-php-wrapper/languages' );
}

add_action('admin_menu', 'openestate_wrapper_menu');
function openestate_wrapper_menu() {
  add_options_page('OpenEstate PHP-Wrapper', 'OpenEstate-Wrapper', 'manage_options', 'openestate_wrapper_setup', 'openestate_wrapper_setup');

	//call register settings function
	add_action( 'admin_init', 'openestate_wrapper_settings' );
}
function openestate_wrapper_settings() {
	//register our settings
	register_setting( 'openestate-wrapper-setup', 'openestate_wrapper_script_path' );
	register_setting( 'openestate-wrapper-setup', 'openestate_wrapper_script_url' );
}
function openestate_wrapper_setup() {
  if (!current_user_can('manage_options'))  {
    wp_die( __('error_access_denied', 'openestate-php-wrapper') );
  }

  $scriptPath = trim( get_option('openestate_wrapper_script_path') );
  if (strlen($scriptPath)>0 && substr($scriptPath, -1)!='/') $scriptPath .= '/';

  $scriptUrl = trim( get_option('openestate_wrapper_script_url') );
  if (strlen($scriptUrl)>0 && substr($scriptUrl, -1)!='/') $scriptUrl .= '/';

  // ImmoTool-Umgebung einbinden
  $environmentErrors = array();
  $environmentFiles = array( 'config.php', 'include/functions.php', 'data/language.php' );
  if (!is_dir($scriptPath)) {
    $environmentErrors[] = __('error_no_export_path', 'openestate-php-wrapper');
  }
  else {
    define('IMMOTOOL_BASE_PATH', $scriptPath);
    foreach ($environmentFiles as $file) {
      if (!is_file(IMMOTOOL_BASE_PATH.$file))
        $environmentErrors[] = __('error_no_export_file_found', 'openestate-php-wrapper') . ': <i>' . $file . '</i>';
    }
    if (count($environmentErrors)==0) {
      define('IN_WEBSITE', 1);
      foreach ($environmentFiles as $file) {
        //echo IMMOTOOL_BASE_PATH . $file . '<hr/>';
        include(IMMOTOOL_BASE_PATH.$file);
      }
      if (!defined('IMMOTOOL_SCRIPT_VERSION'))
        $environmentErrors[] = __('error_no_export_version_found', 'openestate-php-wrapper');
    }
  }
  $environmentIsValid = count($environmentErrors)==0;

  // Wenn eine gültige ImmoTool-Umgebung konfiguriert ist, können weitere Einstellungen vorgenommen werden
  $setupIndex = null;
  $setupExpose = null;
  $setupTranslations = null;
  $setupLang = null;
  if ($environmentIsValid) {
    $setupIndex = new immotool_setup_index();
    $setupExpose = new immotool_setup_expose();
    $setupLang = immotool_functions::init_language( $setupIndex->DefaultLanguage, $setupIndex->DefaultLanguage, $setupTranslations );
    if (!is_array($setupTranslations)) {
      $environmentErrors[] = __('error_no_translation_found', 'openestate-php-wrapper');
      $environmentIsValid = false;
    }
  }
?>
<div class="wrap">
  <div style="clear:both; float:right; width:175px; background-color: #F0F0F0; padding:5px 5px 3px 5px; margin-top: 0.5em;">
    <h3 style="padding:0; margin:0;"><?php echo __('info_module', 'openestate-php-wrapper'); ?></h3>
    <div style="text-align:center;">
      OpenEstate PHP-Wrapper<br/>
      <?php echo __('info_version', 'openestate-php-wrapper'); ?> 0.2.2
    </div>
    <h3><?php echo __('info_license', 'openestate-php-wrapper'); ?></h3>
    <div style="text-align:center;">
      <a href="<?php echo get_bloginfo( 'url' ); ?>/wp-content/plugins/openestate-php-wrapper/gpl-3.0-standalone.html" target="_blank">GNU General Public License v3</a>
    </div>
    <h3><?php echo __('info_authors', 'openestate-php-wrapper'); ?></h3>
    <div style="text-align:center;">
      <a href="http://www.openestate.org/" target="_blank">
        <img src="<?php echo get_bloginfo( 'url' ); ?>/wp-content/plugins/openestate-php-wrapper/openestate.png" border="0" alt="0" />
        <div style="margin-top:0.5em;">Andreas Rudolph, Walter Wagner</div>
      </a>
    </div>
    <h3><?php echo __('info_support_us', 'openestate-php-wrapper'); ?></h3>
    <div style="text-align:center;">
      <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="hosted_button_id" value="11005790">
        <input type="image" src="https://www.paypal.com/de_DE/DE/i/btn/btn_donateCC_LG.gif" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal.">
        <img alt="" border="0" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1">
      </form>
    </div>
  </div>

  <div style="margin-right:200px;">
    <h2>OpenEstate PHP-Wrapper</h2>

    <h3 style="margin-top:1.5em;"><?php echo __('setup', 'openestate-php-wrapper'); ?></h3>
    <form method="post" action="options.php">
      <?php settings_fields( 'openestate-wrapper-setup' ); ?>
      <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr>
          <td style="text-align:right; width:100px; vertical-align:top;">&nbsp;</td>
          <td>
            <?php
            if ($environmentIsValid) {
              echo '<h3 style="color:green; margin:0;">' .
                      __('setup_success', 'openestate-php-wrapper').'<br/>'.
                      '<span style="font-size:0.7em;">' . __('info_version', 'openestate-php-wrapper') . ' ' . IMMOTOOL_SCRIPT_VERSION . '</span>'.
                      '</h3>';
            }
            else {
              echo '<h3 style="color:red; margin-top:0;">' . __('setup_problem', 'openestate-php-wrapper') . '</h3>';
              echo '<ul>';
              echo '<li style="color:red;">&raquo; ' . __('setup_step_export', 'openestate-php-wrapper') . '</li>';
              echo '<li style="color:red;">&raquo; ' . __('setup_step_config', 'openestate-php-wrapper') . '</li>';
              echo '</ul>';
              echo '<h3 style="color:red;">' . __('setup_errors', 'openestate-php-wrapper') . '</h3>';
              echo '<ul>';
              foreach ($environmentErrors as $error) echo '<li style="color:red;">&raquo; ' . $error . '</li>';
              echo '</ul>';
            }
            ?>
          </td>
        </tr>
        <tr>
          <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;"><?php echo __('setup_path', 'openestate-php-wrapper'); ?></td>
          <td><input type="text" name="openestate_wrapper_script_path" value="<?php echo $scriptPath; ?>" style="width:100%;"/><br/><i><?php echo __('setup_path_info', 'openestate-php-wrapper'); ?></i> <span style="font-weight:bold; white-space:nowrap;"><?php echo $_SERVER['DOCUMENT_ROOT']; ?></span></td>
        </tr>
        <tr>
          <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;"><?php echo __('setup_url', 'openestate-php-wrapper'); ?></td>
          <td><input type="text" name="openestate_wrapper_script_url" value="<?php echo $scriptUrl; ?>" style="width:100%;"/><br/><i><?php echo __('setup_url_info', 'openestate-php-wrapper'); ?></i> <span style="font-weight:bold; white-space:nowrap;"><?php echo get_bloginfo( 'url' ); ?></span></td>
        </tr>
        <tr>
          <td colspan="2" style="text-align:center; padding:1em;"><input type="submit" value="<?php echo __('setup_submit', 'openestate-php-wrapper'); ?>" class="button-primary"/></td>
        </tr>
      </table>
    </form>

    <?php if ($environmentIsValid) { ?>
<script language="JavaScript" type="text/javascript">
<!--
function show_wrapper_settings( $value )
{
  document.getElementById( 'immotool_wrap_script_index_settings' ).style.visibility = ($value=='index')? 'visible': 'collapse';
  document.getElementById( 'immotool_wrap_script_expose_settings' ).style.visibility = ($value=='expose')? 'visible': 'collapse';
}
function build_tag()
{
  //alert( 'build_tag' );
  var obj = document.getElementById('openestate_wrapper');
  if (obj==null) return;
  var wrap_index = document.getElementById('immotool_wrap_script_index');
  var wrap_expose = document.getElementById('immotool_wrap_script_expose');

  var obj2 = null;
  var params = '';

  if (wrap_index!=null && wrap_index.checked==true)
  {
    params += ' wrap="' + wrap_index.value + '"';

    obj2 = document.getElementById('index_view');
    if (obj2!=null) params += ' view="' + obj2.value + '"';

    obj2 = document.getElementById('index_mode');
    if (obj2!=null) params += ' mode="' + obj2.value + '"';

    obj2 = document.getElementById('index_lang');
    if (obj2!=null) params += ' lang="' + obj2.value + '"';

    obj2 = document.getElementById('index_order_by');
    if (obj2!=null) params += ' order_by="' + obj2.value + '"';

    obj2 = document.getElementById('index_order_dir');
    if (obj2!=null) params += ' order_dir="' + obj2.value + '"';

    var filters = [];
<?php
$i = 0;
foreach (immotool_functions::list_available_filters() as $key) {
  echo '    filters['.$i.'] = \''.$key.'\';' . "\n";
  $i++;
} ?>
    for (var i=0; i<filters.length; i++)
    {
      obj2 = document.getElementById('filter_' + filters[i]);
      if (obj2==null) continue;
      val = '';
      //alert( filters[i] + ': ' + obj2.checked );
      if (obj2.checked==true || obj2.checked==false)
      {
        if (obj2.checked==true) val = obj2.value;
      }
      else
      {
        val = obj2.value;
      }
      if (val!='' && obj2!=null) params += ' filter_' + filters[i] + '="' + val + '"';
    }
  }

  else if (wrap_expose!=null && wrap_expose.checked==true)
  {
    params += ' wrap="' + wrap_expose.value + '"';

    obj2 = document.getElementById('expose_view');
    if (obj2!=null) params += ' view="' + obj2.value + '"';

    obj2 = document.getElementById('expose_lang');
    if (obj2!=null) params += ' lang="' + obj2.value + '"';

    obj2 = document.getElementById('expose_id');
    if (obj2!=null && obj2.value!='') params += ' id="' + obj2.value + '"';
  }

  obj.innerHTML = '[OpenEstatePhpWrapper' + params + ']';
}
//-->
</script>
    <h3 style="margin-top:1.5em;"><?php echo __('view', 'openestate-php-wrapper'); ?></h3>
    <p><?php echo __('view_info', 'openestate-php-wrapper'); ?></p>
    <textarea id="openestate_wrapper" style="border:1px solid red; background-color:#FFFFE0; padding:0.5em; font-family:monospace; width:100%;" readonly="readonly" onclick="this.select();" cols="50" rows="2">[OpenEstatePhpWrapper]</textarea>
    <h4>
      <input type="radio" id="immotool_wrap_script_index" name="immotool_wrap_script" value="index" onchange="show_wrapper_settings('index');build_tag();" checked="checked" />
      <label for="immotool_wrap_script_index"><?php echo __('view_index', 'openestate-php-wrapper'); ?></label>
    </h4>
    <table cellpadding="0" cellspacing="0" border="0" id="immotool_wrap_script_index_settings" style="width:100%;">

      <tr>
        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;"><?php echo __('view_index_view', 'openestate-php-wrapper'); ?></td>
        <td style="padding-bottom:0.8em;">
          <select id="index_view" style="border:1px solid #c0c0c0;" onchange="build_tag();">
            <option value="index"><?php echo __('view_index_view_summary', 'openestate-php-wrapper'); ?></option>
            <option value="fav"><?php echo __('view_index_view_fav', 'openestate-php-wrapper'); ?></option>
          </select>
        </td>
      </tr>

      <tr>
        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;"><?php echo __('view_index_mode', 'openestate-php-wrapper'); ?></td>
        <td style="padding-bottom:0.8em;">
          <select id="index_mode" style="border:1px solid #c0c0c0;" onchange="build_tag();">
            <option value="entry"><?php echo __('view_index_mode_entry', 'openestate-php-wrapper'); ?></option>
            <option value="gallery"><?php echo __('view_index_mode_gallery', 'openestate-php-wrapper'); ?></option>
          </select>
        </td>
      </tr>

      <tr>
        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;"><?php echo __('view_index_language', 'openestate-php-wrapper'); ?></td>
        <td style="padding-bottom:0.8em;">
          <select id="index_lang" style="border:1px solid #c0c0c0;" onchange="build_tag();">
              <?php
              foreach (immotool_functions::get_language_codes() as $code) {
                echo '<option value="' . $code . '">' . immotool_functions::get_language_name( $code ) . '</option>';
              }
              ?>
          </select>
        </td>
      </tr>

      <tr>
        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;"><?php echo __('view_index_order', 'openestate-php-wrapper'); ?></td>
        <td style="padding-bottom:0.8em;">
          <select id="index_order_by" style="border:1px solid #c0c0c0;" onchange="build_tag();">
              <?php
              $sortedOrders = array();
              $availableOrders = array();
              foreach ($setupIndex->OrderOptions as $key) {
                $orderObj = immotool_functions::get_order($key);
                //$by = $orderObj->getName();
                $by = $orderObj->getTitle( $setupTranslations, $setupLang );
                $sortedOrders[$key] = $by;
                $availableOrders[$key] = $orderObj;
              }
              asort($sortedOrders);

              foreach ($sortedOrders as $key=>$by) {
                $orderObj = $availableOrders[$key];
                echo '<option value="' . $key . '">' . $by . '</option>';
              }
              ?>
          </select><br/>
          <select id="index_order_dir" style="border:1px solid #c0c0c0;" onchange="build_tag();">
            <option value="asc"><?php echo __('view_index_order_asc', 'openestate-php-wrapper'); ?></option>
            <option value="desc"><?php echo __('view_index_order_desc', 'openestate-php-wrapper'); ?></option>
          </select>
        </td>
      </tr>

      <?php
      //foreach ($setupIndex->FilterOptions as $key)
      foreach (immotool_functions::list_available_filters() as $key) {
        $filterObj = immotool_functions::get_filter( $key );
        if (!is_object($filterObj)) {
          //echo "Filter-Objekt $key nicht gefunden<hr/>";
          continue;
        }
        $filterValue = (isset($settings['immotool_index']['filter'][$key]))? $settings['immotool_index']['filter'][$key]: '';
        $filterWidget = $filterObj->getWidget( $filterValue, $setupLang, $setupTranslations, $setupIndex );
        if (!is_string($filterWidget) || strlen($filterWidget)==0) {
          //echo "Filter-Widget $key nicht erzeugt<hr/>";
          continue;
        }
        $filterWidget = str_replace( '<select ', '<select style="border:1px solid #c0c0c0;" ', $filterWidget );
        $filterWidget = str_replace( '<select ', '<select onchange="build_tag();" ', $filterWidget );
        $filterWidget = str_replace( '<input ', '<input onchange="build_tag();" ', $filterWidget );
        ?>
      <tr>
        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;"><?php echo __('view_index_filter', 'openestate-php-wrapper'); ?><br/><span style="font-style:italic;font-size:0.9em;"><?php echo $filterObj->getTitle( $setupTranslations, $setupLang ); ?></span></td>
        <td style="padding-bottom:0.8em;"><?php echo $filterWidget; ?></td>
      </tr>
        <?php
      }
      ?>
    </table>

    <h4>
      <input type="radio" id="immotool_wrap_script_expose" name="immotool_wrap_script" value="expose" onchange="show_wrapper_settings('expose');build_tag();"/>
      <label for="immotool_wrap_script_expose"><?php echo __('view_expose', 'openestate-php-wrapper'); ?></label>
    </h4>
    <table cellpadding="0" cellspacing="0" border="0" id="immotool_wrap_script_expose_settings" style="width:100%;visibility:collapse;">
      <tr>
        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;"><?php echo __('view_expose_id', 'openestate-php-wrapper'); ?></td>
        <td style="padding-bottom:0.8em;">
          <input id="expose_id" type="text" style="border:1px solid #c0c0c0;" maxlength="15" value="" onchange="build_tag();"/>
        </td>
      </tr>
      <tr>
        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;"><?php echo __('view_expose_view', 'openestate-php-wrapper'); ?></td>
        <td style="padding-bottom:0.8em;">
          <select id="expose_view" style="border:1px solid #c0c0c0;" onchange="build_tag();">
            <option value="details"><?php echo __('view_expose_view_details', 'openestate-php-wrapper'); ?></option>
            <option value="texts"><?php echo __('view_expose_view_texts', 'openestate-php-wrapper'); ?></option>
            <option value="gallery"><?php echo __('view_expose_view_gallery', 'openestate-php-wrapper'); ?></option>
            <option value="contact"><?php echo __('view_expose_view_contact', 'openestate-php-wrapper'); ?></option>
            <option value="terms"><?php echo __('view_expose_view_terms', 'openestate-php-wrapper'); ?></option>
          </select>
        </td>
      </tr>
      <tr>
        <td style="width:20%; text-align:right; white-space:nowrap; padding-right:1em; vertical-align:top;"><?php echo __('view_expose_language', 'openestate-php-wrapper'); ?></td>
        <td style="padding-bottom:0.8em;">
          <select id="expose_lang" style="border:1px solid #c0c0c0;" onchange="build_tag();">
              <?php
              foreach (immotool_functions::get_language_codes() as $code) {
                $selected = ($settings['immotool_expose']['lang']==$code)? 'selected="selected"': '';
                echo '<option value="' . $code . '" ' . $selected . '>' . immotool_functions::get_language_name( $code ) . '</option>';
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

add_filter('the_content', 'openestate_wrapper_post');
function openestate_wrapper_post( $post ) {
  if (!is_single() && !is_page()) return $post;

  // OpenEstate-Platzhalter suchen und ersetzen
  $regex = '/\[\s?OpenEstatePhpWrapper\s+([^\]]*)\]/is';
  return preg_replace_callback( $regex, 'openestate_wrapper_post_callback', $post );
}
function openestate_wrapper_post_callback( $matches ) {

  // Konfiguration im OpenEstate-Platzhalter ermitteln
  //echo '<pre>'; print_r($matches); echo '</pre>';
  $regex = '/\s?([^=]*)\s?="([^"]*)"/is';
  $values = array();
  preg_match_all( $regex, $matches[1], $values );
  //echo '<pre>'; print_r($values); echo '</pre>';
  $settings = array();
  foreach ($values[1] as $pos=>$key) {
    $key = trim($key);
    if (substr($key, 0, 7)=='filter_') {
      if (!isset($settings['filter'])) $settings['filter'] = array();
      $settings['filter'][substr($key,7)] = $values[2][$pos];
    }
    else {
      $settings[$key] = $values[2][$pos];
    }
  }
  //echo '<pre>'; print_r($settings); echo '</pre>';

  // Server-Pfad zu den ImmoTool-Skripten
  if (!defined('IMMOTOOL_BASE_PATH')) {
    $scriptPath = trim( get_option('openestate_wrapper_script_path') );
    if (strlen($scriptPath)>0 && substr($scriptPath, -1)!='/') $scriptPath .= '/';
    define( 'IMMOTOOL_BASE_PATH', $scriptPath );
  }

  // URL zu den ImmoTool-Skripten
  if (!defined('IMMOTOOL_BASE_URL')) {
    $scriptUrl = trim( get_option('openestate_wrapper_script_url') );
    if (strlen($scriptUrl)>0 && substr($scriptUrl, -1)!='/') $scriptUrl .= '/';
    define( 'IMMOTOOL_BASE_URL', $scriptUrl );
  }

  if (is_file(IMMOTOOL_BASE_PATH . 'immotool.php.lock')) {
    return __('error_update_is_running', 'openestate-php-wrapper');
  }

  // Definition der zu verwendenden Parameter.
  if (!defined('IMMOTOOL_PARAM_LANG'))
    define('IMMOTOOL_PARAM_LANG', 'wrapped_lang');
  if (!defined('IMMOTOOL_PARAM_FAV'))
    define('IMMOTOOL_PARAM_FAV', 'wrapped_fav');
  if (!defined('IMMOTOOL_PARAM_INDEX_PAGE'))
    define('IMMOTOOL_PARAM_INDEX_PAGE', 'wrapped_page');
  if (!defined('IMMOTOOL_PARAM_INDEX_RESET'))
    define('IMMOTOOL_PARAM_INDEX_RESET', 'wrapped_reset');
  if (!defined('IMMOTOOL_PARAM_INDEX_ORDER'))
    define('IMMOTOOL_PARAM_INDEX_ORDER', 'wrapped_order');
  if (!defined('IMMOTOOL_PARAM_INDEX_FILTER'))
    define('IMMOTOOL_PARAM_INDEX_FILTER', 'wrapped_filter');
  if (!defined('IMMOTOOL_PARAM_INDEX_FILTER_CLEAR'))
    define('IMMOTOOL_PARAM_INDEX_FILTER_CLEAR', 'wrapped_clearFilters');
  if (!defined('IMMOTOOL_PARAM_INDEX_VIEW'))
    define('IMMOTOOL_PARAM_INDEX_VIEW', 'wrapped_view');
  if (!defined('IMMOTOOL_PARAM_INDEX_MODE'))
    define('IMMOTOOL_PARAM_INDEX_MODE', 'wrapped_mode');
  if (!defined('IMMOTOOL_PARAM_EXPOSE_ID'))
    define('IMMOTOOL_PARAM_EXPOSE_ID', 'wrapped_id');
  if (!defined('IMMOTOOL_PARAM_EXPOSE_VIEW'))
    define('IMMOTOOL_PARAM_EXPOSE_VIEW', 'wrapped_view');
  if (!defined('IMMOTOOL_PARAM_EXPOSE_IMG'))
    define('IMMOTOOL_PARAM_EXPOSE_IMG', 'wrapped_img');
  if (!defined('IMMOTOOL_PARAM_EXPOSE_CONTACT'))
    define('IMMOTOOL_PARAM_EXPOSE_CONTACT', 'wrapped_contact');
  if (!defined('IMMOTOOL_PARAM_EXPOSE_CAPTCHA'))
    define('IMMOTOOL_PARAM_EXPOSE_CAPTCHA', 'wrapped_captchacode');

  // Script ermitteln
  $wrap = (isset($_REQUEST['wrap']) && is_string($_REQUEST['wrap']))? $_REQUEST['wrap']: $settings['wrap'];
  if ($wrap=='expose') {
    $wrap = 'expose';
    $script = 'expose.php';

    // Standard-Parameter ggf. setzen
    //echo '<pre>';
    //print_r($_REQUEST);
    //echo '</pre>';
    $params = array( 'wrap', IMMOTOOL_PARAM_LANG, IMMOTOOL_PARAM_EXPOSE_ID, IMMOTOOL_PARAM_EXPOSE_VIEW );
    $useDefaultParams = true;
    foreach ($params as $param) {
      if (isset($_REQUEST[ $param ])) {
        $useDefaultParams = false;
        break;
      }
    }
    if ($useDefaultParams) {
      if (isset($settings['lang']))
        $_REQUEST[ IMMOTOOL_PARAM_LANG ] = $settings['lang'];
      if (isset($settings['id']))
        $_REQUEST[ IMMOTOOL_PARAM_EXPOSE_ID ] = $settings['id'];
      if (isset($settings['view']))
        $_REQUEST[ IMMOTOOL_PARAM_EXPOSE_VIEW ] = $settings['view'];
    }
  }
  else {
    $wrap = 'index';
    $script = 'index.php';

    // Standard-Parameter ggf. setzen
    //echo '<pre>';
    //print_r($_REQUEST);
    //echo '</pre>';
    $params = array( 'wrap', IMMOTOOL_PARAM_LANG, IMMOTOOL_PARAM_INDEX_VIEW, IMMOTOOL_PARAM_INDEX_MODE, IMMOTOOL_PARAM_INDEX_ORDER, IMMOTOOL_PARAM_INDEX_FILTER );
    $useDefaultParams = true;
    foreach ($params as $param) {
      if (isset($_REQUEST[ $param ])) {
        $useDefaultParams = false;
        break;
      }
    }
    if ($useDefaultParams) {
      $_REQUEST[ IMMOTOOL_PARAM_INDEX_FILTER_CLEAR ] = '1';
      if (isset($settings['lang']))
        $_REQUEST[ IMMOTOOL_PARAM_LANG ] = $settings['lang'];
      if (isset($settings['view']))
        $_REQUEST[ IMMOTOOL_PARAM_INDEX_VIEW ] = $settings['view'];
      if (isset($settings['mode']))
        $_REQUEST[ IMMOTOOL_PARAM_INDEX_MODE ] = $settings['mode'];
      if (isset($settings['order_by']) && isset($settings['order_dir']))
        $_REQUEST[ IMMOTOOL_PARAM_INDEX_ORDER ] = $settings['order_by'].'-'.$settings['order_dir'];
      if (isset($settings['filter']))
        $_REQUEST[ IMMOTOOL_PARAM_INDEX_FILTER ] = $settings['filter'];
    }
  }

  // Script ausführen
  //echo 'wrap: ' . IMMOTOOL_BASE_PATH . $script;
  ob_start();
  include( IMMOTOOL_BASE_PATH . $script );
  $page = ob_get_contents();
  //ob_clean();
  ob_end_clean();

  // Stylesheets
  $stylesheets = array();
  $stylesheets[] = IMMOTOOL_BASE_URL . 'style.php';
  if (class_exists('immotool_setup')) {
    $setup = new immotool_setup();
    if (is_callable(array('immotool_myconfig', 'load_config_default'))) immotool_myconfig::load_config_default( $setup );
    if (is_string($setup->AdditionalStylesheet) && strlen($setup->AdditionalStylesheet)>0)
      $stylesheets[] = $setup->AdditionalStylesheet;
  }

  // Ausgabe erzeugen
  $hiddenParams = array();
  if (isset($_REQUEST['p'])) $hiddenParams['p'] = $_REQUEST['p'];
  if (isset($_REQUEST['cat'])) $hiddenParams['cat'] = $_REQUEST['cat'];
  if (isset($_REQUEST['page_id'])) $hiddenParams['page_id'] = $_REQUEST['page_id'];
  return immotool_functions::wrap_page( $page, $wrap, get_permalink(), IMMOTOOL_BASE_URL, $stylesheets, $hiddenParams );
}
