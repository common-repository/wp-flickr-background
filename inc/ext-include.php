<?php
/* 
PLEASE DO NOT DELETE THE FOLLOWING LINE AND LEAVE IT AT THE TOP:
*/
if ( realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) ) die ('Restricted Access');

/**
 * External includes for Javascript and Style sheet loaders
 *
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
 * @subpackage externalLoaders
 * @author Mike Green (Myatu) <me@myatus.co.uk>
 * @copyright Copyright 2010-2011 Mike Green (Myatu)
 * @license http://www.gnu.org/licenses/gpl.html
 * @link http://www.myatus.co.uk
 * @version $Id: ext-include.php 353401 2011-02-28 20:54:08Z Myatu $ 
 *
 * @todo Replace cache control with local etag file
 */


/**
Quiet!
**/ 
error_reporting(0);

 
/**
 * Cache control
 *
 * If set to a value greater than zero, it will try to keep the output
 * in the browser cache for the specified time in minutes (ie, 3600 is one hour)
 *
 * This works independent of the WP Flickr Background 'is_cacheable' option.
 *
 * @var int
 */
$cache_expires = 3600; // One hour

/**
 * Retrieves the data from the config hash file ('../.confighash')
 *
 * @return bool|strong Returns false if the config hash file does not exist or isn't readable
 */
function get_config_hash_file_data() {
	$config_file = dirname(dirname(__FILE__)) . '/.confighash';
	$hash = false;
			
	if ( @file_exists($config_file) && is_readable($config_file) ) {
		$fhandle = fopen($config_file, 'r');
		if ( $fhandle ) {
			$hash = fgets($fhandle, 33);
			fclose($fhandle);
		}		
	}
	
	return $hash;
}

/**
 * Sends out the cache headers
 *
 * @param string $hash The hash of the current configuration
 * @param int $cache_expires Cache offset in minutes. Use 0 if no cacheing is desired.
 */
function send_cache_header($hash, $cache_expires) {
	if ( $cache_expires > 0 ) {
		header('Cache-Control: public, max-age=' . $cache_expires);
		header('Expires: ' . gmdate( "D, d M Y H:i:s", time() + $cache_expires ) . ' GMT');
		header('ETag: "' . $hash . '"');
			
		if ( isset($_SERVER['HTTP_IF_NONE_MATCH']) && (strpos($_SERVER['HTTP_IF_NONE_MATCH'], $hash) !== false) ) {
			header('HTTP/1.1 304 Not Modified');
			die();
		}
	} else {
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Sat, 1 Mar 1997 00:00:00 GMT');
		header('Cache-Control: must-revalidate');
	}
}

// We start output buffering to suppress any erronous plugin output
ob_start();

/**
 * Set to true if there's a preview request, false otherwise (local)
 * @var bool
 */
$preview_req = false;

/**
 * Contains the NONCE of a preview request, if any (local)
 * @var string
 */
$preview_nonce = '';

/**
 * Contains the URL of the background image to preview, if any (local)
 * @var string
 */
$preview_url = '';

/**
 * Contains the custom CSS Style sheet to preview, if any (local)
 * @var string
 */
$preview_css = '';

/**
Extract and fill in the local vars:
**/
preg_match_all('/(\w+)=([^\&]*)/i', (string)$_SERVER['HTTP_REFERER'], $ext_options);

foreach ($ext_options[1] as $ext_opt_idx => $ext_opt_key) {
	$value = urldecode($ext_options[2][$ext_opt_idx]);

	switch ( $ext_opt_key ) {
		case 'wpfbg_preview':
			$preview_req = (bool)$value;
			break;
		
		case 'nonce':
			$preview_nonce = $value;
			break;		
		
		case 'css':
			$preview_css = strip_tags($value);
			break;

		case 'bg':
			$preview_url = strip_tags($value);
			break;				
	}
}

/**
Note that the send_cache_header will not return if:
- the config_hash matches what the browser sent, AND
- there was no preview request (regardless of NONCE)

The purpose here is to check to send back a response
as quickly as possible, before we start loading WordPress
and generating Javascript / Style sheets, etc.
**/
$config_hash = get_config_hash_file_data();
if ( $config_hash )
	send_cache_header( $config_hash, (!$preview_req) ? $cache_expires : 0 );

/**
Load WordPress
**/
if ( !defined('ABSPATH') ) {
	function traverseToWordpressLoader() {
		for ($i=0; $i<16; $i++) {
			$search_dir = str_repeat('../', $i);
			if ( file_exists( $search_dir. 'wp-load.php') ) {
				return realpath($search_dir) . '/';
			}
		}
		
		return '';
	}

	require_once( traverseToWordpressLoader() . 'wp-load.php' );
}

/**
 * Set to true if there's a NONCE validated preview request
 *
 * Only when this is set are the $preview_ fields valid.
 *
 * @var bool
 */
$is_preview = ( $preview_req && ($preview_nonce == wp_create_nonce('wpfbg_preview')) );

// Discard any extranous output thus far
ob_end_clean();

/**
Send the cache headers
**/
$config_hash = apply_filters('wp_flickr_background_get_options_hash', false);
@send_cache_header( $config_hash, ( !$is_preview ) ? $cache_expires : 0 );

//-- That's right, no closing tag!