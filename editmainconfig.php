<?php

free_map_check_map_exists();

$options = get_site_option('freehtml5map_options');
$map_id  = (isset($_REQUEST['map_id'])) ? intval($_REQUEST['map_id']) : array_shift(array_keys($options)) ;

if((isset($_POST['act_type']) && $_POST['act_type'] == 'free_map_plugin_main_save') && current_user_can('manage_options')) {
    
    foreach($_REQUEST['options'] as $key => $value) { $_REQUEST['options'][$key] = sanitize_text_field($value); }
    
    $options[$map_id] = wp_parse_args($_REQUEST['options'],$options[$map_id]);
    update_site_option('freehtml5map_options', $options);
    
}


echo "<h2>" . __( 'HTML5 Map Config', 'free-html5-map' ) . "</h2>";
?>
<script xmlns="http://www.w3.org/1999/html">
    jQuery(function($){
        $('.tipsy-q').tipsy({gravity: 'w'}).css('cursor', 'default');

        $('.color~.colorpicker').each(function(){
            $(this).farbtastic($(this).prev().prev());
            $(this).hide();
            $(this).prev().prev().bind('focus', function(){
                $(this).next().next().fadeIn();
            });
            $(this).prev().prev().bind('blur', function(){
                $(this).next().next().fadeOut();
            });
        });
        
        $('select[name=map_id]').change(function() {
            location.href='admin.php?page=free-map-plugin-options&map_id='+$(this).val();
        });
        
        $('input[name*=isResponsive]').change(function() {
        
            var resp = $('input[name*=isResponsive]:eq(0)').attr('checked')=='checked' ? false : true;
            $('input[name*=maxWidth]').attr('disabled',!resp);
            $('input[name*=mapWidth],input[name*=mapHeight]').attr('disabled',resp);
            
        });
        $('input[name*=isResponsive]').trigger('change');
        
        $('.radio-block h4').click(function() {
            $(this).prev('input').trigger('click');    
        });
        
        $('input[name*=df_type]').change(function() {
        
            var local = $('input[name*=df_type]:eq(0)').attr('checked')=='checked' ? false : true;
            $('input[name*=data_file]:eq(0)').attr('disabled',!local);
            $('input[name*=data_file]:eq(1)').attr('disabled',local);
            
        });
        $('input[name*=df_type]').trigger('change');
        
        
        
    });
</script>

<div class="wrap free-html5-map main full">
<div class="left-block">
    
<form method="POST">
    
    <span class="title">Map: </span>
    <select name="map_id" style="width: 185px;">
        <?php foreach($options as $id => $map_data) { ?>
            <option value="<?php echo $id; ?>" <?php echo ($id==$map_id)?'selected':'';?>><?php echo $map_data['name']; ?></option>
        <?php } ?>
    </select>
    <span class="tipsy-q" original-title="The map">[?]</span><br />
    
    <fieldset style="margin-top: 2px;">
        <legend>Resource</legend>
        
        <span class="title" style="float: left; height: 130px; width: 15%;">Path to map data file: </span>
        
        <div class="radio-block">
            <input type="radio" name="options[df_type]" value="0" <?php echo ($options[$map_id]['df_type']==0) ? 'checked' : ''; ?> />
            <h4>data file on html5maps.com</h4><span class="tipsy-q" original-title="Path to map data file">[?]</span>
            <div class="clear"></div>
            <input type="text" value="<?php echo $options[$map_id]['defaultDataFile']; ?>" readonly />
        </div>
        
        <div class="radio-block">
            <input type="radio" name="options[df_type]" value="1" <?php echo ($options[$map_id]['df_type']==1) ? 'checked' : ''; ?> />
            <h4>data file on your server</h4><span class="tipsy-q" original-title="Path to map data file">[?]</span>
            <div class="clear"></div>
            <input type="text" name="options[data_file]" value="<?php echo $options[$map_id]['data_file']; ?>" />
        </div>
        
    </fieldset>
    
    <p>Specify general settings of the map. To choose a color, click a color box, select the desired color in the color selection dialog and click anywhere outside the dialog to apply the chosen color.</p>
    
    <fieldset>
        <legend>Map Settings</legend>
        
        <span class="title">Map name: </span><input type="text" name="options[name]" value="<?php echo $options[$map_id]['name']; ?>" />
        <span class="tipsy-q" original-title="Name of the map">[?]</span><div class="colorpicker"></div>
    
        
        <span class="title">Borders Color: </span><input class="color" type="text" name="options[borderColor]" value="<?php echo $options[$map_id]['borderColor']; ?>" style="background-color: #<?php echo $options[$map_id]['borderColor']; ?>" />
        <span class="tipsy-q" original-title="The color of borders on the map">[?]</span><div class="colorpicker"></div>
        <div class="clear"></div>
        
        <span class="title">Layout type: </span>
        <label>Not Responsive: <input type="radio" name="options[isResponsive]" value=0 <?php echo !$options[$map_id]['isResponsive']?'checked':''?> /></label>&nbsp;&nbsp;&nbsp;&nbsp;
        <label>Responsive: <input type="radio" name="options[isResponsive]" value=1 <?php echo $options[$map_id]['isResponsive']?'checked':''?> /></label>
        <span class="tipsy-q" original-title="Type of the layout">[?]</span>
        <div class="clear" style="margin-bottom: 10px"></div>
        
        <span class="title">Map width: </span><input class="span2" type="text" name="options[mapWidth]" value="<?php echo $options[$map_id]['mapWidth']; ?>" />
        <span class="tipsy-q" original-title="The width of the map">[?]</span>
        <div class="clear"></div>
        
        <span class="title">Map height: </span><input class="span2" type="text" name="options[mapHeight]" value="<?php echo $options[$map_id]['mapHeight']; ?>" />
        <span class="tipsy-q" original-title="The height of the map">[?]</span>
        <div class="clear"></div>
        
        <span class="title">Max width: </span><input class="span2" type="text" name="options[maxWidth]" value="<?php echo $options[$map_id]['maxWidth']; ?>" disabled />
        <span class="tipsy-q" original-title="The max width of the map">[?]</span>
        <div class="clear"></div>
        
    </fieldset>
    
    <fieldset>
        <legend>Content Info</legend>    
        <span class="title">Additional Info Area: </span>
        <label>At right: <input type="radio" name="options[statesInfoArea]" value="right" <?php echo $options[$map_id]['statesInfoArea'] == 'right'?'checked':''?> /></label>&nbsp;&nbsp;&nbsp;&nbsp;
        <label>At bottom: <input type="radio" name="options[statesInfoArea]" value="bottom" <?php echo $options[$map_id]['statesInfoArea'] == 'bottom'?'checked':''?> /></label>
        <span class="tipsy-q" original-title="Where to place an additional information about state">[?]</span><br />
    </fieldset>
    
    <input type="hidden" name="act_type" value="free_map_plugin_main_save" />
    <p class="submit"><input type="submit" value="Save Changes" class="button-primary" id="submit" name="submit"></p> 
    
</form>
</div>

<div class="banner">
            <a href="http://www.fla-shop.com/wordpressmaps.php?utm_source=html5-maps-plugin&utm_medium=dashboard&utm_campaign=banner" target="_blank"><img src="http://cdn.html5maps.com/html5maps_banner_160x600.png" border="0" width="160" height="600"></a>
</div>

<div class="clear"></div>
</div>