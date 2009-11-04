<?php
    $cacheFile = "../../uploads/debug.html";
    ob_start();
    
    
var_dump($_REQUEST);

require_once('../../../wp-config.php');





echo 'abv';
	

global $wp_registered_widgets, $wp_registered_widget_controls;



switch ($_POST['action']) {
	case 'save_widget_options':
		
		
		print_r($_POST); 
        $name = $_POST['eletro_widgetToSave_id'];
		$callback = $wp_registered_widget_controls[$name]['callback'][0];
		
		foreach ($_POST as $name => $value) {
			if (preg_match('/^widget-' . $callback->base_id . '/', $name)) {
				
			} 
		}
		
        $object->update($x, $object);
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


	$fp = fopen($cacheFile, "w");
	fwrite($fp, ob_get_contents());
	fclose($fp);
	
    ob_end_flush();
  

?>
