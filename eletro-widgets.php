<?php
/*
Plugin Name: Eletro Widgets
Plugin URI: 
Description: Allows you to use the power and flexibility of the WordPress Widgets to set up a dynamic area anywhere in your site and manage multiple columns of widgets, dragging and dropping them around
Author: HackLab
Version: 0.2 beta

development version
*/

///// PLUGIN PATH ///////////

$myabspath = str_replace("\\","/",ABSPATH);  
define('WINABSPATH', $myabspath);
// Pre-2.6 compatibility
if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', WINABSPATH . 'wp-content' );
	
define('EW_FOLDER', plugin_basename( dirname(__FILE__)) );
define('EW_ABSPATH', WP_CONTENT_DIR.'/plugins/'.plugin_basename( dirname(__FILE__)).'/' );
define('EW_URLPATH', WP_CONTENT_URL.'/plugins/'.plugin_basename( dirname(__FILE__)).'/' );

////////////////////////////

class EletroWidgets {

    /**
     * Output the eletro widgets canvas and its widgets
     * 
     * @param int $cols the number of columns for the eletro widget instance
     * @param int $id a unique identifier for the eletro widget instance
     * @return void
     */
    function EletroWidgets($cols = 2, $id = 0) {
        global $wp_registered_widgets;
        
        if ($id == 0)
            $this->addExternalFiles();
        
        echo "<div class='eletro_widgets_separator'></div>";
        
        // print add select box and button
        $selectBox = "<option value='' >".__('Select')."</option>";
        $done = array();
        
        echo "<div id='eletro_widgets_container_$id' class='eletro_widgets_container'>";
        echo "<form name='eletro_widgets_form_$id' method='post' id='eletro_widgets_form_$id' action='/wp284/wp-content/plugins/eletro-widgets/eletro-widgets-ajax.php'>";
        if (current_user_can('manage_eletro_widgets'))
            echo "<div id='eletro_widgets_control'>" . __('Add new Widget: ', 'eletrow');
                    
        $this->list_widgets();
            
        echo "</div>";
        #echo "<input type='button' value='".__('Add', 'eletrow')."' id='eletro_widgets_add_button'></div>";    

        echo "<input type='hidden' name='eletro_widgets_id' id='eletro_widgets_id' value='$id'>";
        echo "<input type='hidden' name='eletro_widgetToSave_id' value=''>";
        echo "<input type='hidden' name='eletro_widgetToSave_number' value=''>";
        echo "<input type='hidden' name='action' value='save_widget_options'>";
        
        $options = get_option('eletro_widgets');
        $options = $options['canvas'];
        $colunas = $options[$id]; // load saved widgets

        for ($i=0; $i<$cols; $i++) {
            echo "<div class='recebeDrag' id='eletro_widgets_col_$i'>";
            if (is_array($colunas[$i])) {
                foreach ($colunas[$i] as $w) {
                    print_eletro_widgets($w['id'], $w['number']);
                }
            }
            echo "</div>";
        }
        echo "</form>";
        echo "</div>";
    }
    
    /**
     * Returns the next avaliable number to be used to create a new widget instance.
     * 
     * @param string $id unique string that identifies the widget type (archive, calendar etc) 
     * @return int $number the next number avaliable to this type of widget
     */
	function next_widget_id_number($id) {
	    $options = get_option('eletro_widgets');
	    $number = 1;;
        
	    if (is_array($options['widgets'][$id])) {	    	
	    	$number = max(array_keys($options['widgets'][$id]));
	    	$number++;
	    }
	    
	    return $number;
	}
	
	/**
	 * Output a select box with the list of avaliable widgets types
	 * 
	 * Based on the function list_widgets() locate on the file wp-admin/includes/widgets.php
	 * 
	 * @return void
	 */
	function list_widgets() {
	    global $wp_registered_widgets, $wp_registered_widget_controls;
	
	    $sort = $wp_registered_widgets;
	    usort( $sort, create_function( '$a, $b', 'return strnatcasecmp( $a["name"], $b["name"] );' ) );
	    $done = array();

	    $selectBox = "<option value='' >".__('Select')."</option>";
	    $addControls = '';
        
	    foreach ($sort as $widget) {
	        if (in_array($widget['callback'], $done, true)) // We already showed this multi-widget
	            continue;
	
	        $sidebar = is_active_widget($widget['callback'], $widget['id'], false, false);
	        $done[] = $widget['callback'];
	
	        if (!isset($widget['params'][0]))
	            $widget['params'][0] = array();
	
	        $args = array('widget_name' => $widget['name'], '_display' => 'template');

	        if (isset($wp_registered_widget_controls[$widget['id']]['id_base']) && isset($widget['params'][0]['number'])) {
	            $id_base = $wp_registered_widget_controls[$widget['id']]['id_base'];
	            $args['_multi_num'] = $this->next_widget_id_number($id_base);
	            $args['_add'] = 'multi';
	            $args['_base_id'] = $id_base;
	            $args['widget_id'] = $id_base . '-2';
	            $args['_multi_num'] = $this->next_widget_id_number($args['widget_id']);
	        } else {
	            $args['_add'] = 'single';
	            if ($sidebar)
	                $args['_hide'] = '1';
	            $args['_base_id'] = $widget['id'];
	            $args['widget_id'] = $widget['id'];
	        }
	        
	        $selectBox .= "<option value='{$args['_base_id']}' >{$widget['name']}</option>";
	        
            $addControls .= $this->get_widget_on_list($args);
	    }
	    
	    echo "<select id='eletro_widgets_add' name='eletro_widgets_add'>$selectBox</select>";
	    echo $addControls;
	}
	
	function get_widget_on_list($args) {		
		$r .= "<div class='widget_add_control' id='widget_add_control_{$args['_base_id']}'>";
		$r .= "<input type='hidden' class='id_base' name='id_base' value='{$args['_base_id']}'>";
		$r .= "<input type='hidden' class='multi_number' name='multi_number' value='{$args['_multi_num']}'>";
		$r .= "<input type='hidden' class='widget-id' name='widget-id' value='{$args['widget_id']}'>";
		$r .= "<input type='hidden' class='add' name='add' value='{$args['_add']}'>";
		
		$r .= "<input type='button' value='".__('Add', 'eletrow')."' class='eletro_widgets_add_button'>";
		$r .= '</div>';
		
		return $r;
	}
    
    #adds the js and css only when we need them
    function addExternalFiles() {
        // only prints the files if logged in
        if (current_user_can('manage_eletro_widgets')) {
            echo '<script type="text/javascript" src="' . EW_URLPATH . 'eletro-widgets.js"></script>';
            echo '<script type="text/javascript" src="' . EW_URLPATH . 'jquery-ui-sortable-1.5.3.js"></script>';
            echo '<script type="text/javascript" src="' . EW_URLPATH . 'jquery.form.js"></script>';
            echo '<script>var eletro_widgets_ajax_url = "'.EW_URLPATH.'eletro-widgets-ajax.php";</script>';         
            echo '<link rel="stylesheet" href="'.EW_URLPATH.'eletro-widgets-admin.css" type="text/css" media="screen" />';
        }

        #if there is a eletro-widgets.css file in the template folder, use this
        $eletroCSS = file_exists(TEMPLATEPATH . '/eletro-widgets.css') ? get_bloginfo('template_url') . '/eletro-widgets.css' : EW_URLPATH . 'eletro-widgets.css';
        echo '<link rel="stylesheet" href="'.$eletroCSS.'" type="text/css" media="screen" />';        
    }
}

function print_eletro_widgets($id, $number, $refresh = false) {
    global $wp_registered_widgets, $wp_registered_widget_controls;

    require_once(ABSPATH . 'wp-admin/includes/template.php'); 

    if ($id) {
        $className = get_class($wp_registered_widgets[$id]['callback'][0]);       
        $newWidget = new $className;
        $newWidget->_set($number);
        
        if (current_user_can('manage_eletro_widgets')) {
            $params = array(array(
                'name' => $newWidget->name,
                'id' => $newWidget->id,
                'before_widget' => '',
                'after_widget' => '',
                'before_title' => '<b style="display: none;">',
                'after_title' => '</b>',
                
            ));
        } else {
            $params = array(array(
                'name' => $newWidget->name,
                'id' => $newWidget->id,
                'before_widget' => '',
                'after_widget' => '',
                'before_title' => '<h2>',
                'after_title' => '</h2>',
            ));
        }
        
        $options = get_option('eletro_widgets');
        
        if (is_array($options['widgets'][$id]) && array_key_exists($number, $options['widgets'][$id])) {
            $options = $options['widgets'][$id][$number];
        }

        if (!$refresh) { 
            echo "<div id='{$newWidget->id}' class='itemDrag' alt='{$newWidget->name}'>";
        }
            
        echo "<input type='hidden' name='widget-id' value='$id'>";
        echo "<input type='hidden' name='widget-number' value='$number'>";
        echo "<input type='hidden' name='action' value='save_widget_options'>";
    
        echo '<div class="eletro_widgets_content">';
    
        if (current_user_can('manage_eletro_widgets')) 
            echo '<h2 class="itemDrag">' . $newWidget->name . '</h2>';
        
        $newWidget->widget($params, $options);

        echo '</div>';
            
        // Control
        if (current_user_can('manage_eletro_widgets')) {
            echo "<div class='eletro_widgets_control'>";
            $newWidget->form($options);
            echo '<input class="save" name="save" type="button" value="Save">';
            echo "</div>";
        }
                
        if (!$refresh) { 
            echo "</div>";
        }        
    }
}
    
function defineAsEletroWidget($widgetName) {
    global $wp_registered_widgets;
    $widgetId = sanitize_title($widgetName);
    $wp_registered_widgets[$widgetId]['eletroWidget'] = true;   
}

function eletroWidgetsInstall() {
    $role = get_role('administrator');
    $role->add_cap('manage_eletro_widgets');
    $options = array();
    update_option('eletro_widgets', $options);
}

function eletroWidgetsUninstall() {
    $role = get_role('administrator');
    $role->remove_cap('manage_eletro_widgets');
    remove_option('eletro_widgets');
}

register_activation_hook( __FILE__, 'eletroWidgetsInstall' );
register_deactivation_hook( __FILE__, 'eletroWidgetsInstall' );

?>
