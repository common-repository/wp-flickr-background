<?php
/*
PLEASE DO NOT DELETE THE FOLLOWING LINE AND LEAVE IT AT THE TOP:
*/
if ( realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) ) die ('Restricted Access');

/**
 * This file is part of WP Flickr Background
 * Copyright 2010 Mike Green (Myatu)
 * 
 * WP Flickr Background is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * WP Flickr Background is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package wpFlickrBackground
 * @subpackage flickrClass
 * @author Mike Green (Myatu) <me@myatus.co.uk>
 * @copyright Copyright 2010 Mike Green (Myatu)
 * @license http://www.gnu.org/licenses/gpl.html
 * @link http://www.myatus.co.uk
 * @link http://www.flickr.com/services/api/
 * @version $Id: class-Flickr.php 482824 2011-12-31 17:13:21Z Myatu $ 
 *
 */
 
/**
 * Flickr Class
 *
 * Provides basic REST based Flickr API access
 *
 * @package wpFlickrBackground
 * @subpackage flickrClass
 */ 
class flickrClass {
	
	/**
	 * Stored Flickr API Key
	 * @var string
	 * @access private
	 */
	var $_api_key = '';

	/**
	 * Cached Flickr license details (false if nothing cached)
	 * @var array|bool
	 * @access private
	 */
	var $_licenses = false;
	
	/**
	 * The name of the textdomain, used for translations. Set to false if unused.
	 * @var string|bool
	 * @access private
	 */
	var $_textdomain = false;

	/**
	 * Constructor for PHP version < 5.0
	 *
	 * @param string $api_key The Flickr API key
	 * @param string $_textdomain Optional, the textdomain to use for translations
	 * @see __construct()
	 */
	function flickrClass($api_key, $_textdomain=false) {
		$this->__construct($api_key, $_textdomain);
	}

	/**
	 * Constructor for PHP version >= 5.0
	 *
	 * @param string $api_key The Flickr API key
	 * @param string $_textdomain Optional, the textdomain to use for translations
	 * @see flickrClass()
	 */	
	function __construct($api_key, $_textdomain=false) {
		$this->_api_key = $api_key;
		$this->_textdomain = $_textdomain;
	}

	/**
	 * Calls a Flickr function
	 *
	 * @param string $flickr_func The Flickr function to call
	 * @param array $flickr_args Optional array containing the parameters for the function call
	 * @return mixed|bool Will return false if there was an error, data specific to the function otherwise
	 */	
	function call_function($flickr_func, $flickr_args=array()) {
		$full_args = array_merge((array)$flickr_args, array(
			'method' => 'flickr.' . $flickr_func,
			'api_key' => $this->_api_key,
			'format' => 'php_serial'
		));
		
		$result = wp_remote_get(add_query_arg($full_args, 'http://api.flickr.com/services/rest/') );

		if ( is_wp_error($result) ) {
			return false;
		} else { 
			return @unserialize( wp_remote_retrieve_body($result) );
		}
	}
	
	/**
	 * Verifies if the URL is a valid Flickr Photo and splits the URL in its relevant portions
	 *
	 * This function accepts URLs to the owner's Photo Page or a static image. Examples are:
	 *
	 * - http://farm{farm-id}.static.flickr.com/{server-id}/{photo_id}_{secret}.jpg
	 * - http://www.flickr.com/photos/{user-id}/{photo-id}
	 *
	 * Depending on the URL, it will return the relavant portions of the URL. For the owner's Photo
	 * Page this is: 'host', 'user_id' and 'photo_id'. For static image URLs, this is: 'host',
	 * 'server_id', 'photo_id' and 'secret'.
	 *
	 * One method of determining whether the URL was to a Photo Page or a static image is to verify the
	 * existence of the 'secret' or 'server_id' in the returned variables.
	 *
	 * @param string $url URL to verify and split
	 * @param array &$results An optional array that will contain the split values if the URL was valid
	 * @return bool Returns true if the URL was valid, false otherwise
	 */
	function split_photo_url ($url, &$results=array()) {
		return ( is_string($url) && (
					( preg_match('@^(http[s]?://)?(www\.)?(?P<host>[^/]+)/photos/(?P<user_id>[^/]+)/(?P<photo_id>[^/]+)@i', strtolower($url), $results) != 0) ||
					( preg_match('@^(http[s]?://)?farm[0-9]{1,3}\.static[\.]?(?P<host>[^/]+)/(?P<server_id>[^/]+)/(?P<photo_id>[^_]+)_(?P<secret>[^_|\.]+)@i', strtolower($url), $results) != 0 )
				));
	}
	
	/**
	 * Retrieves the Flickr license details
	 *
	 * This function retrieves and caches the details about a license. Flickr
	 * returns a license 'id' with each photo, such as 'license=4'. The full
	 * details of license '4', such as description and URL to a help page, can 
	 * then be looked up with the results from this function.
	 *
	 * @return array Array containing the Flickr licenses
	 */
	function get_licenses() {
		if ( $this->_licenses === false )
			$this->_licenses = $this->call_function('photos.licenses.getInfo');
			
		return $this->_licenses;
	}
	
	/**
	 * Returns a more user-friendly error message from the Flickr call_function() results
	 *
	 * @param array $results The Flickr results to check for errors
	 * @return string A string containing a user-friendly error message or empty if no error
	 * @see call_function()
	 */
	function nice_error($results) {
		if ( $results === false )
			return __('There was a problem contacting Flickr. Please try again.', $this->_textdomain);
			
		if ( !array_key_exists('stat', $results) )
			return __('Malformed results received from Flickr. Please try again.', $this->_textdomain);
				
		if ( $results['stat'] != 'ok') {
			switch ( $results['code'] ) {
				case 1:
					return __('The photo could not be found or has been removed from public view.', $this->_textdomain); break;
					
				case 2:
					return __('You do not have permission to view this photo.', $this->_textdomain); break;
					
				case 100:
					return __('There was a problem with the API Key. Please contact the plugin author.', $this->_textdomain); break;
				
				case 105:
					return __('Flickr is currently too busy. Please try again later.', $this->_textdomain); break;
					
				case 111:
					return __('Flickr does not support the requested format. Please contact the plugin author.', $this->_textdomain); break;
					
				case 112:
					return __('Flickr does not support the requested function. Please contact the plugin author.', $this->_textdomain); break;
					
				default:
					return sprintf( __('Flickr reported <i>"%s"</i>.', $this->_textdomain), esc_attr($results['message']) ); break;
			}
		}
		
		return '';
	}
}