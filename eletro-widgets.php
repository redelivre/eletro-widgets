<?php
/*
Plugin Name: Eletro Widgets
Plugin URI:
Description: Allows you to use the power and flexibility of the WordPress Widgets to set up a dynamic area anywhere in your site and manage multiple columns of widgets, dragging and dropping them around
Author: HackLab
Version: 1.0


*/

///// PLUGIN PATH ///////////

define('EW_ABSPATH', WP_CONTENT_DIR.'/plugins/'.plugin_basename( dirname(__FILE__)).'/' );
define('EW_URLPATH', WP_CONTENT_URL.'/plugins/'.plugin_basename( dirname(__FILE__)).'/' );

add_action('wp_print_scripts', 'eletrowidgets_print_scripts');
add_action('wp_print_styles', 'eletrowidgets_print_styles');

function eletrowidgets_print_scripts() {

    // Since we only need JS when admin is logged in, its ok to add it everywhere
    if (current_user_can('manage_eletro_widgets')) {
        #wp_enqueue_script('jquery');
        #wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('eletro-widgets', EW_URLPATH . 'eletro-widgets.js', array('jquery', 'jquery-ui-sortable'));
        $messages = array(
            'ajaxurl' => EW_URLPATH.'eletro-widgets-ajax.php',
            'confirmClear' => 'Are you sure you want to clear all widgets and its settings from this canvas?',
            'confirmApply' => 'Are you sure you want to apply this configuration to the public view of this canvas?',
            'confirmRestore' => 'Are you sure you want to copy the settings from the public view and loose any changes you have made?'
        );
        wp_localize_script('eletro-widgets', 'eletro', $messages);
    }

}

function eletrowidgets_print_styles() {

    if (current_user_can('manage_eletro_widgets')) {
        wp_enqueue_style('eletro-widgets-admin', EW_URLPATH.'eletro-widgets-admin.css');
    }

    $css = file_exists(TEMPLATEPATH . '/eletro-widgets.css') ? get_bloginfo('template_url') . '/eletro-widgets.css' : EW_URLPATH . 'eletro-widgets.css';
    wp_enqueue_style('eletro-widgets', $css);
}

////////////////////////////

class EletroWidgets {

    function EletroWidgets($cols = 2, $id = 0, $onlyEletro = false) {

        $this->id = $id;
        $this->cols = $cols;
        $this->onlyEletro = $onlyEletro;
        $this->outputCanvas();

    }

    /**
     * Output the eletro widgets canvas and its widgets
     *
     * @return void
     */
    function outputCanvas() {

        echo "<div class='eletro_widgets_separator'></div>";

        // The main DIV for this canvas
        echo "<div id='eletro_widgets_container_{$this->id}' class='eletro_widgets_container'>";

        #echo "<form name='eletro_widgets_form_{$this->id}' id='eletro_widgets_form_{$this->id}'";

        // If admin, print the control
        if (current_user_can('manage_eletro_widgets')) {
            echo "<div id='eletro_widgets_control'>";

            echo '<div class="eletro_list_widgets">';
            echo __('Add new Widget: ', 'eletrow');
            $this->list_widgets();
            echo '</div>';

            echo '<div class="eletro_widgets_buttons">';
            echo '<a class="eletroClearAll">' . __('Clear all widgets', 'eletroWidgets') . '</a>';
            echo '<a class="eletroApply">' . __('Apply to public', 'eletroWidgets') . '</a>';
            echo '<a class="eletroRestore">' . __('Restore from public', 'eletroWidgets') . '</a>';
            echo '</div>';

            echo '<div class="clearfix"></div>';
            echo '</div>';
        }

        // Put the canvas ID in a hidden field
        echo "<input type='hidden' name='eletro_widgets_id' id='eletro_widgets_id' value='{$this->id}'>";

        // Get saved widgets and print them
        if (current_user_can('manage_eletro_widgets')) {
            $options = get_option('eletro_widgets');
        } else {
            $options = get_option('eletro_widgets_public');
        }

        $colunas = $options[$this->id]['widgets'];

        for ($i = 0; $i < $this->cols; $i ++) {
            echo "<div class='eletro_widgets_col' id='eletro_widgets_col_$i'>";
            if (is_array($colunas[$i])) {
                foreach ($colunas[$i] as $w) {
                    print_eletro_widgets($w['id'], $w['number'], $this->id);
                }
            }
            echo "</div>";
        }

        // closes the form and the canvas div
        #echo "</form>";
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
	    $number = 1;
	    if (isset($options[$this->id]['widgets_options'][$id]['last_number'])) {
	    	$number = $options[$this->id]['widgets_options'][$id]['last_number'];
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

            if ($this->onlyEletro && ( !isset($widget['eletroWidget'] ) ) ) // Check for only eletro widgets option
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
}

function print_eletro_widgets($id, $number, $canvas_id, $refresh = false) {
    global $wp_registered_widgets, $wp_registered_widget_controls;

    if ($id) {
        $widgetName = $wp_registered_widgets[$id]['name'];

        if (is_array($wp_registered_widgets[$id]['callback'])) {
			// Multi Widget
			$className = get_class($wp_registered_widgets[$id]['callback'][0]);
			$newWidget = new $className;
			$newWidget->_set($number);

			if (current_user_can('manage_eletro_widgets')) {
                $options = get_option('eletro_widgets');
            } else {
                $options = get_option('eletro_widgets_public');
            }

			if (is_array($options[$canvas_id]['widgets_options'][$id]) && array_key_exists($number, $options[$canvas_id]['widgets_options'][$id])) {
				$options = $options[$canvas_id]['widgets_options'][$id][$number];
			}
			$widgetType = 'multi';
			$widgetDivID = $newWidget->id;

		} else {
			// Single Widget
			$callback = $wp_registered_widgets[$id]['callback'];
			$callbackControl = $wp_registered_widget_controls[$id]['callback'];
			$widgetType = 'single';
			$widgetDivID = $id;
		}

        if (current_user_can('manage_eletro_widgets')) {
            $params = array(
                'name' => $widgetName,
                'id' => $id,
                'before_widget' => '',
                'after_widget' => '',
                'before_title' => '<h2>',
                'after_title' => '</h2>',
            );
        } else {
            $params = array(
                'name' => $widgetName,
                'id' => $id,
                'before_widget' => '',
                'after_widget' => '',
                'before_title' => '<h2>',
                'after_title' => '</h2>',
            );
        }

        // This is weird, but is needed
        if ($widgetType == 'single')
            $params = array($params);

        if (!$refresh) {
            echo "<div id='{$widgetDivID}' class='itemDrag' alt='{$widgetName}'>";
        }

        echo "<input type='hidden' name='widget-id' value='$id'>";
        echo "<input type='hidden' name='widget-number' value='$number'>";
        echo "<input type='hidden' name='widget-type' value='$widgetType'>";
        echo "<input type='hidden' name='canvas-id' value='$canvas_id'>";
        echo "<input type='hidden' name='action' value='save_widget_options'>";

        echo '<div class="eletro_widgets_content">';

        if (current_user_can('manage_eletro_widgets'))
            echo '<span class="itemDrag">' . $widgetName . '</span>';

        // Print Widget
        if ($widgetType == 'multi') {
			$newWidget->widget($params, $options);
		} else {
			if ( is_callable($callback) )
                call_user_func_array($callback, $params);
		}

        echo '</div>';

        // Control
        if (current_user_can('manage_eletro_widgets')) {

            // load this files that have some functions (such as checked()) used by some widget controls
            require_once(ABSPATH . 'wp-admin/includes/template.php');

            echo "<div class='eletro_widgets_control'>";

            if ($widgetType == 'multi') {
            	$newWidget->form($options);
            } else {
				if ( is_callable($callbackControl) ) {
                    call_user_func_array($callbackControl, '');
                } else {
                     _e('There are no options for this widget.');
                }
			}

            echo '<input class="save" name="save" type="button" value="Save">';
            echo "</div>";
        }

        if (!$refresh) {
            echo "</div>";
        }
    }
}

function defineAsEletroWidget($widgetId) {
    global $wp_registered_widgets;
    $wp_registered_widgets[$widgetId]['eletroWidget'] = true;
}

function eletroWidgetsInstall() {
    $role = get_role('administrator');
    $role->add_cap('manage_eletro_widgets');
    $options = array();
    update_option('eletro_widgets', $options);
    update_option('eletro_widgets_public', $options);
}

function eletroWidgetsUninstall() {
    $role = get_role('administrator');
    $role->remove_cap('manage_eletro_widgets');
    remove_option('eletro_widgets');
    remove_option('eletro_widgets_public');
}

register_activation_hook( __FILE__, 'eletroWidgetsInstall' );
register_deactivation_hook( __FILE__, 'eletroWidgetsInstall' );



?>
