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

    var $displayOnlyEletroWidgets = false;

    function EletroWidgets($cols = 2, $id = 0) {

        global $wp_registered_widgets;
        
        if ($id == 0)
            $this->addExternalFiles();
        
        echo "<div class='eletro_widgets_separator'></div>";
        
        // print add select box and button
        $selectBox = "<option value='' >".__('Select')."</option>";
        foreach($wp_registered_widgets as $name => $info) {
            if ($this->displayOnlyEletroWidgets && !isset($info['eletroWidget']))
                continue;

            $selectBox .= "<option value='$name'>{$info['name']}</option>";
        }
        
        echo "<div id='eletro_widgets_container_$id' class='eletro_widgets_container'>";
            echo "<form name='eletro_widgets_form_$id' method='post' id='eletro_widgets_form_$id' action='/wp284/wp-content/plugins/eletro-widgets/eletro-widgets-ajax.php'>";
                if (current_user_can('manage_eletro_widgets'))
                    echo "<div id='eletro_widgets_control'>" . __('Add new Widget: ', 'eletrow') . "<select id='eletro_widgets_add' name='eletro_widgets_add'>$selectBox</select><input type='button' value='".__('Add', 'eletrow')."' id='eletro_widgets_add_button'></div>";    

                echo "<input type='hidden' name='eletro_widgets_id' id='eletro_widgets_id' value='$id'>";
                echo "<input type='hidden' name='eletro_widgetToSave_id' id='eletro_widgetToSave_id' value=''>";
                echo "<input type='hidden' name='action' value='save_widget_options'>";
                
                $options = get_option('eletro_widgets');
                $colunas = $options[$id]; // load saved widgets
                for ($i=0; $i<$cols; $i++) {
                    echo "<div class='recebeDrag' id='eletro_widgets_col_$i'>";
                        if (is_array($colunas[$i])) {
                            foreach ($colunas[$i] as $w) {
                                print_eletro_widgets($w);
                            }
                        }
                    echo "</div>";
                }
            echo "</form>";
        echo "</div>";
    }
    
    #adds the js and css only when we need them
    function addExternalFiles() {
        // only prints the files if logged in
        if (current_user_can('manage_eletro_widgets')) {
            
            require_once(ABSPATH . '/wp-admin/includes/template.php');
            
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

function print_eletro_widgets($name, $refresh = false) {
    global $wp_registered_widgets, $wp_registered_widget_controls;

    require_once(ABSPATH . 'wp-admin/includes/template.php'); 

    if ($name) {
        $callback = $wp_registered_widgets[$name]['callback'];
        $niceName = __($wp_registered_widgets[$name]['name']);
        $callbackControl = $wp_registered_widget_controls[$name]['callback'];
        #var_dump($callbackControl);
        #var_dump(get_option($callbackControl[0]->option_name));
        if (current_user_can('manage_eletro_widgets')) {
			$params = array(array(
				'name' => 'Eletro Widgets',
				'id' => 'eletrowidgets',
				'before_widget' => '',
				'after_widget' => '',
				'before_title' => '<b style="display: none;">',
				'after_title' => '</b>',
				
			));
		} else {
			$params = array(array(
				'name' => 'Eletro Widgets',
				'id' => 'eletrowidgets',
				'before_widget' => '',
				'after_widget' => '',
				'before_title' => '<h2>',
				'after_title' => '</h2>',
			));
		}
    
		// is array indicates that the widgets uses the 2.8+ widget API
		if (is_array($callback)) 
			$params[] = $callback[0]->number;
		
        if (!$refresh) 
            echo "<div id='$name' class='itemDrag' alt='$niceName'>";
            
        echo '<div class="eletro_widgets_content">';
        
            if (current_user_can('manage_eletro_widgets')) 
                echo '<h2 class="itemDrag">' . $niceName . '</h2>';
            
            if ( is_callable($callback) ) 
                call_user_func_array($callback, $params);

        echo '</div>';
        
        $controlParam = '';
        // is array indicates that the widgets uses the 2.8+ widget API
        if (is_array($callbackControl))
            $controlParam = $callbackControl[0]->number;
        
        // Control
        if (current_user_can('manage_eletro_widgets')) {
            echo "<div class='eletro_widgets_control'>";
                if ( is_callable($callbackControl) ) {
                    call_user_func_array($callbackControl, $controlParam);                
                } else {
                     _e('There are no options for this widget.');
                }
            echo "</div>";
        }
                
        if (!$refresh) 
            echo "</div>";
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
