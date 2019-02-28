<?php
/**
* Plugin Name: Wordpress Plugin Selective Loader
* Description: A brief description about your plugin.
* Version: 1.0
* Author: Luca Piazzoni (info@be-oz.com)
* Author URI: https://github.com/bioz87
*/
require_once( ABSPATH . WPINC . '/pluggable.php' );
require_once( 'exclusion-policies.php' );
////////////////////////////////////////////////////////////////////////////////
// Carico plugin in modo selettivo
///////////////////////////////////////////////////////////////////////////////
function convert_to_human_readable($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}
function enable_plugins_selectively($plugins) {
    global $loaded_plugins;

    //Never remove a single plugin for the super admin.
    if(!is_super_admin())
    {
        $plugins_to_remove_when_on_frontend                 = array();
        $plugins_to_remove_when_the_user_is_not_logged_in   = array();
        $plugins_to_remove_for_non_admin_users              = array();
        $plugins_to_remove_for_ajax_calls                   = array();
        $plugins_to_remove_as_a_regular_rule                = array();

        $plugins_to_remove_when_on_frontend                 = apply_filters("remove_plugins_on_frontend",                 $plugins_to_remove_when_on_frontend );
        $plugins_to_remove_when_the_user_is_not_logged_in   = apply_filters("remove_plugins_for_unauthenticated_users",   $plugins_to_remove_when_the_user_is_not_logged_in );
        $plugins_to_remove_for_non_admin_users              = apply_filters("remove_plugins_for_non_admin_users",         $plugins_to_remove_for_non_admin_users );
        $plugins_to_remove_for_ajax_calls                   = apply_filters("remove_plugins_on_ajax_calls",               $plugins_to_remove_for_ajax_calls );
        $plugins_to_remove_as_a_regular_rule                = apply_filters("remove_plugins_not_used_as_usual",           $plugins_to_remove_as_a_regular_rule );

        $plugins_to_remove_when_on_frontend                 = $plugins_to_remove_when_on_frontend               ? $plugins_to_remove_when_on_frontend : array();
        $plugins_to_remove_when_the_user_is_not_logged_in   = $plugins_to_remove_when_the_user_is_not_logged_in ? $plugins_to_remove_when_the_user_is_not_logged_in : array();
        $plugins_to_remove_for_non_admin_users              = $plugins_to_remove_for_non_admin_users            ? $plugins_to_remove_for_non_admin_users : array();
        $plugins_to_remove_for_ajax_calls                   = $plugins_to_remove_for_ajax_calls                 ? $plugins_to_remove_for_ajax_calls : array();
        $plugins_to_remove_as_a_regular_rule                = $plugins_to_remove_as_a_regular_rule              ? $plugins_to_remove_as_a_regular_rule : array();

        $current_page 		= add_query_arg( array() );
        $plugins_to_remove	= array();

        //remove for frontend
        if(!is_admin())
        {
            $plugins_to_remove = array_merge($plugins_to_remove, $plugins_to_remove_when_on_frontend);
        }

        //remove for unauthenticated
        if(!is_user_logged_in())
        {
            $plugins_to_remove = array_merge($plugins_to_remove, $plugins_to_remove_when_the_user_is_not_logged_in);
        }

        //remove for non admin
        if(is_user_logged_in())
        {
            $plugins_to_remove = array_merge($plugins_to_remove, $plugins_to_remove_for_non_admin_users);
        }

        //remove on AJAX calls
        if(defined( 'DOING_AJAX' ) && DOING_AJAX )
        {
            $plugins_to_remove = array_merge($plugins_to_remove, $plugins_to_remove_for_ajax_calls);
        }


        //this plugins are usually not needed but a page could reinclude some of them
        $plugins_to_remove = array_merge($plugins_to_remove, $plugins_to_remove_as_a_regular_rule);


        //Fetching custom rules
        $plugins_to_remove = apply_filters("apply_inclusion_custom_rules", $plugins_to_remove );
        $plugins_to_remove = apply_filters("apply_exclusion_custom_rules", $plugins_to_remove );


        //Removing plugins and add statistics to browser console
        $plugins = array_values($plugins);

        for ($i=count($plugins)-1; $i >= 0 ; $i--) {
        	if(in_array($plugins[$i], $plugins_to_remove))
        	{
                unset($plugins[$i]);
        	}
        }

        if(!function_exists("add_stats_data"))
        {
            function add_stats_data($memory, $memory_peak, $plugins) {?>
                <script>
                        window.memory_usage         = '<?php echo($memory);?>';
                        window.memory_usage_peak    = '<?php echo($memory_peak);?>';
                        window.active_plugins = [
                        <?php
                        foreach ($plugins as $plugin)
                        {
                            echo "'".$plugin."',";
                        }
                        ?>
                        ];
                        console.log('Memory consumption: '+window.memory_usage);
                        console.log('Memory peak consumption: '+window.memory_usage_peak);
                        console.log('ACTIVE PLUGINS');
                        console.table(window.active_plugins);

                </script>
            <?php }
        }

        if(!function_exists("print_stats"))
        {
            function print_stats() {
                global $loaded_plugins;
                add_stats_data(
                    convert_to_human_readable(memory_get_usage()), 
                    convert_to_human_readable(memory_get_peak_usage()), 
                    $loaded_plugins
                );
            }
        }

        if(defined("ENABLE_PLUGIN_LOGGING") && ENABLE_PLUGIN_LOGGING == true)
        {
            remove_action( 'wp_footer', "print_stats");
            add_action('wp_footer', "print_stats");
        }

        $loaded_plugins=$plugins;

        unset($plugins_to_remove_when_on_frontend);
        unset($plugins_to_remove_when_the_user_is_not_logged_in);
        unset($plugins_to_remove_for_non_admin_users);
        unset($plugins_to_remove_for_ajax_calls);
        unset($plugins_to_remove_as_a_regular_rule);
    }
    return $plugins;
}
add_filter( 'option_active_plugins', 'enable_plugins_selectively' );
?>