<?php

/**
 * Class to Wrap some Wordpress functions
 */
class WpWrappers
{
    public static function getOption($name)
    {
        return get_option($name);
    }

    public static function updateOption($name, $data)
    {
        return update_option($name, $data);
    }
}
