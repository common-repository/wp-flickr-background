<?php
/*
Plugin Name: WP Flickr Background
Plugin URI: http://www.myatus.co.uk/wp-flickr-background/
Description: WP Flickr Background is a simple to use WordPress plugin that allows you to display a photo from Flickr as the theme background, without the need to modify any files. <strong>Note: WP Flickr Background has been superseded by <a href="http://wordpress.org/extend/plugins/background-manager/" target="_blank">Background Manager</a></strong>
Version: 1.2
Author: Mike Green (Myatu)
Author URI: http://www.myatus.co.uk/
Minimum WordPress Version: 2.9
Minimum PHP Version: 4.3.2
*/

/**
 * WP Flickr Background
 * 
 * WP Flickr Background is a simple to use WordPress plugin that allows you to 
 * display a photo from Flickr as the theme background, without the need to 
 * modify any files.
 * 
 * All you need to do is create one or more galleries within the plugin's settings, 
 * each containing a collection of photos from Flicker that you have chosen, and WP 
 * Flickr will randomly select a photo from the active gallery to display as the
 * theme background.
 * 
 * You can also customise a gallery by adding CSS styling code that will be loaded 
 * along with the photo, allowing you to color match the Wordpress theme to 
 * the particular photo displayed or for other use.
 *
 *
 * This file is part of WP Flickr Background
 * Copyright 2010-2011 Mike Green (Myatu)
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
 * @author Mike Green (Myatu) <me@myatus.co.uk>
 * @copyright Copyright 2010-2011 Mike Green (Myatu)
 * @license http://www.gnu.org/licenses/gpl.html
 * @link http://www.myatus.co.uk
 * @version $Id: wp-flickr-background.php 482824 2011-12-31 17:13:21Z Myatu $ 
 *
 */
 
 
/**
Tested with:

Google Chrome 3.0.195.38
Opera 10.10
Firefox 3.0.17
Internet Explorer 8.0.6001.18865
Safari 4.0.3 (531.9.1) Mac & Windows

**/ 

/**
 * Plugin global reference:
 */
global $wp_flickr_background;

// Required Includes:
require_once(dirname(__FILE__) . '/inc/class-myatuPluginBase.php');
require_once(dirname(__FILE__) . '/inc/class-Flickr.php');

// Optional Includes:
@include_once(dirname(__FILE__) . '/contrib/class-javascriptPacker.php');
@include_once(dirname(__FILE__) . '/contrib/htmlspecialchars_decode.php');

if ( !class_exists('wpFlickrBackground') ) {

	/**
	 * WP Flickr Background plugin class
	 *
	 * This class encapsulates all the functions for the plugin
	 *
	 * @package wpFlickrBackground
	 */
	class wpFlickrBackground extends myatuPluginBaseClass {
		/** 
		 * Flickr class object
		 * @var object
		 * @access private
		 */
		var $_flickr = null;

		/**
		 * Initializes the plugin
		 */
		function on_init() {
			$this->_flickr = new flickrClass('85482b1bc18c383d9e930873e06d0194', $this->name);
			
			add_action('wp_flickr_background_javascript',		array($this, '_public_javascript'), 10, 1);
			add_action('wp_flickr_background_stylesheet',		array($this, '_public_stylesheet'), 10, 1);
			add_filter('wp_flickr_background_get_options_hash',	array($this, '_public_get_options_hash'));
			
			// Initialize the config hash file.
			$this->_init_config_hash_file();
		}
		
		/**
		 * Deinitializes the plugin
		 */
		function on_deinit() {
			if ( !empty($this->_flickr) )
				unset($this->_flickr);
		}	

		/**
		 * Default Plugin Options callback
		 *
		 * @return array Default plugin options. 
		 * @see myatuPluginBaseClass::upgrade_options()
		 */
		function get_default_options() {		
			return array (
				'active_gallery'	=> 0,							/* int - What's the current active gallery, by index */
				'change_freq'		=> '',							/* int|string - Background change frequency, in minutes */
				'image_alignment'	=> 2,							/* int - Background alignment (0=top-left, 1=top-right, 2=bottom-left, 3=bottom-right) */
				'image_h_stretch'	=> true,						/* bool - Stretch image horizonatlly */
				'image_v_stretch'	=> false,						/* bool - Stretch image vertically */
				'is_cacheable'		=> false,						/* bool - Is the plugin JS and CSS output cacheable */
				'pack_js'			=> true,						/* bool - Compress public JS */ 
				'disable_orig_bg'	=> true,						/* bool - Disable original theme's background */
				'hide_lic_attr'		=> false,						/* bool - Hide the License and attribution box on public side */
				'license_location'	=> 'left',						/* string - 'left' or 'right'  */
				'galleries'			=> array (						/* array - Galleries */
					array (
						'name'		=> 'Default',					/* string - Gallery name */
						'desc'		=> 'Default Gallery',			/* string - Gallery description */
						'customcss' => '',							/* string - Custom CSS to be included with theme/gallery */
						'photos'	=> array (						/* array - Photos in the gallery */
							array(
								'id'			=> '',				/* string - Flickr Photo ID */
								'title'			=> '',				/* string - Photo title */
								'owner'			=> '',				/* string - (user)name of photographer */
								'photopage'		=> '',				/* string - URL to the Photo's original Flickr page */
								'thumbnail'		=> '',				/* string - URL to thumbnail image of the photo */
								'background'	=> '',				/* string - URL to the full size image of the photo */
								'licensename'	=> '',				/* string - Name of the license for this photo */
								'licenseurl'	=> ''				/* string - URL to a full description of the license */
							)
						)
					)
				)
			);
		}
		
		/**
		 * Get Submenus Callback
		 *
		 * @return array Return the available submenus
		 */
		function get_submenus() {
			$plugin_options = $this->get_options();
			
			return array(
				'configuration' => __('Configuration', $this->name),
				'galleries' => sprintf( __('Galleries <span class="count">(%s)</span>', $this->name), number_format_i18n(count($plugin_options['galleries'])) ),
				'about' => __('About'),
				'gallery-edit' => ''
			);			
		}
		
		/**
		 * Set the help context
		 *
		 * @return string String containing help context depending on the submenu
		 */
		function get_help_context($active_submenu='') {
			$more_information_link = sprintf( __('<p>For more information visit the <a href="%s" onclick="return ! window.open(this.href);">%s</a> home page.</p>', $this->name), $this->get_plugin_info('PluginURI'), $this->get_plugin_info('Name'));

			switch ($active_submenu) {
				case 'configuration':
					return sprintf( __('<p>On this page you can specify the general configuration of %s.</p>', $this->name), $this->get_plugin_info('Name') ) . $more_information_link;
					break;
					
				case 'galleries':
					return __('<p>On this page you can view all the available galleries and perform additional functions such as create, edit or delete a gallery.</p>', $this->name) . $more_information_link;
					break;
					
				case 'gallery-edit':
					return __('<p>This page allows you to modify a gallery\'s name, description and add Flickr photos.</p>', $this->name) . $more_information_link;
					break;
					
					
				case 'about':
					return $more_information_link;
					break;
					
				default:
					return '';
					break;
			}
		}
		
		/**
		 * Event triggered when the plugin options are created
		 *
		 * We need to remove the 'photos' array from the default gallery on first-time
		 * installations, which is done here.
		 */
		function on_options_created() {
			$plugin_options = $this->get_options();
			
			@$plugin_options['galleries'][0]['photos'] = array();
			
			$this->set_options($plugin_options);
		}
		
		/**
		 * Event triggered when the plugin options have been changed
		 *
		 * It will save the hash of the configuration into the '.confighash' file, which
		 * will be used by the external Javascript and Style sheet. This is an optional
		 * feature, and the plugin will work without it.
		 */
		function on_options_changed() {
			$config_file = $this->_has_writeable_config_hash_file(true);
			
			if ( $config_file !== false ) {
				$fhandle = fopen($config_file, 'w');
				if ( $fhandle ) {
					fwrite($fhandle, $this->get_options_hash());
					fclose($fhandle);
				}
			}
		}

		/*
		Helper Functions
		*/
		
		/**
		 * Checks if the config hash file ('.confighash') exists and is writeable (internal)
		 *
		 * @access private
		 * @param bool $create_file Try to create the config hash file if it does not exist (not guaranteed)
		 * @return bool|string Returns false if the file is not writeable, otherwise the full path to the file.
		 * @changed 1.0.3
		 */
		function _has_writeable_config_hash_file($create_file=false) {
			$config_file = $this->get_plugin_dir() . '.confighash';
			
			// Attempt to create the file if it does not yet exist
			if ( !@file_exists($config_file) && $create_file )
				@fclose(@fopen($config_file, 'x'));
			
			if ( @file_exists($config_file) && is_writable($config_file) ) {
				return $config_file;
			} else {
				return false;
			}
		}
		
		/**
		 * Initializes a config hash file
		 *
		 * This will create the config hash file, if possible. If it already exists, it will check if
		 * it needs updating (ie., the user manually created the file due to permissions).
		 *
		 * @access private
		 */
		function _init_config_hash_file() {
			$config_file = $this->_has_writeable_config_hash_file(true);
			
			if ( $config_file !== false && (filesize($config_file) <> 32) ) {
				// Config hash file not likely to contain a valid config hash, initialize now
				$this->on_options_changed();
			}
		}
		
		/**
		 * Obtains a preview value from a GET request (internal)
		 *
		 * It will verify the NONCE prior to returning any values.
		 *
		 * @access private
		 * @param string $option The option for which to return the value
		 * @return string Blank if NONCE could not be verified or the option did not exist.
		 */	 
		function _get_preview_option($option) {
			$result = '';

			if ( isset($_GET[$option]) &&
				 isset($_GET['wpfbg_preview']) &&
			     isset($_GET['nonce']) &&
				 wp_verify_nonce((string)$_GET['nonce'], 'wpfbg_preview') ) {
				
				$result = strip_tags((string)$_GET[$option]);
			}
			
			return $result;
		}
		
		/**
		 * Generates the public-side Javascript code (internal)
		 *
		 * When the action 'wp_flickr_background_javascript' is called, this function will generate
		 * the output
		 *
		 * @access private
		 * @param string $preview_url Optionally override the gallery with the specified photo (URL to photo)
		 */
		function _public_javascript($preview_url='') {
			$plugin_options = $this->get_options();

			if ( $preview_url != '' ) {
				// Fill in the blanks:
				$default_options = $this->get_default_options();
				$results = $default_options['galleries'][0]['photos'];
				
				// Set the preview details:
				$results[0]['background']	= $preview_url;
				$results[0]['title']		= '(' . $this->get_plugin_info('Name') . ')';
				$results[0]['owner']		= '(' . __('Preview', $this->name) . ')';
				$results[0]['licensename']	= '(' . __('Preview', $this->name) . ')';
			} else {
				$results = $plugin_options['galleries'][$plugin_options['active_gallery']]['photos'];
			}
			
			// Output the Javascript if the photo array contains one or more photos:
			if ( count($results) > 0 ) {
				
				ob_start();
?>
				jQuery(document).ready(function($){
					var wpfbg_cookie 		= '<?php echo $this->name; ?>-cookie';
					var photos		 		= <?php echo json_encode($results); ?>;
					var random_selection	= Math.floor(photos.length * Math.random());

<?php			if ( empty($preview_url) &&
					 ( $plugin_options['change_freq'] == '' || ($plugin_options['change_freq'] != '' && (int) $plugin_options['change_freq'] > 0))
					) { ?>
					
					var has_valid_cookie	= false;
					
					if ( document.cookie && document.cookie != '' ) {
						var cookies	= document.cookie.split(';');
						var cookie	= '';
						
						for (var i=0; i<cookies.length; i++) {
							cookie = jQuery.trim(cookies[i]);
						
							if (cookie.substring(0, wpfbg_cookie.length + 1) == (wpfbg_cookie + '=')) {
								var saved_random_selection = decodeURIComponent(cookie.substring(wpfbg_cookie.length + 1));
								if ( (saved_random_selection >= 0) && (saved_random_selection < photos.length) ) {
									random_selection = saved_random_selection;
									has_valid_cookie = true;
								}
								
								break; // We're done here
							}
						}
					}
					
					if ( !has_valid_cookie ) {				
						var expires_mins	= <?php echo ( $plugin_options['change_freq'] != '' ) ? $plugin_options['change_freq'] : '\'\''; ?>;
						var cookie_expires	= '';
						
						if (typeof expires_mins == 'number') {
							var expires_date = new Date();
							expires_date.setTime(expires_date.getTime() + (expires_mins * 60 * 1000));
							cookie_expires = '; expires=' + expires_date.toUTCString();
						}
						
						// Save the random selection:
						document.cookie = wpfbg_cookie + '=' + encodeURIComponent(random_selection) + '; path=/' + cookie_expires;
					}
<?php 			} // If not preview or per-page reload ?>			

					var photo = photos[random_selection];
		
					var updated_body = $('body')
						.prepend(
							$('<div>').attr('id', 'wpfbg_msie_wrap') /* MSIE Layer - Wonky at times... */
								.append($('<img>').attr({'src': photo.background, 'id':'wpfbg'}))
						)
<?php 
	// Hide the license and attribution?
	if ($plugin_options['hide_lic_attr'] === true) {
?>
						;
<?php } else { ?>
					
						.append(
							$('<div>').attr('id','wpfbg_lic_wrap')
								.append($('<div>').attr('id','wpfbg_lic')
									.append($('<strong>').html(photo.title))
								)
						);
						
						if ( photo.owner != '' ) {
							$('#wpfbg_lic', updated_body)
								.append('<br /> <?php _e('Photo by', $this->name); ?> ')
								.append($('<a>').attr('href', photo.photopage).click(function(){return ! window.open(this.href);}).html(photo.owner));
						}
						
						if ( photo.licensename != '' && photo.licenseurl != '' ) {
							$('#wpfbg_lic', updated_body)
								.append(' (<?php _e('License', $this->name); ?>: ')
								.append($('<a>').attr('href', photo.licenseurl).click(function(){return ! window.open(this.href);}).html(photo.licensename))
								.append(')');
						}
<?php } ?>						
						
				});
				
<?php		
				if ( ($plugin_options['pack_js'] === true) && class_exists('JavaScriptPacker') ) {
					// Compress the Javascript output:					
					$packed_javascript = new JavaScriptPacker(ob_get_contents());

					ob_end_clean();
	
					echo $packed_javascript->pack();
				
					unset($packed_javascript);
				} else {
					ob_end_flush();
				}
			} // End Javascript Output
		}
		
		/**
		 * Generates the public-side Style sheet code (internal)
		 *
		 * When the action 'wp_flickr_background_stylesheet' is called, this function will generate
		 * the output
		 *
		 * 1.1: Added max-width and max-height, positioning of license.
		 *
		 * @access private
		 * @param string $preview_css Optionally override the gallery CSS with the preview CSS
		 */
		function _public_stylesheet($preview_css='') {
			$plugin_options	= $this->get_options();
			$active_gallery	= $plugin_options['active_gallery'];

			switch ( (int)$plugin_options['image_alignment'] ) {
				case 0:
					$image_alignment = 'top: 0; left: 0;';
					break;
				
				case 1:
					$image_alignment = 'top: 0; right: 0;';
					break;
					
				case 2:
					$image_alignment = 'bottom: 0; left: 0;';
					break;
					
				case 3:
					$image_alignment = 'bottom: 0; right: 0;';
					break;
					
				default:
					$image_alignment = '';
					break;
			}
			
			
			// Output the CSS:
			ob_start();
?>
			#wpfbg_msie_wrap { 
				position:absolute; 
				top:0; 
				left:0; 
				z-index:-999;
			}
			
			#wpfbg { 
				<?php echo $image_alignment; ?> 
				position:fixed; 
				width: <?php echo ( $plugin_options['image_h_stretch'] === true) ? '100%' : 'auto'; ?>; 
				<?php if ( $plugin_options['image_h_stretch'] === true ): ?>
				max-width: 100%;
				<?php endif; ?>
				
				height: <?php echo ( $plugin_options['image_v_stretch'] === true) ? '100%' : 'auto'; ?>; 
				<?php if ( $plugin_options['image_v_stretch'] === true ): ?>
				max-height: 100%;
				<?php endif; ?>				
				
				z-index:-999;				
			}
			
			html, body { 
				margin:0 !important; 
				padding:0 !important;
			}
			
			#wpfbg_lic_wrap {
				clear:both;
				width:100%;
			}
			
			#wpfbg_lic { 
				-webkit-border-top-right-radius:5px;
				-webkit-border-top-left-radius:5px;
				-moz-border-radius-topleft:5px;
				-moz-border-radius-topright:5px;
				background-color:#FFFFFF;
				color:#000000; 
				float: <?php echo $plugin_options['license_location'] ?>;
				margin:10px 10px 0;
				font-family:'Lucida Grande',Verdana,Arial,Sans-Serif; 
				font-size:8pt; 
				padding:5px; 
				text-align:left;
				opacity:0.8; 
				filter:alpha(opacity=80); 
				*display:inline; zoom:1;  
			}
			
			#wpfbg_lic a { 
				color:#FF0000; 
			}
			
			* html { 
				overflow-y:hidden; 
			}
			
			* body { 
				overflow-y:auto;
			}
			
<?php 		if ($plugin_options['disable_orig_bg']) { ?>
				body { 
					background-image: none !important;
				}
<?php 		}

			// Include custom or preview style sheets:
			if ( !empty($preview_css) ) {
				echo $preview_css;
			} elseif  ( array_key_exists($active_gallery, $plugin_options['galleries']) ) {
				echo htmlspecialchars_decode( $plugin_options['galleries'][$active_gallery]['customcss'] );
			}
			
			// Note: we're not using the ob_start() callback due to circular references (causing fatal errors in 1.0.2)
			$ob_output = ob_get_contents();
			ob_end_clean();
			
			echo preg_replace('/}/', '}' . PHP_EOL, preg_replace('/\t|\n|\r/', '', $ob_output));
		}	
		
		/**
		 * Exposes the get_options_hash() function in the 'wp_flickr_background_get_options_hash' filter (internal)
		 *
		 * Example:
		 * <code>
		 * $hash = apply_filters('wp_flickr_background_get_options_hash', false);
		 * </code>
		 *
		 * @access private
		 * @return string The current config hash
		 */
		function _public_get_options_hash() {
			return $this->get_options_hash();
		}
				
		/**
		 * Generates the code for a tooltip 
		 * 
		 * This function works in conjunction with the jQuery qTip and adm-global.js
		 *
		 * Example:
		 * <code>
		 *   $this->tooltip(__'This is a longer description of the option');
		 * </code>
		 *
		 * @access public
		 * @param string $tooltip Tooltip contents
		 * @param string $title Optional title for the tooltip
		 **/
		function tooltip($tooltip, $title='What\'s This?') {
			printf ( '<div class="tooltip">%s<div class="tooltip-content">%s</div></div>', $title, $tooltip );
		}
		
		/*
		Admin Functions
		*/
		
		/**
		 * Deletes a single gallery entry from options array.
		 *
		 * @param int The index of the gallery to be deleted
		 * @return bool Returns true if successful, false otherwise
		 */
		function admin_gallery_delete($idx) {
			$plugin_options = $this->get_options();
			
			if ( $idx == 0 || !array_key_exists($idx, $plugin_options['galleries']) ) 
				return false;
			
			// Delete gallery
			unset($plugin_options['galleries'][$idx]);
			
			// Reset to default gallery if the deleted gallery was the active one
			if ( $plugin_options['active_gallery'] == $idx )
				$plugin_options['active_gallery'] = 0;
			
			return $this->set_options($plugin_options);
		}
		
		/**
		 * Updates a single gallery entry
		 *
		 * @param int $idx The index of the gallery to be updated. Specify -1 to create a new gallery
		 * @param array $data Raw POST data containing the fields to be saved for this gallery
		 * @return bool Returns true if successful, false otherwise
		 * @see get_default_options(), on_admin_action()
		 */
		function admin_gallery_update($idx, $data) {
			$plugin_options = $this->get_options();
			
			// If an existing gallery was specified but doesn't exist, then exit
			if ( ($idx >= 0) && !array_key_exists($idx, $plugin_options['galleries']) ) 
				return false;

			// We need at least a name
			if ( empty($data['name']) ) {
				$this->add_admin_notice( __('No gallery name was specified', $this->name), true );
				return false;
			}
			
			$data_to_save = array();
			
			$data_to_save['name']		= esc_attr(stripslashes($data['name']));
			$data_to_save['desc']		= esc_attr(stripslashes($data['desc']));
			$data_to_save['customcss']	= wp_specialchars(strip_tags(stripslashes($data['customcss'])), 1);
			$data_to_save['photos']		= array();
			
			// Any photos specified?
			if ( isset($data['photos_id']) ) {
				foreach ( $data['photos_id'] as $photo_idx => $photoid ) {
					$photo_to_save = array();
					
					$photo_to_save['id']			= $photoid;
					$photo_to_save['title']			= esc_attr(stripslashes($data['photos_title'][$photo_idx]));
					$photo_to_save['owner']			= esc_attr(stripslashes($data['photos_owner'][$photo_idx]));
					$photo_to_save['photopage']		= (string)$data['photos_photopage'][$photo_idx];
					$photo_to_save['thumbnail']		= (string)$data['photos_thumbnail'][$photo_idx];
					$photo_to_save['background']	= (string)$data['photos_background'][$photo_idx];
					$photo_to_save['licensename']	= (string)$data['photos_licensename'][$photo_idx];
					$photo_to_save['licenseurl']	= (string)$data['photos_licenseurl'][$photo_idx];
					
					$data_to_save['photos'][] = $photo_to_save;
					unset($photo_to_save);
				}
			}
			
			// Save to existing or new entry?
			if ( $idx >= 0 ) {
				$plugin_options['galleries'][$idx] = $data_to_save;
			} else {
				$plugin_options['galleries'][] = $data_to_save;
			}
			
			return $this->set_options($plugin_options);
		}
		
		/**
		 * Update the general configuration
		 *
		 * @param mixed $data Configuration data to save
		 * @see get_default_options(), on_admin_action()
		 */	 
		function admin_update_config($data) {
			$plugin_options = $this->get_options();
			
			$plugin_options['active_gallery']	= (int)$data['active_gallery'];
			$plugin_options['change_freq']		= (string)$data['change_freq'];
			$plugin_options['disable_orig_bg']	= ( !empty($data['disable_orig_bg']) );
			$plugin_options['is_cacheable']		= ( !empty($data['is_cacheable']) );
			$plugin_options['pack_js']			= ( !empty($data['pack_js']) );
			$plugin_options['image_v_stretch']	= ( !empty($data['image_v_stretch']) );
			$plugin_options['image_h_stretch']	= ( !empty($data['image_h_stretch']) );			
			$plugin_options['image_alignment']	= (int)$data['image_alignment'];
			$plugin_options['hide_lic_attr']	= ( !empty($data['hide_lic_attr']) );
			$plugin_options['license_location']	= $data['license_location'];
			
			return $this->set_options($plugin_options);
		}
			
		/**
		 * Retrieves the details about a photo and exits with an Ajax response (Callback)
		 *
		 * Note: this function does not return
		 *
		 * @param string $photo_url URL of the photo
		 * @see on_ajax_request()
		 */
		function get_photo_details($photo_url) {
			// First verify if it's a complete and valid Flickr URL
			if ( !$this->_flickr->split_photo_url($photo_url, $split_photo_url) ) 
				$this->ajax_response( __('The URL specfied is not a valid Flickr Photo Source', $this->name), true );
			
			// Check if the host belongs to flickr
			if ( $split_photo_url['host'] != 'flickr.com' )
				$this->ajax_response( sprintf(__('The photo is hosted at <strong>%s</strong>, which is not part of the Flickr service', $this->name), esc_attr($split_photo_url['host']), true) );
			
			// We made it this far, so we have a valid Flickr Photo Source URL.
			$photo_id = $split_photo_url['photo_id'];
			$results  = array('id'=>$photo_id);
				
			// Grab the details about the photo
			$flickr_results	= $this->_flickr->call_function('photos.getInfo', array('photo_id'=>$photo_id, 'extras'=>'original_format'));
			$flickr_error	= $this->_flickr->nice_error($flickr_results);	
			if ( $flickr_error != '' ) 
				$this->ajax_response( $flickr_error, true );
				
			if ( $flickr_results['photo']['media'] != 'photo' )
				$this->ajax_response( sprintf(__('The specified Flickr media is not a photo but a <strong>%s</strong>. Please specify photos only.', $this->name), esc_attr($flickr_results['photo']['media'])), true );			

			$results['owner']		= ( empty($flickr_results['photo']['owner']['realname']) ) ? $flickr_results['photo']['owner']['username'] : $flickr_results['photo']['owner']['realname'];
			$results['title']		= esc_attr($flickr_results['photo']['title']['_content']);
			$results['photopage']	= sprintf('http://www.flickr.com/photos/%s/%s/', $flickr_results['photo']['owner']['nsid'], $photo_id);		
		
			// Grab the license descriptions:
			$flickr_licenses = $this->_flickr->get_licenses();
			if ( $flickr_licenses === false )
				$this->ajax_response( __('Flickr is currently too busy. Please try again later.', $this->name), true );
			
			// Set default license details:
			$results['licenseurl']	= $results['photopage'];
			$results['licensename']	= 'Unknown';
			
			// Find the license used:
			foreach ( $flickr_licenses['licenses']['license'] as $license ) {
				if ( $license['id'] == $flickr_results['photo']['license'] ) {
					if ( !empty($license['url']) )
						$results['licenseurl'] = $license['url'];
						
					$results['licensename'] = $license['name'];
					
					break;
				}
			}

			// Grab the direct image urls to this photo:
			$flickr_results = $this->_flickr->call_function('photos.getSizes', array('photo_id'=>$photo_id));
			$flickr_error	= $this->_flickr->nice_error($flickr_results);		
			if ( $flickr_error != '' ) 
				$this->ajax_response( $flickr_error, true );

			/* We go through each one and build up the 'background' to the largest available to us,
			 * since this varies on uploaded size and API access
			 */
			$have_original_size = false;
			foreach ( $flickr_results['sizes']['size'] as $size ) {
				if ( $size['media'] == 'photo' ) {
					switch ( $size['label'] ) {
						case 'Square' :
						case 'Thumbnail' :
							$results['thumbnail'] = $size['source'];
							break;
													
						case 'Original' :							
							$have_original_size = true;
							// Fall Through to default (!)
							
						default:
							$results['background'] = $size['source'];
							break;
					}
				}

				if ( $have_original_size ) break;
			}
			
			// Send out the Ajax response:
			$this->ajax_response($results);
		}
		
		/**
		Admin Events
		**/
			
		/**
		 * Processes Actions
		 */
		 function on_admin_action($action, $is_bulk_action, $data) {
 			if ( !current_user_can('update_plugins') )
				wp_die( __('You do not have sufficient permissions.') );

			switch ( $action ) {
				case 'add-gallery':
					if ( !$this->admin_gallery_update(-1, $data) ) {
						$this->add_admin_notice( __('Unable to add the gallery', $this->name), true) ;
					} else {
						$this->add_admin_notice( __('Successfully added the new gallery', $this->name) );

						// Override the active submenu:
						$this->set_active_submenu('galleries');
					}					
					break;
					
				case 'save-gallery':
					if ( !$this->admin_gallery_update((int)$data['idx'], $data) ) {
						$this->add_admin_notice( __('Unable to update the gallery', $this->name), true );
					} else {
						$this->add_admin_notice( __('Successfully updated the gallery', $this->name) );
						
						// Override the active submenu:
						$this->set_active_submenu('galleries');
					}					
					break;
					
				case 'delete-gallery':
					if ( isset($data['idx']) ) {
						if ( $this->admin_gallery_delete((int)$data['idx']) ) {
							$this->add_admin_notice( __('Gallery has been deleted.', $this->name) );
						} else {
							$this->add_admin_notice( __('Could not delete the gallery!', $this->name ), true);
						}
					}
					break;
					
				case 'delete-selected-galleries':			
					if ( $is_bulk_action &&
						 array_key_exists('checked', $data) && 
						 is_array($data['checked']) ) {
						$all_ok = true;
						
						foreach ( $data['checked'] as $idx ) {
							if ( !$this->admin_gallery_delete((int)$idx) )
								$all_ok = false;
						}
						
						if ( $all_ok ) {
							$this->add_admin_notice( __('The selected galleries have been deleted.', $this->name) );
						} else {
							$this->add_admin_notice( __('One or more selected galleries could not be deleted!', $this->name), true );
						}
					}
					break;
					
				case 'update-config':
					if ( $this->admin_update_config($data) ) {
						$this->add_admin_notice( __('The configuration has been saved', $this->name) );
					} else {
						$this->add_admin_notice( __('The configuration could not be saved!', $this->name), true );
					}
					break;
					
				default:
					break;
			} // switch $action
		}
		
		/**
		 * Renders the Admin options page
		 */
		function on_admin_render() {
			$plugin_options = $this->get_options();
			$active_submenu_file = $this->get_plugin_dir() . 'adm-' . $this->get_active_submenu() . '.php';
?>		
			<div class="wrap">
				<div class="icon32" id="icon-options-general"><br /></div>
				<h2><?php printf( __('%s Settings', $this->name), $this->get_plugin_info('Name') ); ?></h2>
				<div>
					<?php $this->render_submenus(); ?>
				</div>
				<div class="clear"></div>
				<div class="options_content">
<?php 
					if ( @file_exists($active_submenu_file) ) {
						include_once($active_submenu_file);
					} else {
						_e('That submenu does not exist.', $this->name);
					}
?>
				</div>
			</div>
<?php
                    if ($this->get_active_submenu() == 'configuration') {
?>
            <div id="message" class="updated">
                <p>
                    <strong>Note:</strong> WP Flickr Background has been superseded by <a href="http://wordpress.org/extend/plugins/background-manager/" target="_blank">Background Manager</a>. It is highly recommended to upgrade, as WP Flickr Background will be discontinued.
                </p>
                <p>
                    You can safely import your current galleries into Background Manager, including any custom CSS settings. Additional benefits include:
                </p>
                <ul style="list-style: disc outside none; padding-left: 35px">
                    <li>Full screen images with ratio retention</li>
                    <li>Active background slideshow with fade in/out effects</li>
                    <li>More control over image positioning</li>
                    <li>Better browser support</li>
                    <li>And more...</li>                    
                </ul>
            </div>
<?php
                    }
		}

		/**
		 * Handles Admin Javascripts
		 */
		function on_admin_scripts() {
			wp_enqueue_script('dashboard');
			wp_enqueue_script('jquery-qtip', $this->get_plugin_url() . 'contrib/js/jquery-qtip/jquery.qtip-1.0.0-rc3.min.js',  array('jquery'), '1.0.0RC3');
			wp_enqueue_script($this->name . '-adm-global', $this->get_plugin_url() . 'js/adm-global.js',  array('jquery-qtip'), $this->get_plugin_info('Version'));
			
			switch ( $this->get_active_submenu() ) {
				case 'gallery-edit' :
					wp_enqueue_script('thickbox');
					wp_enqueue_script($this->name . '-adm-gallery-edit', $this->get_plugin_url() . 'js/adm-gallery-edit.js', false, $this->get_plugin_info('Version'));
					
					wp_localize_script(
						$this->name . '-adm-gallery-edit', 'galleryeditL10n', array(
							'by'						=> __('by', $this->name),
							'license'					=> __('License', $this->name),
							'authenticity_error'		=> __('The authenticity of the Ajax response could not be verified', $this->name),
							'photo_added'				=> __('The photo has been added to the gallery', $this->name),
							'photo_already_present'		=> __('The photo is already in this gallery', $this->name),
							'error'						=> __('Error', $this->name),
							'ajaxerror'					=> __('An unknown Ajax response was received. Please try again', $this->name),
							'enter_name_before_saving'	=> __('Please enter a name for this gallery before saving', $this->name),
							'l10n_print_after'			=> 'try{convertEntities(galleryeditL10n);}catch(e){};'
						) 
					);

					echo (
						'<script type="text/javascript">' . PHP_EOL .
						'//<![CDATA[' . PHP_EOL .
						'var gallery_preview_url = "' . add_query_arg( array( 'wpfbg_preview' => 'true', 'nonce' => wp_create_nonce('wpfbg_preview') ), trailingslashit(get_bloginfo('url')) ) . '";' . PHP_EOL .
						'//]]>' . PHP_EOL .
						'</script>'					
					);
					
					break;
					
				case 'galleries' :
					wp_enqueue_script($this->name . '-adm-galleries', $this->get_plugin_url() . 'js/adm-galleries.js', false, $this->get_plugin_info('Version'));
					break;
			}
		}
		
		/**
		 * Handles Admin styles
		 */
		function on_admin_styles() {
			wp_admin_css('dashboard');
			wp_enqueue_style('thickbox');
			wp_enqueue_style($this->name . '-adm', $this->get_plugin_url() . 'css/adm-style.css', false, $this->get_plugin_info('Version'), 'all');
		}

		/**
		 * Handles Ajax request
		 *
		 * @see get_photo_details()
		 */
		function on_ajax_request($action, $data) {
			switch ( $action ) {
				case 'get_photo_details' :
					$this->get_photo_details( trim((string)$data) );
					break;
			}
		}
		
		/**
		Public Events
		**/
		
		/**
		 * Handles Javascript on the public side
		 */
		function on_public_scripts() {
			$plugin_options = $this->get_options();
			
			if ( $this->get_var('css_loaded') === true ) {
				$bg_preview_option = $this->_get_preview_option('bg');

				if ( !$plugin_options['is_cacheable'] ) {
					// Hash to prevent caching of incorrect configuration or a preview:
					$hash = ( !empty($bg_preview_option) ) ? md5( $bg_preview_option . $this->get_options_hash() ) : $this->get_options_hash();
					wp_enqueue_script($this->name, $this->get_plugin_url() . 'pub-script.js.php', array('jquery'), $this->get_plugin_info('Version') . '&h=' . $hash );
				} else {
					wp_enqueue_script('jquery');
				}
			}
		}
		
		/**
		 * Handles style sheets on the public side
		 */
		function on_public_styles() {
			$plugin_options		= $this->get_options();
			$css_preview_option	= $this->_get_preview_option('css');
			
			if ( !$plugin_options['is_cacheable'] ) {
				// Hash to prevent caching of incorrect configuration or a preview:
				$hash = ( !empty($css_preview_option) ) ? md5( $css_preview_option . $this->get_options_hash() ) : $this->get_options_hash();
				wp_enqueue_style($this->name, $this->get_plugin_url() . 'pub-style.css.php', false, $this->get_plugin_info('Version') . '&h=' . $hash );
			} else {
				// Insert the stylesheet directly into the page
				echo PHP_EOL . '<!-- WP Flickr Background -->' . PHP_EOL . '<style type="text/css">' . PHP_EOL;
				do_action('wp_flickr_background_stylesheet', $css_preview_option);
				echo PHP_EOL . '</style>' . PHP_EOL . '<!-- /WP Flickr Background -->' . PHP_EOL;
			}
			
			/* The styles get loaded before the scripts. Some plugins, like "WordPress Mobile Edition" by Crowd Favorite, disable
			 * themes. If that happens, we should also disable our Javascript.
			 */
			$this->set_var('css_loaded', true);
		}
		
		/**
		 * Inserts the Javascript in directly in the footer if the 'is_cacheable' option is set and the style sheet is loaded
		 */
		function on_public_footer() {
			$plugin_options		= $this->get_options();
			$bg_preview_option	= $this->_get_preview_option('bg');
			
			if ( $plugin_options['is_cacheable'] && 
				 ($this->get_var('css_loaded') === true) ) {				
				echo PHP_EOL . '<!-- WP Flickr Background -->' . PHP_EOL . '<script type="text/javascript">' . PHP_EOL . '//<![CDATA[' . PHP_EOL;
				do_action('wp_flickr_background_javascript', $bg_preview_option);
				echo PHP_EOL . '//]]>' . PHP_EOL . '</script>' . PHP_EOL . '<!-- /WP Flickr Background -->' . PHP_EOL;
			}
		}

		
	} // class definition
} // if class_exists


/**
* Activate plugin
*
* Note: You'd rather want to use actions/filters instead of the global reference.
*/
if ( !isset($wp_flickr_background) || empty($wp_flickr_background) )
	$wp_flickr_background = new wpFlickrBackground(__FILE__);

/**
 * Uninstall Function
 *
 * This function is automatically called when the plugin is deleted (uninstalled) from WordPress.
 *
 * @see myatuPluginBaseClass::__construct()
 */
function on_uninstall_wpFlickrBackground() {
	global $wp_flickr_background;
	
	if ( isset($wp_flickr_background) && !empty($wp_flickr_background ) ) {
		$wp_flickr_background->delete_options();
		unset($wp_flickr_background);
	} else {
		delete_option('wp-flickr-background');
	}
}

?>