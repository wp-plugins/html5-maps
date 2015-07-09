<?php

$update   = false;
$options  = get_site_option('freehtml5map_options');

if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'new':
            $type      = intval($_REQUEST['map_type']);
            $name      = sanitize_text_field($_REQUEST['name']);
            $defaults  = free_map_plugin_map_defaults($name,$type);
            
            if (is_array($defaults)) {
                $options[] = $defaults;
                $update    = true;
            }
            
            break;
        case 'delete':
            unset($options[intval($_REQUEST['map_id'])]);
            $update = true;
            break;
    }
}

if ($update) update_site_option('freehtml5map_options',$options);

class Map_List_Table extends WP_List_Table {

    public function prepare_items()
    {
        $columns  = $this->get_columns();
        $hidden   = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data     = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }
    
    public function get_columns()
    {
        $columns = array(
            'checkbox'  => '<input type="checkbox" class="maps_toggle" autocomplete="off" />',
            'name'      => __( 'Name', 'free-html5-map' ),
            'type'      => __( 'Map', 'free-html5-map' ),
            'shortcode' => __( 'ShortCode', 'free-html5-map' ),
            'edit'      => __( 'Edit', 'free-html5-map' ),
        );

        return $columns;
    }
    
    public function get_hidden_columns()
    {
        return array();
    }
    
    public function get_sortable_columns()
    {
        return array('name' => array('name', false));
    }
    
    private function table_data()
    {
        
        $data      = array();
        $options   = get_site_option('freehtml5map_options');
        
        if (is_array($options) && count($options)) {
            foreach ($options as $map_id => $map_data) {
                
                $data[] = array(
                                'id'        => $map_id,
                                'name'      => $map_data['name'],
                                'type'      => $map_data['type'],
                                'shortcode' => '[freehtml5map id="'.$map_id.'"]',
                                'edit'      => '<a href="admin.php?page=free-map-plugin-options&map_id='.$map_id.'">'.__( 'Map settings', 'free-html5-map' ).'</a><br />
                                                <a href="admin.php?page=free-map-plugin-states&map_id='.$map_id.'">'.__( 'Map detailed settings', 'free-html5-map' ).'</a><br />
                                                <a href="admin.php?page=free-map-plugin-view&map_id='.$map_id.'">'.__( 'Preview', 'free-html5-map' ).'</a><br /><br />
                                                <a href="admin.php?page=free-map-plugin-maps&action=delete&map_id='.$map_id.'" class="delete" style="color:#FF0000">'.__( 'Delete', 'free-html5-map' ).'</a><br />
                                                ',
                                );
            }
        }
        
        return $data;
    }
    
    public function column_default( $item, $column_name )
    {
        
        switch( $column_name ) {
            case 'checkbox':
                echo '&nbsp;<input type="checkbox" value="'.$item['id'].'" class="map_checkbox" autocomplete="off" />';
                break;
            case 'name':
            case 'type':
            case 'shortcode':
            case 'edit':
                return $item[ $column_name ];
        }
    }
    
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'name';
        $order   = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }

        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
    
}


$listtable = new Map_List_Table();
$listtable->prepare_items();

?>

    <?php if (isset($_REQUEST['msg']) && !isset($_REQUEST['action'])) { ?>
        <div class="error"><p><?php _e( 'You need to create your first map. Select a map from the drop-down list below and click "Add new map"', 'free-html5-map' ); ?></p></div>
    <?php } ?>
    
    <div class="wrap free-html5-map full">
        <div id="icon-users" class="icon32"></div>
        <h2><?php echo __( 'HTML5 Maps', 'free-html5-map' ); ?></h2>
        
        <div class="left-block">
            <?php $listtable->display(); ?>
            
            <form name="action_form" action="" method="POST" enctype="multipart/form-data" class="html5-map full">
                <input type="hidden" name="action" value="new" />
                <input type="hidden" name="maps" value="" />
                
                <fieldset>
                    <legend>Add new map</legend>
                    <span>New map name:</span>
                    <input type="text" name="name" value="New map" />
                    
                    <?php
                    
                       $types = free_map_get_map_types();
                        
                    ?>
                    
                    <select name="map_type">
                        <option value="">Please select the map</option>
                        <?php foreach($types as $id => $type) { ?>
                            <option value="<?php echo $id; ?>"><?php echo $type->name; ?></option>
                        <?php } ?>
                    </select>
                    
                    <input type="submit" class="button button-primary" value="<?php echo __( 'Add new map', 'free-html5-map' ); ?>" />
                </fieldset>
                
                <fieldset>
                    <legend>Export/import</legend>   
                    <p><?php echo __( 'To export please select a checkbox of one or more maps, and press Export button', 'free-html5-map' ); ?></p>
                    <input type="button" class="button button-secondary export" value="<?php echo __( 'Export', 'free-html5-map' ); ?>" />
                    <input type="button" class="button button-secondary import" value="<?php echo __( 'Import', 'free-html5-map' ); ?>" disabled />
                
                    <p>
                        The Import function is only available in <a href="http://www.fla-shop.com/wordpressmaps.php">Premium plugins</a> 
                    </p>
                
                </fieldset>
                
            </form>
            
        </div>
        
        <div class="banner">
            <a href="http://www.fla-shop.com/wordpressmaps.php?utm_source=html5-maps-plugin&utm_medium=dashboard&utm_campaign=banner" target="_blank"><img src="http://cdn.html5maps.com/html5maps_banner_160x600.png" border="0" width="160" height="600"></a>
        </div>
        
        <div class="clear"></div>
        
    </div>
    
    
    <script type="text/javascript">
        jQuery(document).ready(function() {
            
            jQuery('a.delete').click(function() {
                if (confirm('<?php echo __( 'Remove the map?\nAttention! All settings for the map will be deleted permanently!', 'free-html5-map' ); ?>')) {
                    return true;
                } else {
                    return false;
                }
            });
            
            jQuery('.maps_toggle').click(function() {
                jQuery('.map_checkbox,.maps_toggle').not(jQuery(this)).each(function() {
                    jQuery(this).prop('checked', !(jQuery(this).is(':checked')));
                });
            });
            
            jQuery('input.export').click(function() {
                jQuery('input[name=action]').val('free_map_export');
                
                var maps = '';
                jQuery('.map_checkbox:checked').each(function() {
                    if (maps!='') maps+=',';
                    maps+=jQuery(this).val();
                });
                
                jQuery('input[name=maps]').val(maps);
                
                jQuery('form[name=action_form]').submit();
                return false; 
            });
            

        });
    </script>
    
<?php

?>