<?php

// Debug helpers
class SH_Debug {
    
    // Dump the contents of a variable
    public static function dump($var, $title=null) {
        
        // Dump the variable
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
        
        // Output the variable
        echo('<pre>');
        if (!empty($title)) {
            echo("<b>{$title}</b>" . PHP_EOL);
        }
        echo(htmlentities($output, ENT_QUOTES));
        echo('</pre>');
        
    }
    
}
