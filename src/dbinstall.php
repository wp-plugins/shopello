<?php

global $shopello_db_version;
$shopello_db_version = '3.3';

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

/**
 * Migrate away the serializaion of the class SWP_Item and store data as JSON in the database
 */
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
    update_option('shopello_db_version', '3.0');
}

/**
 * Migration to have Object IDs as Key in the Listing array in the database storage
 */
if (get_option('shopello_db_version') === '3.0') {
    $listings = json_decode(get_option('shopello_list'));

    $array = array();

    foreach ($listings as $listing) {
        $array[$listing->id] = $listing;
    }

    update_option('shopello_list', json_encode((object) $array));
    update_option('shopello_db_version', '3.1');
}

/**
 * We had overlooked a piece of code and got data stored incorrectly, this
 * migration intend to fix that.
 */
if (get_option('shopello_db_version') === '3.1') {
    $data = get_option('shopello_list');

    if ('object' === gettype($data)) {
        update_option('shopello_list', json_encode($data));
    }

    update_option('shopello_db_version', '3.2');
}

/**
 * Migration to add ID's to the listing objects
 */
if (get_option('shopello_db_version') === '3.2') {
    $items = json_decode(get_option('shopello_list'));

    foreach ($items as $id => $item) {
        $items->$id->id = $id;
    }

    update_option('shopello_list', json_encode($items));
    update_option('shopello_db_version', '3.3');
}
