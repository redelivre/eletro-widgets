<?php

require_once('../../../wp-config.php');
/*
#if ($_POST['action'] == 'save_widget_options') {
    $cacheFile = "/tmp/debug.html";
    ob_start();
    echo "<pre>4";
#}
*/

global $wp_registered_widgets, $wp_registered_widget_controls, $wp_registered_widget_updates;

switch ($_POST['action']) {
    case 'save_widget_options':
        //      echo "<pre>"; var_dump($wp_registered_widget_updates); exit;
        
        /*
        $widget_name = $wp_registered_widget_controls[$id]['callback'][0]->id_base;
        
        $update = $wp_registered_widget_updates[$widget_name];
        
        #$update['params'] = array('number' => $wp_registered_widget_controls[$id]['callback'][0]->number);
        
        $callback =& $update['callback'];
        #$params =& $update['params'];
        $params = array('number' => $wp_registered_widget_controls[$id]['callback'][0]->number);
        
        if (is_callable($callback))  {
        	ob_start();
            call_user_func_array($callback, $params);
            ob_end_clean();
        }
        */
      
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
        
        #$id_base = $_POST['id_base'];
        #echo 'number:', $number;
        
        print_eletro_widgets($name, $number, $refresh);
        
        
        break;
    case 'save':
        #echo '<pre>';
    	$theOptions = get_option('eletro_widgets');
        $options = array();
        #print_r($_POST['value']); 
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
        
        #print_r($options); 
        #echo '</pre>';
        
        $theOptions['canvas'][$_POST['id']] = $options;
        update_option('eletro_widgets', $theOptions);
        
}

/*
#if ($_POST['action'] == 'save_widget_options') {
  
    
	
	$fp = fopen($cacheFile, "w");
    fwrite($fp, ob_get_contents());
    fclose($fp);
        
    ob_end_flush();
    
#}
*/
?>
