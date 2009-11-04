<?php

require_once('../../../wp-config.php');

if (0) {
    $cacheFile = "/tmp/debug.html";
    ob_start();
    echo "<pre>";
    
    
    var_dump($_REQUEST);
    $fp = fopen($cacheFile, "w");
    fwrite($fp, ob_get_contents());
    fclose($fp);
    
    ob_end_flush();
    
}


global $wp_registered_widgets, $wp_registered_widget_controls, $wp_registered_widget_updates;



switch ($_POST['action']) {
    case 'save_widget_options':
        //      echo "<pre>"; var_dump($wp_registered_widget_updates); exit;
        $id = $_POST['eletro_widgetToSave_id'];
        $widget_name = $wp_registered_widget_controls[$id]['callback'][0]->id_base;
        $update = $wp_registered_widget_updates[$widget_name];

        $callback =& $update['callback'];
        $params =& $update['params'];

        if (is_callable($callback)) 
            call_user_func_array($callback, $params);

        break;
		
    case 'add':
    	$name = $_POST['name'];
        $refresh = $_POST['refresh'] ? true : false;
        print_eletro_widgets($name, $refresh);
        break;
    case 'save':
        $theOptions = get_option('eletro_widgets');
        $options = array();
        $values = explode('X||X', $_POST['value']);

        if (is_array($values)) {
            $c = 0;
            foreach ($values as $col) {
                if ($col) {
                    $options[$c] = array();
                    $i = 0;
                    $items = explode('X|X', $col);
                    foreach ($items as $val) {
                        if ($val) {
                            $options[$c][$i] = $val;
                            $i++;
                        }
                    }
                }
                $c++;
            }
            
            $theOptions[$_POST['id']] = $options;
            update_option('eletro_widgets', $theOptions);
        }
}


?>
