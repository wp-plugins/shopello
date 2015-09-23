<?php
namespace Shopello;

use \WpWrappers;
use \SWP\Listing;
use \stdClass;

class ListingManager
{
    private $listings = array();
    private $maxItems = 50;

    private static $instance;

    // Singleton this
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // No direct instances
    private function __construct()
    {
        $this->loadListings();
    }

    /**
     * Load all Listings from the database
     */
    public function loadListings()
    {
        $settings = json_decode(WpWrappers::getOption('shopello_list'));

        if (empty($settings)) {
            return;
        }

        foreach ($settings as $key => $value) {
            $listing = new Listing();
            $listing->importSettings($value);

            $this->listings[$key] = $listing;
        }
    }

    /**
     * Save all Listings to the database
     */
    public function saveListings()
    {
        $settings = array();

        foreach ($this->listings as $key => $listing) {
            $settings[$key] = $listing->exportSettings();
        }

        return WpWrappers::updateOption('shopello_list', json_encode((object) $settings));
    }

    /**
     * Add Listing
     */
    public function addListing(Listing $listing)
    {
        if (count($this->listings) >= $this->maxItems) {
            return false;
        }

        end($this->listings);
        $id = key($this->listings) + 1;

        $this->listings[$id] = $listing;
        $this->listings[$id]->set_id($id);

        $this->saveListings();
    }

    /**
     * Remove Listing
     */
    public function removeListing($id)
    {
        if (isset($this->listings[$id])) {
            unset($this->listings[$id]);

            $this->saveListings();
        }

        return true;
    }

    /**
     * Edit Listing
     */
    public function editListing($id, stdClass $object)
    {
        if (isset($this->listings[$id])) {
            $this->listings[$id]->importSettings($object);

            $this->saveListings();
        }

        return true;
    }

    /**
     * Get All Listings
     */
    public function getAllListings()
    {
        return $this->listings;
    }

    /**
     * Get Listing By ID
     */
    public function getListingById($id)
    {
        return $this->listings[$id];
    }
}
