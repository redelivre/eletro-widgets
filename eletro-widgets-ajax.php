<?php

require_once('../../../wp-config.php');

global $wp_registered_widgets;

switch ($_POST['action']) {

    case 'add':
        
        $refresh = $_POST['refresh'] ? true : false;
        print_eletro_widgets($_POST['name'], $refresh);
        
    case 'save' :

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
