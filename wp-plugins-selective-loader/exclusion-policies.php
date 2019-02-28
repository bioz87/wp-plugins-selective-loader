<?php

/*----------------------------------------------------*/
// Definisco politica di gestione dei plugins
/*----------------------------------------------------*/

function plugins_to_remove_from_frontend($to_remove) {
    if(!defined("PLUGINS_TO_REMOVE_FROM_FRONTEND"))
        define("PLUGINS_TO_REMOVE_FROM_FRONTEND", serialize(array()));
    $to_remove = array_merge($to_remove, unserialize(PLUGINS_TO_REMOVE_FROM_FRONTEND));

    return $to_remove;
}
add_filter( 'remove_plugins_on_frontend', 'plugins_to_remove_from_frontend');



function plugins_to_remove_for_unauthenticated_users($to_remove) {
    if(!defined("PLUGINS_TO_REMOVE_FOR_UNAUTHENTICATED_USERS"))
        define("PLUGINS_TO_REMOVE_FOR_UNAUTHENTICATED_USERS", serialize(array()));
    $to_remove = array_merge($to_remove, unserialize(PLUGINS_TO_REMOVE_FOR_UNAUTHENTICATED_USERS));

    return $to_remove;
}
add_filter( 'remove_plugins_for_unauthenticated_users', 'plugins_to_remove_for_unauthenticated_users');



function plugins_to_remove_for_non_admin_users($to_remove) {
    if(!defined("PLUGINS_TO_REMOVE_FOR_NON_ADMIN_USERS"))
        define("PLUGINS_TO_REMOVE_FOR_NON_ADMIN_USERS", serialize(array()));
    $to_remove = array_merge($to_remove, unserialize(PLUGINS_TO_REMOVE_FOR_NON_ADMIN_USERS));

    return $to_remove;
}
add_filter( 'remove_plugins_for_non_admin_users', 'plugins_to_remove_for_non_admin_users');



function plugins_to_remove_usually_not_needed($to_remove) {
    if(!defined("PLUGINS_TO_REMOVE_USUALLY_NOT_NEEDED"))
        define("PLUGINS_TO_REMOVE_USUALLY_NOT_NEEDED", serialize(array()));
    $to_remove = array_merge($to_remove, unserialize(PLUGINS_TO_REMOVE_USUALLY_NOT_NEEDED));

    return $to_remove;
}
add_filter( 'remove_plugins_not_used_as_usual', 'plugins_to_remove_usually_not_needed');



function plugins_to_remove_in_ajax_calls($to_remove) {
    if(!defined("PLUGINS_TO_REMOVE_IN_AJAX_CALLS"))
        define("PLUGINS_TO_REMOVE_IN_AJAX_CALLS", serialize(array()));
    $to_remove = array_merge($to_remove, unserialize(PLUGINS_TO_REMOVE_IN_AJAX_CALLS));

    return $to_remove;
}
add_filter( 'remove_plugins_on_ajax_calls', 'plugins_to_remove_in_ajax_calls');



function define_inclusion_custom_rules($to_remove) {
    if(!defined("INCLUSION_CUSTOM_RULES"))
        define("INCLUSION_CUSTOM_RULES", serialize(array()));
    $inclusions = unserialize(INCLUSION_CUSTOM_RULES);

    foreach ($inclusions as $inclusion)
    {
        foreach ($inclusion["pages"] as $page)
        {
            foreach ($inclusion["plugins"] as $plugin)
            {
                if(strpos($_SERVER['REQUEST_URI'], $page) !== false)
                {
                    $index = array_search($plugin, $to_remove);
                    if($index != false)
                    {
                        unset($to_remove[$index]);
                    }
                }
            }
        }
    }

    return $to_remove;
}
add_filter( 'apply_inclusion_custom_rules', 'define_inclusion_custom_rules');

function define_exclusion_custom_rules($to_remove) {
    if(!defined("EXCLUSION_CUSTOM_RULES"))
        define("EXCLUSION_CUSTOM_RULES", serialize(array()));
    $exclusions = unserialize(EXCLUSION_CUSTOM_RULES);

    foreach ($exclusions as $exclusion)
    {
        foreach ($exclusion["pages"] as $page)
        {
            foreach ($exclusion["plugins"] as $plugin)
            {
                if(strpos($_SERVER['REQUEST_URI'], $page) !== false)
                {
                    $index = array_search($plugin, $to_remove);
                    if($index == false)
                    {
                        array_push($to_remove, $plugin);
                        unset($to_remove[$index]);
                    }
                }
            }
        }
    }

    return $to_remove;
}
add_filter( 'apply_exclusion_custom_rules', 'define_exclusion_custom_rules');
?>