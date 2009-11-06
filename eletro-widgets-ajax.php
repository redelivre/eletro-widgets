<?php

require_once('../../../wp-config.php');

global $wp_registered_widgets, $wp_registered_widget_controls, $wp_registered_widget_updates;

switch ($_POST['action']) {
    case 'save_widget_options':      
        $id = $_POST['widget-id'];
        $number = $_POST['widget-number'];
        
        $id_base = preg_replace('/(-2)$/', '', $id);
        $option_name = 'widget-'.$id_base;
        
        $newOptions = $_POST[$option_name][$number];
        
        $options = get_option('eletro_widgets');
        
        if (is_array( $options['widgets'][$id] )) {
            $oldOptions = $options['widgets'][$id][$number];
        } else {
            $oldOptions = array();
        }
        
        $className = get_class($wp_registered_widgets[$id]['callback'][0]);
        $newWidget = new $className;
        $newWidget->_set($number);
        $newOptions = $newWidget->update($newOptions, $oldOptions);
        
        if (is_array( $options['widgets'][$id] )) {
        	$options['widgets'][$id][$number] = $newOptions;
        } else {
        	$options['widgets'][$id] = array($number => $newOptions);
        }
        
        update_option('eletro_widgets', $options);
        break;
    case 'add':
    	$name = $_POST['name'];
        $refresh = $_POST['refresh'] ? true : false;
        $number = $_POST['number'];
        
        print_eletro_widgets($name, $number, $refresh);
        break;
    case 'save':
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
        
        $theOptions['canvas'][$_POST['id']] = $options;
        update_option('eletro_widgets', $theOptions);        
}

?>