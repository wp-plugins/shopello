<?php

global $shopello_db_version;
$shopello_db_version = '3.0';

// On plugin activate, install database tables
register_activation_hook(SHOPELLO_PLUGIN_DIR.'shopello_api.php', 'swp_db_install');

function swp_db_install()
{
    global $wpdb;
    global $shopello_db_version;

    $table_categories = $wpdb->prefix.SHOPELLO_PLUGIN_TABLE_CATEGORIES;
    $table_relations  = $wpdb->prefix.SHOPELLO_PLUGIN_TABLE_RELATIONS;

    /**
     * We'll set the default character set and collation for this table.
     * If we don't do this, some characters could end up being converted
     * to just ?'s when saved in our table.
     */
    $charset_collate = '';

    if (!empty($wpdb->charset)) {
        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
    }

    if (!empty($wpdb->collate)) {
        $charset_collate .= " COLLATE {$wpdb->collate}";
    }

    // Create categories table
    $sql1 = "CREATE TABLE IF NOT EXISTS `$table_categories` (`category_id` int(4) NOT NULL,  `name` varchar(60) NOT NULL) $charset_collate;";
    // Set primary key
    $sql2 = "ALTER TABLE `$table_categories` ADD UNIQUE KEY (`category_id`);";
    // Set relations table
    $sql3 = "CREATE TABLE IF NOT EXISTS `$table_relations` ( `category_id` int(4) NOT NULL, `parent_id` int(4) NOT NULL) $charset_collate;";

    require_once(ABSPATH.'wp-admin/includes/upgrade.php');

    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);

    add_option('shopello_db_version', $shopello_db_version);
    wp_schedule_event(time(), 'daily', 'swpsynccategories');
}

if (get_option('shopello_db_version') === '2.0') {
    // Function to convert an __PHP_Incomplete_Class to stdClass
    $incompleteClassToStdClass = (function ($incompleteClass) {
        $object = new stdClass;
        $incompleteClassArray = (array) $incompleteClass;

        // Get classname of class and drop it from the array
        $className = $incompleteClassArray['__PHP_Incomplete_Class_Name'];
        unset($incompleteClassArray['__PHP_Incomplete_Class_Name']);

        foreach ($incompleteClassArray as $key => $value) {
            $newKey = trim(str_replace($className, '', $key));

            $object->$newKey = $value;
        }

        return $object;
    });

    $array = array();
    $swpList = unserialize(get_option('swp_list'));

    foreach ($swpList as $item) {
        $array[] = $incompleteClassToStdClass($item);
    }

    delete_option('swp_list');
    add_option('shopello_list', json_encode($array));
    update_option('shopello_db_version', $shopello_db_version);
}

/*
// Mockup data
function swp_db_install_data() {
    global $wpdb;

    $welcome_name = 'Mr. WordPres';
    $welcome_text = 'Congratulations, you just completed the installation!';

    $table_categories = $wpdb->prefix . 'swp_categories';

    $wpdb->insert(
        $table_categories,
        array(
            'name' => "Bildelar",
            'category_id' => 1,
        )
    );
}

// Upgrade code for future programming
global $wpdb;
$installed_ver = get_option( "swp_db_version" );

if ($installed_ver != $swp_db_version) {
    $table_name = $wpdb->prefix . 'liveshoutbox';

    $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		text text NOT NULL,
		url varchar(100) DEFAULT '' NOT NULL,
		UNIQUE KEY id (id)
	    );";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);

    update_option( "swp_db_version", $swp_db_version );
}

function myplugin_update_db_check()
{
    global $swp_db_version;
    if ( get_site_option( 'swp_db_version' ) != $swp_db_version ) {
        swp_install();
    }
}
add_action('plugins_loaded', 'myplugin_update_db_check');
//*/
