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
            'name'      => __( 'Name', 'html5-maps' ),
            'type'      => __( 'Map', 'html5-maps' ),
            'shortcode' => __( 'ShortCode', 'html5-maps' ),
            'edit'      => __( 'Edit', 'html5-maps' ),
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
                                'edit'      => '<a href="admin.php?page=free-map-plugin-options&map_id='.$map_id.'">'.__( 'Map settings', 'html5-maps' ).'</a><br />
                                                <a href="admin.php?page=free-map-plugin-states&map_id='.$map_id.'">'.__( 'Map detailed settings', 'html5-maps' ).'</a><br />
                                                <a href="admin.php?page=free-map-plugin-view&map_id='.$map_id.'">'.__( 'Preview', 'html5-maps' ).'</a><br /><br />
                                                <a href="admin.php?page=free-map-plugin-maps&action=delete&map_id='.$map_id.'" class="delete" style="color:#FF0000">'.__( 'Delete', 'html5-maps' ).'</a><br />
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
        <div class="error"><p><?php _e( 'You need to create your first map. Select a map from the drop-down list below and click "Add new map"', 'html5-maps' ); ?></p></div>
    <?php } ?>

    <div class="wrap free-html5-map full">
        <div id="icon-users" class="icon32"></div>
        <h2><?php echo __( 'HTML5 Maps', 'html5-maps' ); ?></h2>

        <div class="left-block">
            <?php $listtable->display(); ?>

            <form name="action_form" action="" method="POST" enctype="multipart/form-data" class="html5-map full">
                <input type="hidden" name="action" value="new" />
                <input type="hidden" name="maps" value="" />

                <fieldset>
                    <legend><?php _e( 'Add new map', 'html5-maps' ) ?></legend>
                    <span><?php _e( 'New map name:', 'html5-maps' ) ?></span>
                    <input type="text" name="name" value="<?php _e( 'New map', 'html5-maps' ) ?>" />

                    <?php

                       $types = free_map_get_map_types();

                    ?>

                    <select name="map_type" class="chosen-select">
                        <option value=""><?php _e( 'Please select the map', 'html5-maps' ) ?></option>

                        <?php

                            $last_group = ''; $n=0;
                            foreach($types as $id => $type) {

                                $n++;

                                if ($type->group!=$last_group) {
                                    if ($n>1) { echo '</optgroup>'; }
                                    echo '<optgroup label="'.$type->group.'">';
                                }

                                $last_group = $type->group;

                                $type->name_html        = str_replace('+','%20',urlencode($type->name_html));
                                $type->onselect_content = str_replace('+','%20',urlencode($type->onselect_content));

                        ?>
                            <option value="<?php echo $id; ?>" data-name-html="<?php echo $type->name_html; ?>" data-onselect-content="<?php echo $type->onselect_content; ?>" data-license="<?php echo $type->license; ?>"><?php echo $type->name; ?></option>

                        <?php } ?>

                    </select>

                    <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Add new map', 'html5-maps' ); ?>" />

                    <div class="onselect_content"></div>

                </fieldset>

                <fieldset>
                    <legend><?php _e( 'Export/import Map Settings', 'html5-maps' ) ?></legend>   
                    <p><?php _e( 'To export please select a checkbox of one or more maps, and press Export button', 'html5-maps' ); ?></p>
                    <input type="button" class="button button-secondary export" value="<?php esc_attr_e( 'Export', 'html5-maps' ); ?>" />
                    <input type="button" class="button button-secondary import" value="<?php esc_attr_e( 'Import', 'html5-maps' ); ?>" disabled />

                    <p>
                        <?php _e( 'The Import function is only available in <a href="http://www.fla-shop.com/wordpressmaps.php">Premium plugins</a> ', 'html5-maps' ) ?>
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
        jQuery(document).ready(function($) {

            $('a.delete').click(function() {
                if (confirm('<?php echo __( 'Remove the map?\nAttention! All settings for the map will be deleted permanently!', 'html5-maps' ); ?>')) {
                    return true;
                } else {
                    return false;
                }
            });

            $('.maps_toggle').click(function() {
                $('.map_checkbox,.maps_toggle').not($(this)).each(function() {
                    $(this).prop('checked', !($(this).is(':checked')));
                });
            });

            $('input.export').click(function() {
                $('input[name=action]').val('free_map_export');

                var maps = '';
                $('.map_checkbox:checked').each(function() {
                    if (maps!='') maps+=',';
                    maps+=$(this).val();
                });

                $('input[name=maps]').val(maps);

                $('form[name=action_form]').submit();
                return false; 
            });


            var onMapSelect = function(e) {

                var content = $(this).find('option:selected').attr('data-onselect-content');
                content = content ? decodeURIComponent(content) : '';
                var license = $(this).find('option:selected').attr('data-license');

                if (license=="free") {
                    $('.button-primary').attr("disabled",false);
                } else {
                    $('.button-primary').attr("disabled",true);
                }

                $('.onselect_content').html(content);

            };
            $('.chosen-select').chosen().change(onMapSelect);
            onMapSelect();


        });
    </script>

<?php

?>