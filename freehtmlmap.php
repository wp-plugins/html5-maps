<?php
/*
Plugin Name: HTML5 Maps
Plugin URI: http://www.fla-shop.com
Description: High-quality map plugin for WordPress. The map depicts regions (states, provinces, counties etc.) and features color, landing page and popup customization.
Version: 1.3
Author: Fla-shop.com
Author URI: http://www.fla-shop.com
License: GPLv2 or later
*/

if (isset($_REQUEST['action']) && $_REQUEST['action']=='free_map_export') { free_map_export(); }

add_action('admin_menu', 'free_map_plugin_menu');

function free_map_plugin_menu() {

    add_menu_page(__('HTML5 Map Settings','free-html5-map'), __('HTML5 Map Settings','free-html5-map'), 'manage_options', 'free-map-plugin-main', 'free_map_plugin_maps' );

    add_submenu_page('free-map-plugin-main', __('Maps','free-html5-map'), __('Maps','free-html5-map'), 'manage_options', 'free-map-plugin-maps', 'free_map_plugin_maps');
    
    add_submenu_page('free-map-plugin-main', __('Main settings','free-html5-map'), __('Main settings','free-html5-map'), 'manage_options', 'free-map-plugin-options', 'free_map_plugin_options');
    add_submenu_page('free-map-plugin-main', __('Detailed settings','free-html5-map'), __('Detailed settings','free-html5-map'), 'manage_options', 'free-map-plugin-states', 'free_map_plugin_states');
    add_submenu_page('free-map-plugin-main', __('Map Preview','free-html5-map'), __('Map Preview','free-html5-map'), 'manage_options', 'free-map-plugin-view', 'free_map_plugin_view');

    remove_submenu_page('free-map-plugin-main','free-map-plugin-main');
    
}

function free_map_plugin_options() {
    include('editmainconfig.php');
}

function free_map_plugin_states() {
    include('editstatesconfig.php');
}

function free_map_plugin_maps() {
    include('mapslist.php');
}

function free_map_plugin_view() {
    
    free_map_check_map_exists();
    
    $options = get_site_option('freehtml5map_options');
    $map_id  = (isset($_REQUEST['map_id'])) ? intval($_REQUEST['map_id']) : array_shift(array_keys($options)) ;
    
?>

    <div style="clear: both"></div>

    <h2>Map Preview</h2>
    
    <script type="text/javascript">
        jQuery(function(){

            jQuery('select[name=map_id]').change(function() {
                location.href='admin.php?page=free-map-plugin-view&map_id='+jQuery(this).val();
            });
    
        });
    </script>

    <span class="title">Map: </span>
    <select name="map_id" style="width: 185px;">
        <?php foreach($options as $id => $map_data) { ?>
            <option value="<?php echo $id; ?>" <?php echo ($id==$map_id)?'selected':'';?>><?php echo $map_data['name']; ?></option>
        <?php } ?>
    </select>

    <div style="clear: both; height: 30px;"></div>    
    
<?php

    echo '<p>Use shortcode <b>[freehtml5map id="'.$map_id.'"]</b> for install this map</p>';

    echo do_shortcode('<div style="width: 99%">[freehtml5map id="'.$map_id.'"]</div>');
}

add_action('admin_init','free_map_plugin_scripts');

function free_map_plugin_scripts(){
    
    
    
    if ( is_admin() ){

        wp_register_style('jquery-tipsy', plugins_url('/static/css/tipsy.css', __FILE__));
        wp_enqueue_style('jquery-tipsy');
        wp_register_style('free-html5-mapadm', plugins_url('/static/css/mapadm.css', __FILE__));
        wp_enqueue_style('free-html5-mapadm');
        wp_enqueue_style('farbtastic');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('farbtastic');
        wp_enqueue_script('tiny_mce');
        wp_register_script('jquery-tipsy', plugins_url('/static/js/jquery.tipsy.js', __FILE__));
        wp_enqueue_script('jquery-tipsy');

    }
    else {

        $options = get_site_option('freehtml5map_options');
    
        wp_register_style('free-html5-map-style', plugins_url('/static/css/map.css', __FILE__));
        wp_enqueue_style('free-html5-map-style');
        wp_register_script('raphael', plugins_url('/static/js/raphael-min.js', __FILE__));
        wp_enqueue_script('raphael');
        
        
        $path = isset($options[0]['data_file']) ? $options[0]['data_file'] : $options[0]['defaultDataFile'];
        wp_register_script('free-html5-map-js', $path);
        wp_enqueue_script('free-html5-map-js');
        
        wp_enqueue_script('jquery');

    }
}

add_action('wp_enqueue_scripts', 'free_map_plugin_scripts_method');

function free_map_plugin_scripts_method() {
    wp_enqueue_script('jquery');
}


add_shortcode( 'freehtml5map', 'free_map_plugin_content' );

function free_map_plugin_content($atts, $content) {

    $dir               = WP_PLUGIN_URL.'/html5-maps/static/';
    $siteURL           = get_site_url();
    $options           = get_site_option('freehtml5map_options');
    
    if (isset($atts['id'])) {
        $map_id  = intval($atts['id']);
        $options = $options[$map_id];
    } else {
        $map_id  = array_shift(array_keys($options));
        $options = array_shift($options);
    }
    
    $isResponsive      = $options['isResponsive'];
    $stateInfoArea     = $options['statesInfoArea'];
    $respInfo          = $isResponsive ? ' htmlMapResponsive' : '';
    $popupNameColor    = $options['popupNameColor'];
    $popupNameFontSize = $options['popupNameFontSize'].'px';

    $style             = (!empty($options['maxWidth']) && $isResponsive) ? 'max-width:'.intval($options['maxWidth']).'px' : '';
    
    $path_js           = ($options['df_type']==1) ? $options['data_file'] : $options['defaultDataFile'];
    
    $mapInit = "
        <!-- start Fla-shop.com HTML5 Map -->	
        <div class='freeHtmlMap$stateInfoArea$respInfo' style='$style'>
        <div id='map-container'></div>
            <link href='{$dir}css/map.css' rel='stylesheet'>
            <style>
                body .fm-tooltip {
                    color: $popupNameColor;
                    font-size: $popupNameFontSize;
                }
            </style>
            <script src='{$dir}js/raphael-min.js'></script>
            <script src='{$siteURL}/index.php?freemap_js_data=true&map_id=$map_id&r=".rand(11111,99999)."'></script>
            <script src='$path_js'></script>
            <script>
				var map = new FlaMap(map_cfg);
				map.draw('map-container');
            </script>
            <script>
                function free_map_set_state_text(state) {
                    jQuery('#freeHtmlMapStateInfo').html('Loading...');
                    jQuery.ajax({
                        type: 'POST',
                        url: '{$siteURL}/index.php?freemap_get_state_info='+state+'&map_id=$map_id',
                        success: function(data, textStatus, jqXHR){
                            jQuery('#freeHtmlMapStateInfo').html(data);
                        },
                        dataType: 'text'
                    });
                }
            </script>
            <div id='freeHtmlMapStateInfo'></div>
            </div>
            <div style='clear: both'></div>
			<!-- end HTML5 Map -->
    ";
    
    return $mapInit;
}


$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'free_map_plugin_settings_link' );

function free_map_plugin_settings_link($links) {
    $settings_link = '<a href="admin.php?page=free-map-plugin-options">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}


add_action( 'parse_request', 'free_map_plugin_wp_request' );

function free_map_plugin_wp_request( $wp ) {
    
    if (isset($_REQUEST['freemap_js_data']) or isset($_REQUEST['freemap_get_state_info'])) {
        $map_id  = intval($_REQUEST['map_id']);
        $options = get_site_option('freehtml5map_options');
        $options = $options[$map_id];
    }
    
    $options['map_data'] = htmlspecialchars_decode($options['map_data']);
    
    if( isset($_GET['freemap_js_data']) ) {

        header( 'Content-Type: application/javascript' );
       ?>
    
        var	map_cfg = {
        
        <?php  if(!$options['isResponsive']) { ?>
        mapWidth		: <?php echo $options['mapWidth']; ?>,
        mapHeight		: <?php echo $options['mapHeight']; ?>,
        <?php }     else { ?>
			mapWidth		: 0,
			<?php } ?>
        
        shadowWidth		: <?php echo $options['shadowWidth']; ?>,
        shadowOpacity		: <?php echo $options['shadowOpacity']; ?>,
        shadowColor		: "<?php echo $options['shadowColor']; ?>",
        shadowX			: <?php echo $options['shadowX']; ?>,
        shadowY			: <?php echo $options['shadowY']; ?>,

        iPhoneLink		: <?php echo $options['iPhoneLink']; ?>,

        isNewWindow		: <?php echo $options['isNewWindow']; ?>,

        borderColor		: "<?php echo $options['borderColor']; ?>",
        borderColorOver		: "<?php echo $options['borderColorOver']; ?>",

        nameColor		: "<?php echo $options['nameColor']; ?>",
        popupNameColor		: "<?php echo $options['popupNameColor']; ?>",
        //nameFontSize		: "<?php echo $options['nameFontSize'].'px'; ?>",
        popupNameFontSize	: "<?php echo $options['popupNameFontSize'].'px'; ?>",
        nameFontWeight		: "<?php echo $options['nameFontWeight']; ?>",

        overDelay		: <?php echo $options['overDelay']; ?>,
        nameStroke		: <?php echo $options['nameStroke']?'true':'false'; ?>,
        nameStrokeColor		: "<?php echo $options['nameStrokeColor']; ?>",
        map_data        : <?php echo $options['map_data']; ?>
		}

        <?php

        exit;
    }

    if(isset($_GET['freemap_get_state_info'])) {
        $stateId = (int) $_GET['freemap_get_state_info'];

        //echo nl2br($options['state_info'][$stateId]);
		echo nl2br(apply_filters('the_content',$options['state_info'][$stateId]));

        exit;
    }
}


function free_map_plugin_map_defaults($name='New map',$type=0) {
    
    $type = free_map_get_map_types($type);
    
    $initialStatesPath = dirname(__FILE__).'/static/settings/'.$type->defaultSettings;
    
    if (!file_exists($initialStatesPath)) {
        echo '<div class="error"><p>'.__( 'Settings file not found for map of '.$type->name, 'free-html5-map' ).'</p></div>';
        return false;
    }
    
    $map_data = file_get_contents($initialStatesPath);
    
    $defaults = array(
                        'name'              => $name,
                        'type'              => "",
                        'map_data'          => $map_data,
                        'mapWidth'          => 500,
                        'mapHeight'         => 400,
                        'maxWidth'          => 780,
                        'shadowWidth'       => 2,
                        'shadowOpacity'     => 0.3,
                        'shadowColor'       => "black",
                        'shadowX'           => 0,
                        'shadowY'           => 0,
                        'iPhoneLink'        => "true",
                        'isNewWindow'       => "false",
                        'borderColor'       => "#ffffff",
                        'borderColorOver'   => "#ffffff",
                        'nameColor'         => "#ffffff",
                        'popupNameColor'    => "#000000",
                        'nameFontSize'      => "10",
                        'popupNameFontSize' => "20",
                        'nameFontWeight'    => "bold",
                        'overDelay'         => 300,
                        'statesInfoArea'    => "bottom",
                        'isResponsive'      => "1",
                        'nameStroke'        => true,
                        'nameStrokeColor'   => "#000000",
                        'defaultDataFile'   => "",
                    );
    
    
    $type->type = $type->name;
    $type->name = $name;
    $defaults   = wp_parse_args( (array)$type, $defaults );
    
    $map_data   = json_decode($map_data);
    $count      = count((array)$map_data);
    
    for($i = 1; $i <= $count; $i++) {
        $defaults['state_info'][$i] = '';
    }
    
    return $defaults;
}


register_activation_hook( __FILE__, 'free_map_plugin_activation' );

function free_map_plugin_activation() {
    
    add_site_option('freehtml5map_options', $options);
    
}

register_deactivation_hook( __FILE__, 'free_map_plugin_deactivation' );

function free_map_plugin_deactivation() {

}

register_uninstall_hook( __FILE__, 'free_map_plugin_uninstall' );

function free_map_plugin_uninstall() {
    delete_site_option('freehtml5map_options');
}

add_filter('widget_text', 'do_shortcode');

function free_map_export() {
    $maps    = explode(',',sanitize_text_field($_REQUEST['maps']));
    $options = get_site_option('freehtml5map_options');
    
    foreach($options as $map_id => $option) {
        if (!in_array($map_id,$maps)) {
            unset($options[$map_id]);
        }
    }
    
    if (count($options)>0) {
        $options = json_encode($options);
        $options = htmlspecialchars_decode($options);
        
        header($_SERVER["SERVER_PROTOCOL"] . ' 200 OK');
        header('Content-Type: text/json');
        header('Content-Length: ' . (strlen($options)));
        header('Connection: close');
        header('Content-Disposition: attachment; filename="maps.json";');
        echo $options;
        
        exit();
    }

}

function free_map_check_map_exists($redirect=true) {
    
    $options  = get_site_option('freehtml5map_options');
    $exists   = (is_array($options) && count($options));
    
    if ($redirect && !$exists) {
        
        echo '<script type="text/javascript">location.href = "admin.php?page=free-map-plugin-maps&msg=1";</script>';
        exit();
        
    } else {
        return $exists;
    }
    
}

function free_map_get_map_types($id='') {
    
    $types = dirname(__FILE__).'/static/map_types.json';
    $hwnd  = fopen($types,'r');
    $types = fread($hwnd,filesize($types)); fclose($hwnd);
    $types = json_decode($types);
    
    if (empty($id)) {
        return $types;
    } else {
        return $types->{$id};
    }
    
}