<?php

require_once('../../../wp-load.php');

global $wp_registered_widgets, $wp_registered_widget_controls, $wp_registered_widget_updates;

switch ($_POST['action']) {
    case 'save_widget_options':      
        $id = $_POST['widget-id'];
        $number = $_POST['widget-number'];
        $canvas_id = $_POST['canvas-id'];
        
        $id_base = preg_replace('/(-2)$/', '', $id);
        $option_name = 'widget-'.$id_base;
        
        if (is_array($wp_registered_widgets[$id]['callback'])) {
        // Saving Multi Widgets
            $newOptions = $_POST[$option_name][$number];
            $options = get_option('eletro_widgets');
            
            if (is_array( $options[$canvas_id]['widgets_options'][$id] )) {
                $oldOptions = $options[$canvas_id]['widgets_options'][$id][$number];
            } else {
                $oldOptions = array();
            }
            
            $className = get_class($wp_registered_widgets[$id]['callback'][0]);
            $newWidget = new $className;
            $newWidget->_set($number);
            $newOptions = $newWidget->update($newOptions, $oldOptions);
            
            if (is_array( $options[$canvas_id]['widgets_options'][$id] )) {
                $options[$canvas_id]['widgets_options'][$id][$number] = $newOptions;
            } else {
                $options[$canvas_id]['widgets_options'][$id] = array($number => $newOptions);
            }
            #echo '<pre>'; print_r($options); echo '</pre>';
            update_option('eletro_widgets', $options);
            
        } else {
        // Saving single Widgets
            $callbackControl = $wp_registered_widget_controls[$id]['callback'];
            if ( is_callable($callbackControl) ) 
                call_user_func_array($callbackControl, '');
        }
        
        break;
        
    case 'add':
    
    	$widget_id = $_POST['widget_id'];
        $refresh = $_POST['refresh'] ? true : false;
        $widget_number = $_POST['widget_number'];
        $canvas_id = $_POST['canvas_id'];
        
        if (!$refresh) {
            $options = get_option('eletro_widgets');
            if ( isset($options[$canvas_id]['widgets_options'][$widget_id]['last_number']) && is_int($options[$canvas_id]['widgets_options'][$widget_id]['last_number']) ) {
                $options[$canvas_id]['widgets_options'][$widget_id]['last_number'] ++;
            } else {
                $options[$canvas_id]['widgets_options'][$widget_id]['last_number'] = 1;
            }
            update_option('eletro_widgets', $options);
        }
        print_eletro_widgets($widget_id, $widget_number, $canvas_id, $refresh);
        break;
        
    case 'save':
    
    	$canvas_id = $_POST['id'];
        
        $theOptions = get_option('eletro_widgets');
        $options = array();
        $values = $_POST['value'];

        if (is_array($values)) {
            foreach ($values as $col => $ws) {
                $options[$col] = array();
                $items = explode(',', $ws);
                $i = 0;
                foreach ($items as $widget) {
                   	$w = explode('X|X', $widget);
                   	$options[$col][$i]['id'] = $w[0];
                   	$options[$col][$i]['number'] = $w[1];
                 	$i ++;
                }
            }
        }
        
        $theOptions[$canvas_id]['widgets'] = $options;
        update_option('eletro_widgets', $theOptions);   
        
        break;
        
    case 'apply' :
    
        $canvas_id = $_POST['canvas_id'];
        $adminOptions = get_option('eletro_widgets');
        $publicOptions = get_option('eletro_widgets_public');
        
        $publicOptions[$canvas_id] = $adminOptions[$canvas_id];
        
        update_option('eletro_widgets_public', $publicOptions);
        
        break;
        
    case 'restore' :
    
        $canvas_id = $_POST['canvas_id'];
        $adminOptions = get_option('eletro_widgets');
        $publicOptions = get_option('eletro_widgets_public');
        
        $adminOptions[$canvas_id] = $publicOptions[$canvas_id];
        
        update_option('eletro_widgets', $adminOptions);
        
        break;
    
}

?>
