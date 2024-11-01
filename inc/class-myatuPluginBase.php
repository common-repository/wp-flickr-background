<?php
/*
PLEASE DO NOT DELETE THE FOLLOWING LINE AND LEAVE IT AT THE TOP:
*/
if ( realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) ) die ('Restricted Access');

/**
 * Myatu's Plugin Base Class for WordPress
 * 
 * This file defines the myatuPluginBaseClass, a basic class that encapsulates common
 * functions used by WordPress plugins, making plugin development faster and consistent.
 *
 *
 * Copyright 2010 Mike Green (Myatu)
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package myatuPluginBaseClass 
 * @author Mike Green (Myatu) <me@myatus.co.uk>
 * @copyright Copyright 2010 Mike Green (Myatu)
 * @license http://www.gnu.org/licenses/gpl.html
 * @link http://www.myatus.co.uk
 * @version 1.0.2
 */


if ( !class_exists('myatuPluginBaseClass') ) {

	/**
	 * Myatu's Plugin Base Class
	 *
	 * Encapsulates common functions used by WordPress plugins in a simple to use class
	 *
	 * @package myatuPluginBaseClass
	 */
	class myatuPluginBaseClass {	
		
		/**
		 * Plugin reference name
		 * @var string
		 * @access protected
		 */
		var $name = '';
		
		/**
		 * The name of the options page reserved for us by WordPress
		 * @var string
		 * @access protected
		 */	 
		var $options_page = '';
		
		/**
		 * Plugin-wide variable storage
		 * @var array()
		 * @access private
		 * @see get_var(), set_var()
		 */
		var $_vars = array();
		
		/**
		 * Plugin base directory
		 * @var string
		 * @access private
		 * @see __construct()
		 */
		var $_plugin_base_dir = '';
		
		/**
		 * Plugin base filename
		 * @var string
		 * @access private
		 * @see __construct()
		 */
		var $_plugin_base_file = '';
		
		/**
		 * Plugin information cache
		 * @access private
		 * @var bool|array
		 * @see get_plugin_info()
		 */		 
		var $_plugin_info = false;
		
		/**
		 * Data collected about installed plugins
		 * @access private
		 * @var bool|array
		 * @see get_installed_plugin_info()
		 */
		var $_installed_plugins = false;
		
		/**
		 * Holds the submenu details
		 * @access private
		 * @var array
		 * @see get_submenus()
		 */
		var $_submenus = array();
		
		/**
		 * Default plugin headers
		 *
		 * Note: FileBase, MinWPVersion and MinPHPVersion are unique to myatuPluginBaseClass 
		 *
		 * @access private
		 * @see get_plugin_info(), get_installed_plugin_info()
		 */
		var $_default_plugin_headers = array( 
			'Name'			=> 'Plugin Name', 
			'PluginURI'		=> 'Plugin URI', 
			'Version'		=> 'Version', 
			'Description'	=> 'Description', 
			'Author'		=> 'Author', 
			'AuthorURI'		=> 'Author URI',
			'TextDomain'	=> 'Text Domain',
			'DomainPath'	=> 'Domain Path',
			'FileBase'		=> '',
			'MinWPVersion'	=> 'Minimum WordPress Version',
			'MinPHPVersion'	=> 'Minimum PHP Version'
		);

		/**
		Constructor and Deconstructor
		**/
		
		/**
		 * Constructor for PHP version < 5.0
		 * 
		 * @see __construct()
		 */
		function myatuPluginBase($plugin_base_file, $internal_name=false) {
			$this->__construct($plugin_base_file, $internal_name);
			register_shutdown_function( array($this, '__destruct') );
		}
		
		/**
		 * Constructor for PHP version >= 5.0
		 *
		 * Example:
		 * <code>
		 * <?php
		 *   require_once(dirname(__FILE__) . '/inc/class_myatuPluginBase.php');
		 *   class myCoolPlugin extends myatuPluginBaseClass {
		 *     function hello($world) { echo 'Hello ' . $world; }
		 *   }
		 *   $myCoolPlugin = new myCoolPlugin(__FILE__);
		 * ?>
		 * </code>
		 *
		 * Also note that the constructor registers an on_uninstall_<className> hook
		 * that must be defined *outside* the class. Example:
		 *
		 * <code>
		 * class myCoolPlugin extends myatuPluginBaseClass {
		 *   ..
		 * }
		 *
		 * function on_uninstall_myCoolPlugin() {
		 *   delete_option('my-cool-plugin');
		 * }
		 * </code>
		 *
		 * @param string $plugin_base_file Required, plugin filename
		 * @param string $internal_name Optional, plugin reference name
		 * @see __construct()
		 */
		function __construct($plugin_base_file, $internal_name=false) {
			$this->_plugin_base_file	= $plugin_base_file;
			$this->_plugin_base_dir		= trailingslashit('/plugins/' . plugin_basename(dirname($this->_plugin_base_file)));
		
			if ( !$internal_name ) {
				// Converts the classname to an internal version, ie wpMyPlugin = wp-my-plugin
				$this->name = strtolower(preg_replace('/([A-Z]+(?:[a-z]))/', '-${0}', get_class($this) ));
			} else {
				$this->name = $internal_name;
			}

			// Optional PHP < 5.0 helper:
			@include_once($this->get_plugin_dir() . 'contrib/json_encode.php');
			
			$this->_load_plugin_locale();
		
			// Set private events
			add_action('admin_menu',				array($this, '_on_admin_register'));
			add_action('admin_notices',				array($this, '_on_admin_notices'));
			add_action('wp_ajax_' . $this->name,	array($this, '_on_ajax_call'));		
			add_action('parse_request',				array($this, '_on_public_init')); // Moved from 'init' since 1.0.1; called too early in WP 3.1+
			
			// Plugin listing events
			add_action('after_plugin_row_' . plugin_basename($this->_plugin_base_file),		array($this, '_on_after_plugin_row'));
			add_filter('plugin_action_links_' . plugin_basename($this->_plugin_base_file),	array($this, '_on_plugin_action_links'), 10, 1);
			
			// Activation hook
			register_activation_hook( $this->_plugin_base_file, array($this, '_on_activation') );
			
			/* Uninstall hook. Since this gets stored in the 'options' by WordPress, you cannot 
			 * use the class resource (due to serialization), so it must be outside the class.
			 */
			register_uninstall_hook( $this->_plugin_base_file, 'on_uninstall_' . get_class($this) );
			
			// We're done. Let the user safely specify whatever (s)he likes now:
			$this->on_init();
		}
		
		/**
		 * Destructor
		 *
		 * Called upon destruction of class.
		 *
		 * @abstract
		 */
		function __destruct() {
			$this->on_deinit();
		}
		
		/**
		Internal Functions (do not call directly)
		**/
		
		/**
		 * Internal function that updates each individual option (internal)
		 *
		 * @access private
		 * @param array $plugin_options Existing plugin options
		 * @param array $default_options Default plugin options
		 * @see upgrade_options()
		 */
		function _upgrade_option_rec(&$plugin_options, $default_options) {	
			foreach ( $default_options as $default_option_key => $default_option_value ) {
				// Create the option if it does not exist or is of a different type:
				if ( !array_key_exists($default_option_key, $plugin_options) ||
					 (gettype($plugin_options[$default_option_key]) != gettype($default_option_value)) ) {
					$plugin_options[$default_option_key] = $default_option_value;
				}

				// If the option is an array, update any existing entries in that array:
				if ( is_array($default_option_value) ) {
					foreach ( $plugin_options[$default_option_key] as $plugin_option_idx => $plugin_option_value ) {
						$this->_upgrade_option_rec($plugin_option_value, $default_option_value[0]);
						
						// Push changes back into the main $plugin_options variable
						$plugin_options[$default_option_key][$plugin_option_idx] = $plugin_option_value;
					}
				}
			}
		}

		/**
		 * Sets a warning if a minimum version requirement has not been met (internal)
		 *
		 * @access private
		 * @param string $min_version Minimum version required
		 * @param string $actual_version Actual version of the program/script
		 * @param string $program Name of the program/script being checked
		 */
		function _set_version_warning($min_version, $actual_version, $program) {
			if ( !empty($min_version) && 
				 !empty($actual_version) &&
				 version_compare($min_version, $actual_version, '>') ) {
				$this->add_admin_notice( sprintf( __('%s requires %s version %s or better, however version %s was detected. Proper functioning of this plugin cannot be guaranteed.', $this->name), $this->get_plugin_info('Name'), $program, $min_version, $actual_version), true );		
			}
		}
		
		/** 
		 * Loads the plugin translation file (internal)
		 * 
		 * @access private
		 */
		function _load_plugin_locale() {		
			$locale = get_locale();
	 
			if( !empty($locale) ) {
				$mofile = $this->get_plugin_dir() . 'lang/' . $this->name . '-' . $locale . '.mo';	
				if ( @file_exists($mofile) && is_readable($mofile) )
					load_textdomain($this->name, $mofile);
			}
		}
		
		/**
		 * Checks if the options were upgraded and sets an admin notice if true (internal)
		 *
		 * @access private
		 */
		function _is_upgraded() {
			$plugin_options = $this->get_options();
			
			if ( array_key_exists('_is_upgraded', $plugin_options) ) {
				unset($plugin_options['_is_upgraded']);
				$this->set_options($plugin_options);
				
				$this->add_admin_notice( sprintf( __('Successfuly upgraded <strong>%s</strong> to version <strong>%s</strong>.', $this->name), $this->get_plugin_info('Name'), $this->get_plugin_info('Version')) );
			}
		}
		
		/**
		Generic Helper Functions 
		**/
		
		/**
		 * Returns information about the plugin
		 *
		 * The information returned depends on the $field param. If the $field param is
		 * not specified, it will return an array with available information about the
		 * plugin. Otherwise, it will return the specified field if available or a blank
		 * if this field is not available.
		 *
		 * The information is extracted from the plugin base file, the same information that
		 * WordPress uses to display the plugin details. Example:
		 *
		 * <code>
		 * Plugin Name: My Cool Plugin
		 * Plugin URI: http://www.myatus.co.uk/
		 * Description: Does something really cool!
		 * Version: 1.0.2
		 * Author: Myatu
		 * Author URI: http://www.myatus.co.uk/
		 * Minimum WordPress Version: 2.9.1
		 * Minimum PHP Version: 5.2
		 * </code>
		 *
		 * Note: if using this function to retrieve information about an another (external) plugin, you may
		 * wish to use get_installed_plugin_info() instead
		 *
		 * @access public
		 * @param string $field Optional field descriptor
		 * @param strgin $external_plugin Optional field specifying the full file path to another plugin
		 * @return array|string Depends on $field param: array if none specified, blank string if the 
		 *		field was not found otherwise the value for the specified field.
		 * @see get_installed_plugin_info()
		 */
		function get_plugin_info($field=false, $external_plugin=false) {
			if ( $external_plugin )  {
				// Requesting info about an external plugin:
				$result = get_file_data( $external_plugin, $this->_default_plugin_headers, 'plugin' );
				$result['FileBase'] = plugin_basename($external_plugin);
			} elseif ( !$this->_plugin_info && !$external_plugin ) {
				// Requesting info about ourselves, but nothing cached yet:
				$result = get_file_data( $this->_plugin_base_file, $this->_default_plugin_headers, 'plugin' );
				$result['FileBase'] = plugin_basename($this->_plugin_base_file);
				
				// Cache the details:
				$this->_plugin_info = $result;
			} else {
				// Retrieve details from cache:
				$result = $this->_plugin_info;
			}
			
			if ( !$field ) {
				return ($result);
			} else {
				if ( is_array($result) && array_key_exists($field, $result) ) {
					return $result[$field];
				} else {
					return '';
				}
			}
		}
		
		/**
		 * Returns the plugin directory
		 *
		 * @access public
		 * @return string Plugin directory (always with trailing slash)
		 */
		function get_plugin_dir() {		
			return trailingslashit(WP_CONTENT_DIR . $this->_plugin_base_dir);
		}

		/**
		 * Get the plugin URL
		 *
		 * @access public
		 * @return string Plugin URL (always with a trailing slash)
		 */
		function get_plugin_url() {		
			return trailingslashit(WP_CONTENT_URL . $this->_plugin_base_dir);
		}
		
		/**
		 * Get the plugin admin URL
		 * 
		 * @access public
		 * @return string Returns the URL to the WordPress option page for this plugin
		 */
		function get_admin_url() {
			return admin_url('options-general.php?page=') . $this->name;
		}
		
		/** 
		 * Get the plugin options
		 *
		 * @access public
		 * @return array Array with the options
		 * @see set_options()
		 */
		function get_options() {
			$result = get_option($this->name);
			
			// If we couldn't get the options, we likely need to repair our options:
			if ( $result === false ) {
				$this->upgrade_options(true);
				$result = get_option($this->name);
			}

			return $result;
		}
		
		/**
		 * Updates or creates the plugin options
		 *
		 * @access public
		 * @return bool True if successful, false otherwise
		 * @see get_options()
		 */
		function set_options($options) {
			$original_hash = $this->get_options_hash();
			$new_hash = $this->get_options_hash($options);

			// Nothing changed, so return true (WordPress returns false in this case!)
			if ( $original_hash === $new_hash )
				return true;
			
			$result = update_option($this->name, $options);
			
			if ( $result )
				$this->on_options_changed();
				
			return $result;
		}
		
		/**
		 * Deletes all the options associated with this plugin
		 *
		 * @access public
		 * @return bool True if successful
		 */
		function delete_options() {
			return delete_option($this->name);
		}
			
		/**
		 * Gets a variable
		 *
		 * @access public
		 * @param string $key Key to the corresponding value to retrieve
		 * @return mixed|boolean Returns false if the key does not exist
		 * @see set_var(), delete_var()
		 */
		function get_var($key) {
			if ( array_key_exists($key, $this->_vars) )
				return $this->_vars[$key];
			
			// Otherwise return false
			return false;
		}
		
		/**
		 * Sets a variable
		 * 
		 * Prior to 1.0.1, this a was per-user variable, which is now a plugin-wide variable
		 *
		 * @access public
		 * @param string $key Key to the corresponding value
		 * @param mixed $val Value to store
		 * @see get_var(), delete_var()
		 */
		function set_var($key, $val) {
			$this->_vars[$key] = $val;
		}
		
		/**
		 * Deletes a variable
		 *
		 * @access public
		 * @param string $key Key to the corresponding value to delete
		 * @see get_var(), set_var()
		 */
		function delete_var($key) {
			if ( array_key_exists($key, $this->_vars) )
				unset ($this->_vars[$key]);
		}
		
			
		/**
		 * Adds an Admin Notice to the queue (Admin Only)
		 *
		 * @access public
		 * @param string $message Message to display to the end user
		 * @param bool $is_error Optional parameter to indicate the message is an errormessage
		 * @see display_admin_notices()
		 */
		function add_admin_notice($message, $is_error=false) {
			if ( !is_admin() ) return; // Admin only function
			
			$class = ( $is_error ) ? 'error' : 'updated fade';
			$admin_notices = $this->get_var('_admin_notices');
			
			if ( !$admin_notices )
				$admin_notices = array();
				
			$admin_notices[] = '<div id="message" class="' . $class . '"><p>' . $message . '</p></div>';
			
			$this->set_var('_admin_notices', $admin_notices);
		}
		
		/**
		 * Clears the Admin Notice queue(Admin Only)
		 *
		 * @access public
		 * @see display_admin_notices()
		 */
		function clear_admin_notices() {
			if ( !is_admin() ) return; // Admin only function
			
			$this->set_var('_admin_notices', array());
		}
		
		/**
		 * Displays (and clears) the Admin Notices in the queue (Admin Only)
		 *
		 * An 'Admin Notice' is displayed on WordPress Admin pages to indicate the status
		 * of a certain action, such as saving the plugin options for example.
		 *
		 * Note: this is automatically called by the plugin, but is provided as an public
		 * function.
		 *
		 * @access public
		 */
		function display_admin_notices() {
			if ( !is_admin() ) return; // Admin only function

			$admin_notices = $this->get_var('_admin_notices');
			
			if ( $admin_notices ) {
				foreach ( $admin_notices as $notice )
					echo $notice;
			}
			
			$this->clear_admin_notices();
		}
			
		/**
		 * Returns the hash value of the current configuration or that of one specified
		 *
		 * @access public
		 * @param array $ext_hash An array containing options which are not yet saved
		 * @return string Hash value of the current configuration
		 */
		function get_options_hash($ext_hash=false) {
			if ( !$ext_hash ) {
				return md5(serialize($this->get_options()));
			} else {
				return md5(serialize($ext_hash));
			}
		}

		/**
		 * Generates an Ajax response
		 *
		 * Generates an Ajax response, encoding the data as JSON data. It will also 
		 * include a NONCE to protect the authenticity of the data.
		 *
		 * Note: this function will finish with a 'die()', therefore any functions
		 * placed after this function will not be executed.
		 *
		 * @access public
		 * @param mixed|string $data The data to return, or an error string as the message 
		 *		to be displayed if $is_error is true
		 * @param bool $is_error Optional parameter that if set to true will create an 
		 *		Ajax error response (uses $data as the error string)
		 * @return none
		 */
		function ajax_response($data, $is_error=false) {
			$out = array();
			
			$out['stat']	= ( $is_error ) ? 'fail' : 'ok';
			$out['data']	= $data;
			$out['nonce']	= wp_create_nonce($this->name . '-ajax-response');
			
			die ( json_encode($out) );
		}		

		/**
		 * Upgrades the plugin's options if required (or forcibly)
		 *
		 * This will upgrade the plugin's options based on the stored options' version
		 * or optionally forcibly. 
		 *
		 * It will use 'get_default_options()' as the callback to obtain the default plugin options.
		 *
		 * An 'on_options_created' event will be triggered if no options existed and were created,
		 * and an 'on_options_upgraded' event will be triggered if the existing options were upgraded.
		 *		 
		 * Note: automatically called when the plugin becomes activated.
		 * Note: As of 1.0.2, also automatically called _on_admin_register.
		 *
		 * @access public
		 * @param bool $force_upgrade Optional, if set to true will ignore version of stored options
		 * @see on_options_upgraded(), on_options_created()
		 * @return bool Returns false if there was an error upgrading the option, true if successful or not required to upgrade
		 */
		function upgrade_options($force_upgrade=false) {
			/* NOTE: Do NOT use $this->get_options() to avoid a potential loop (that function will call
			 * upgrade_options in case of an error)
			 */
			$plugin_options = get_option($this->name);

			// Only upgrade if the previous version does not match the current one, or if forced:
			if ( !$force_upgrade && 
				 is_array($plugin_options) &&
				 array_key_exists('version', $plugin_options) && 
				 version_compare($plugin_options['version'], $this->get_plugin_info('Version'), '>=') ) {
				return true;
			}

			// If we reach this point, we need to perform an upgrade
			$default_options = $this->get_default_options();
			$result = false;

			if ( $plugin_options === false ) {
				// Non-existent, so add it:
				
				// It MUST be an array, sorry.
				if ( !is_array($default_options) )
					$default_options = array();
					
				$default_options['version'] = $this->get_plugin_info('Version');
				
				add_option($this->name, $default_options);
				
				// Since add_option() simply returns a 'null', we check if we created the options properly:
				$plugin_options = get_option($this->name);
				if ( array_key_exists('version', $plugin_options) && 
					 ($plugin_options['version'] == $this->get_plugin_info('Version')) ) {
					$this->on_options_created();
					$this->on_options_changed();
					
					$result = true;
				}
			} else {
				// Plugin options are present, so upgrade each one:
				$this->_upgrade_option_rec($plugin_options, $default_options);	
				$plugin_options['version'] = $this->get_plugin_info('Version');
				$plugin_options['_is_upgraded'] = true;

				$result = update_option($this->name, $plugin_options);

				if ( $result ) {
					$this->on_options_upgraded();
					$this->on_options_changed();
				}
			}
			
			return $result;
		}
			
		/**
		 * Returns the active submenu (Admin Only)
		 *
		 * @access public
		 * @return string The active submenu
		 * @see get_submenus(), render_submenus(), set_active_submenu()
		 */
		function get_active_submenu() {
			if ( !is_admin() ) return ''; // Admin only function
			
			return $this->get_var('submenu');
		}
		
		/**
		 * Sets the activate submenu (Admin Only)
		 *
		 * Automatically called during _on_admin_load()
		 *
		 * @param string|bool $override_menu Optionally specify the submenu to activate, otherwise auto-detect if false
		 * @return string The active submenu
		 * @see get_submenus(), render_submenus(), get_submenus()
		 */
		function set_active_submenu($override_menu=false) {
			if ( !is_admin() ) return ''; // Admin only function
			
			if ( $override_menu !== false ) {
				$this->set_var('submenu', (string)$override_menu);
				return (string)$override_menu;
			}
			
			$data = array_merge($_GET, $_POST);
			$submenus =	$this->get_submenus();

			// Set the default menu:
			foreach ( $submenus as $submenu_name => $display_name ) {
				$this->set_var('submenu', (string)$submenu_name);
				break;
			}
			
			if ( isset($data['submenu']) ) {
				foreach ( $submenus as $submenu_name => $display_name ) {
					if ( (string)$submenu_name == (string)$data['submenu'] ) {
						$this->set_var('submenu', (string)$submenu_name);
						break;
					}
				}
			}
				
			// Returns a blank if no submenus available / active
			return $this->get_active_submenu();
		}
		
		/**
		 * Renders (displays) the submenu, if any (Admin Only)
		 *
		 * @access public
		 * @see get_submenus(), set_active_submenu(), get_active_submenu()
		 */
		function render_submenus() {
			if ( !is_admin() ) return; // Admin only function
			
			$submenus =	$this->get_submenus();
			
			if ( count($submenus) <= 0 ) return;
			
			$sub_links = array();
			$active_submenu = $this->get_active_submenu();
			
			foreach ( $submenus as $submenu_name => $display_name ) {
				if ( !empty($display_name) ) {
					$class = ( $active_submenu == (string)$submenu_name ) ? ' class="current"' : '';
					$sub_links[] = '<li><a href="' .  htmlspecialchars(add_query_arg(array('submenu'=>(string)$submenu_name), $this->get_admin_url())) . '"' . $class . '>' . $display_name . '</a>';
				}
			}
			
			echo '<ul class="subsubsub">' . implode( " |</li>", $sub_links ) . '</li></ul>';
		}
		
		/**
		 * Retrieves the details of currently installed plugin(s)
		 *
		 * Examples:
		 *
		 * To return all installed plugins, which are activated only:
		 * <code>
		 * $active_plugins_only = true;
		 * $plugins = $this->get_installed_plugin_info($active_plugins_only);
		 * foreach ($plugins as $plugin_filebase => $plugin_info) {
		 *   echo $plugin_info['Name'] . ' is installed at ' . $plugin_filebase;
		 * }
		 * </code>
		 *
		 * To return a particular field from an installed plugin, regardless if
		 * it is activated or not:
		 * <code>
		 * $description = $this->get_installed_plugin_info(false, 'Akismet', 'Description');
		 * echo 'The Aksimet plugin has the following description: "' . $desription . '"';
		 * </code>
		 *
		 * @access public
		 * @param bool $active_only Optionally filter plugins by those that are activated
		 * @param bool|string $name A string to optionally select a specific plugin by name (RegExp enabled, case insensitive)
		 * @param bool|string $field A string to optionally select a specific field (only valid if $name is set)
		 * @return array|string Details about all (or specified) plugin, or the specified plugin's field
		 * @see get_plugin_info(), is_plugin_installed()
		 */
		function get_installed_plugin_info($active_only=false, $name=false, $field=false) {
			$active_plugins = get_option('active_plugins');
			
			if ( $active_plugins === false )
				$active_plugins = array();
			
			// Create cached data for installed plugins if there's none yet:
			if ( !$this->_installed_plugins ) {
				require_once( ABSPATH . '/wp-admin/includes/admin.php' );
				$this->_installed_plugins = get_plugins();
			}
			
			$result = array();
			
			// Do we need to return all the installed plugins?
			if ( !$name ) {
				$result = $this->_installed_plugins;
				
				// Do we need to filter it by active plugins only?
				if ( $active_only ) {
					foreach ( $result as $plugin_file_base => $plugin_details ) {					
						if ( !in_array($plugin_file_base, $active_plugins) )
							unset ( $result[$plugin_file_base] );
					}
				}
			} else {		
				// Create a default result array:
				foreach ( $this->_default_plugin_headers as $plugin_header_name => $plugin_header_context )
					$result[$plugin_header_name] = '';
				
				// Find the specified plugin
				foreach ($this->_installed_plugins as $plugin_file_base => $plugin_details) {
					// If we need active plugins only and it's listed as active, or any plugin is fine,
					// run a preg_match to see if the $name matches:
					if ( ( ($active_only && in_array($plugin_file_base, $active_plugins)) || ( !$active_only ) ) &&
						  preg_match('/' . $name . '/i', $plugin_details['Name']) ) {
						$result = $plugin_details;
						$result['FileBase'] = $plugin_file_base;
						break;
					}
				}

				// Was a specific field specified, or should we return everything?
				if ( $field ) {
					if ( array_key_exists($field, $result) ) {
						$result = $result[$field];
					} else {
						$result = '';
					}
				}
			}
			
			return $result;
		}
		
		/**
		 * Checks if one or more plugins is installed
		 *
		 * If one name is given as a string, the function will simply return a true / false. If
		 * more than one / array is specified, it will return an array of the installed plugins or false
		 * if none were found.
		 *
		 * @access public
		 * @param string|array One or more names of plugins to check for installation (RegExp enabled, case insensitive)
		 * @param bool $active_only Only check the plugins that are activated
		 * @return bool|array (see long description)
		 * @see get_installed_plugin_info()
		 */
		function is_plugin_installed($name, $active_only=false) {
			if ( !is_array($name) ) {
				// We just need to find a single plugin
				$plugin = $this->get_installed_plugin_info($active_only, $name);
				return ( !empty($plugin['Name']) );
			} else {
				// We need to find one or more plugins
				$results = array ();
				
				foreach ($name as $single_name) {	
					$plugin = $this->get_installed_plugin_info($active_only, $single_name);
					
					if ( !empty($plugin['Name']) )
						$results[] = $plugin;
				}
				
				if ( count($results) == 0 ) {
					return false;
				} else {
					return $results;
				}
			}
		}
		
		/**
		Private Events
		**/
		
		/**
		 * Event triggered upon plugin activation (internal)
		 *
		 * @access private
		 * @see on_activation()
		 */
		function _on_activation() {
			if ( !$this->upgrade_options() ) 
				$this->add_admin_notice( sprintf( __('<strong>Critical:</strong> Unable to create the database options for <strong>%s</strong>', $this->name), $this->get_plugin_info('Name')), true );	
			
			$this->on_activation();
		}
		
		/**
		 * Registers the admin menu (internal)
		 *
		 * This will add the plugin to the Admin options page and set the 'on_admin_load()', 'on_admin_action()', 'on_admin_scripts()'
		 * as well as 'on_admin_styles()' events, which will only be triggered when this plugin options page 
		 * is visible thus avoiding unneccesary loading of scripts, etc.
		 *
		 * As of 1.0.2: An upgrade check will be done at this stage as well, as _on_activation is not always fired on updates, depending
		 * on the Wordpress version (2.8, 2.9 or 3.0+).
		 * 
		 * @access private
		 * @see on_admin_load(), on_admin_scripts(), on_admin_styles(), on_admin_action(), get_help_context()
		 */
		function _on_admin_register() {
			$this->options_page = add_options_page($this->get_plugin_info('Name'), $this->get_plugin_info('Name'), 'manage_options', $this->name, array($this, 'on_admin_render'));
			
			add_action('load-' . $this->options_page, array($this, '_on_admin_load'));
			add_action('admin_print_scripts-' . $this->options_page, array($this, '_on_admin_scripts'));
			add_action('admin_print_styles-' . $this->options_page, array($this, 'on_admin_styles'));
			
			if ( !$this->upgrade_options() ) 
				$this->add_admin_notice( sprintf( __('<strong>Critical:</strong> Unable to upgrade the database options for <strong>%s</strong>', $this->name), $this->get_plugin_info('Name')), true );			
		}
		
		/**
		 * Display the notices waiting in the queue (internal)
		 *
		 * This will display the 'Admin Notices' waiting in the queue. It will also display
		 * warnings (errors) if the minimum versions for WordPress and PHP as specified by this
		 * plugin are not matched.
		 *
		 * @access private
		 * @see display_admin_notices(), add_admin_notice()
		 */
		function _on_admin_notices() {
			global $wp_version;
			
			// Let the user know if the plugin was upgraded:
			$this->_is_upgraded();
			
			// Nag about versions:
			$this->_set_version_warning($this->get_plugin_info('MinWPVersion'), $wp_version, 'WordPress');
			$this->_set_version_warning($this->get_plugin_info('MinPHPVersion'), PHP_VERSION, 'PHP');		
		
			$this->display_admin_notices();
		}
		
		/**
		 * Add a quick link on the Plugins page (internal)
		 *
		 * This will provide a 'Settings' link on the Admin plugins page
		 *
		 * @access private
		 */
		function _on_plugin_action_links($actions) {
			array_unshift($actions, '<a href="' . $this->get_admin_url() .'" title="' . sprintf( __('Configure %s', $this->name), $this->get_plugin_info('Name') ) . '">' . __('Settings', $this->name) . '</a>');
				
			return $actions;
		}
		
		/**
		 * Adds a message after the plugin (internal)
		 *
		 * @access private
		 * @see get_after_plugin_text();
		 */
		function _on_after_plugin_row() {
			$text = $this->get_after_plugin_text();
			
			if ( !empty($text) )
				echo '<tr><td colspan="3"><div style="-moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; border:1px solid #DFDFDF; background-color:#F1F1F1; margin:5px; padding:3px 5px;">' . $text . '</div></td></tr>';
		}
		
		/**
		 * Process an Ajax call (internal)
		 *
		 * @access private
		 * @see on_ajax_request(), ajax_response()
		 */
		function _on_ajax_call() {
			check_ajax_referer($this->name . '-ajax-call');
			
			header('Content-type: application/json');
			
			if ( !isset($_POST['func']) ||
				 !isset($_POST['data']) ) 
				$this->ajax_response( __('Malformed Ajax Request', $this->name), true );
			
			$this->on_ajax_request( (string)$_POST['func'], $_POST['data'] );
			
			// Default response:
			$this->ajax_response( __('No Event Response', $this->name), true);
		}
		
		/**
		 * Event triggered when WordPress is ready to load Javascript (internal)
		 *
		 * @access private
		 * @see on_admin_scripts()
		 */
		function _on_admin_scripts() {
			echo (
				'<script type="text/javascript">' . PHP_EOL .
				'//<![CDATA[' . PHP_EOL . 
				'var ajaxaction = \'' . $this->name . '\';' .
				'var ajaxnonce = \'' . wp_create_nonce($this->name . '-ajax-call') . '\';' .
				'var ajaxnonceresponse = \'' . wp_create_nonce($this->name . '-ajax-response') . '\';' . PHP_EOL .
				'//]]>' . PHP_EOL .
				'</script>'
			);

			$this->on_admin_scripts();
		}
		
		/**
		 * Registers the public (non-admin) side events (internal)
		 *
		 * @access private
		 * @see on_public_scripts(), on_public_styles(), on_public_load()
		 */
		function _on_public_init() {
			if ( is_admin() ) return;

			add_action('wp_print_scripts',			array($this, 'on_public_scripts'));
			add_action('wp_print_styles',			array($this, 'on_public_styles'));
			add_action('wp_footer',					array($this, 'on_public_footer'));
			
			$this->on_public_load();
		}
		
		/**
		 * The internal admin loader determines the submenu selected, any waiting actions and
		 * sets the help context.
		 *
		 * @access private
		 * @see on_admin_load(), on_admin_action()
		 */
		function _on_admin_load() {
			$this->set_active_submenu();
			
			$action = '';
			$is_bulk_action = false;
			$data = array_merge($_GET, $_POST);
			
			if ( array_key_exists('bulk-action', $data) ) {
				if ( is_array($data['bulk-action']) ) {
					$idx = -1;
					
					// Check for a 'bulk-action-<index>'
					for ($i=0; $i < count($data['bulk-action']); $i++) {
						if ( array_key_exists('bulk-action-' . ($i + 1), $data) && !empty($data['bulk-action-' . ($i + 1)]) ) {
							$idx = $i;
							break;
						}
					}
					
					if ( ($idx < 0) ) {
						// If no 'bulk-action-<index>' was found, find the first non-empty bulk action
						foreach ( $data['bulk-action'] as $bulk_action ) {
							if ( !empty($bulk_action) ) {
								$action = (string)$bulk_action;
								break;
							}
						}
					} else {
						// A 'bulk-action-<index> was found, use the array value at this index as the action
						$action = (string)$data['bulk-action'][$idx];
					} // (!)$idx check
				} else {
					$action = (string)$data['bulk-action'];
				} // (!)is_array
				
				if ( !empty($action) )
					$is_bulk_action = true;
			} // array_key_exists 'bulk-action'
			
			// No bulk action specified, check for a single action
			if ( !$is_bulk_action && array_key_exists('action', $data) )
				$action = (string)$data['action'];
			
			// Trigger the on_admin_action:
			if ( !empty($action) && check_admin_referer($this->name) )
				$this->on_admin_action($action, $is_bulk_action, $data);
			
			// Trigger the on_admin_load event
			$this->on_admin_load();
			
			// Set the help context. It's here in case on_admin_load does some last-minute stuff with the submenu.
			$help_context = $this->get_help_context($this->get_active_submenu());
			if ( !empty($help_context) )
				add_contextual_help($this->options_page, $help_context);			
		}
		
		/**
		Public Global Events. This is where a plugin really does all its work.
		**/

		/**
		 * Event triggered when the plugin is ready for initialisation
		 *
		 * This event is triggered whenever the plugin is ready to do additional initialisation,
		 * such as adding global wp_footer actions, initialise certain variables, etc.
		 *
		 * @access public
		 * @abstract
		 */
		function on_init() {}

		/**
		 * Event triggered when the plugin class is about to be destroyed.
		 *
		 * @access public
		 * @abstract
		 */
		function on_deinit() {}
		
		/**
		 * Event triggered when the plugin is activated
		 *
		 * This event is triggered when the plugin has been activated on the Admin Plugin page
		 *
		 * @access public
		 * @abstract
		 */
		function on_activation() {}
		
		/**
		 * Event triggered when the plugin options have been created
		 *
		 * This event is triggered when the plugin has successfully created the default options, usually
		 * at installation / activation of the plugin.
		 *
		 * @access public
		 * @abstract
		 * @see on_options_upgraded(), on_activation()
		 */
		function on_options_created() {}
		
		/**
		 * Event triggered when the plugin options have been upgraded
		 *
		 * This event is triggered when existing options have been upgraded to the current version. This
		 * usually occurs during a plugin upgrade / re-activation by WordPress or manually.
		 *
		 * @access public
		 * @abstract
		 * @see on_options_created, upgrade_options()
		 */		 
		function on_options_upgraded() {}
		
		
		/**
		 * Event triggered whenever the options have been changed in the database
		 *
		 * @access public
		 * @abstract
		 */
		function on_options_changed() {}
		
		/**
		Public Admin Events
		**/
		
		/**
		 * Event triggered when the Admin options page begins loading
		 *
		 * This event is triggered when the Admin options page begins loading, which is a good opportunity
		 * to handle $_POST and $_GET requests, saving form data, etc.
		 *
		 * Note: it will not be triggered on the public side, and only for the pages directly related to this plugin
		 *
		 * @access public
		 * @abstract
		 */
		function on_admin_load() {}
		
		/**
		 * Event triggered when an valid action is requested
		 *
		 * An 'action' can be triggered by seting a POST/GET 'action' or 'bulk-action' in a form or URL. 
		 *
		 * If a 'bulk-action' is specified, it will check if it is an array and takes as $action either:
		 *
		 * - the first non-empty value (UNSAFE)
		 * - the value at index 'bulk-action-<index>' (1-based [1, 2, 3, ...] and safe)
		 * 
		 * Otherwise $action will be the verbatim value of 'bulk-action'.
		 *
		 * Example of a bulk action that can exist more than once on a page:
		 * <code>
		 * <select name="bulk-action[]">
		 *   <option value="">Select a bulk action:</option>
		 *   <option value="delete-selected">Delete Selected</option>
		 * </select>
		 * <input type="submit" name="bulk-action-1" value="Apply">
		 * </code>
		 *
		 * Examply of a bulk action that can exist only once:
		 * <code>
		 * <input type="submit" name="bulk-action" value="Delete Selected" />
		 * </code>
		 *
		 * If an 'action' is specified, its value will be used verbatim for $action. However, a
		 * bulk action will take priority (that is, if both 'bulk-action' and 'action' were specified and
		 * both had values, then the bulk-action will be passed as $action)
		 *
		 * Notes: 
		 * - This event will not be triggered if it fails the NONCE test.
		 * - It will not verify the user privileges ( current_user_can() )
		 * - A POST action takes priority over a GET action
		 *
		 * @access public
		 * @param string $action Action requested
		 * @param bool $is_bulk_action Will be set to true if it is a bulk action, false otherwise
		 * @param array $data Data submitted with the action
		 * @abstract
		 */
		function on_admin_action($action, $is_bulk_action, $data) {}
		
		/**
		 * Event triggered when queueing Admin Javascripts
		 *
		 * This event is triggered when the Admin options page is ready to load Javascript.
		 *
		 * Note: it will not be triggered on the public side, and only for the pages directly related to this plugin
		 *
		 * @access public
		 * @abstract
		 */
		function on_admin_scripts() {}
		
		/**
		 * Event triggered when queueing Admin Style sheets
		 *
		 * This event is triggered when the Admin options page is ready to load Style sheets.
		 *
		 * Note: it will not be triggered on the public side, and only for the pages directly related to this plugin
		 *
		 * @access public
		 * @abstract
		 */
		function on_admin_styles() {}
		
		/**
		 * Event triggered when this plugin's admin page needs to be rendered
		 *
		 * This event is triggered when the Admin options page needs to be rendered (displayed) to the user
		 *
		 * @access public
		 * @abstract
		 */
		function on_admin_render() {}
		
		/**
		 * Handle an Ajax request
		 *
		 * This will process an Ajax call for this plugin. The 'ajax_response()' function
		 * must be used to return any data, otherwise a 'No Event Response' error will be
		 * returned to the Ajax caller.
		 *
		 * It will verify the NONCE prior to triggering this event. If it fails this
		 * verification, an error will be returned to the Ajax caller
		 *
		 * Example:
		 * <code>
		 * $.ajax({
		 *   type : 'POST',
		 *   dataType : 'json',
		 *   url : ajaxurl,
		 *   timeout : 30000,
		 *   data : {
		 *     action: ajaxaction,
		 *     func: 'say',
		 *     data: 'Hello World',
		 *     _ajax_nonce: ajaxnonce
		 *    },
		 *    success : function(resp){
		 *      if (resp.nonce != ajaxnonceresponse) {
		 *        alert ('The Ajax response could not be validated');
		 *        return;
		 *     }
		 * 	
		 *     if (resp.stat == 'fail') {
		 *       alert ('There was an error: ' + resp.data);
		 *     } else {
		 *       alert (resp.data);
		 *     }
		 *   },
		 *   error : function(err){
		 *     alert ('There was an error obtaining the Ajax response');
		 *   }
		 * });
		 * </code>
		 *
		 * Wherein the plugin code, the on_ajax_request() event is:
		 * <code>
		 * function on_ajax_request( $func, $data ) {
		 *  if ( $func == 'say' ) {
		 *    $this->ajax_response($data);
		 *  } else {
		 *    $this->ajax_response('I don\'t know what to do!', true);
		 *  }
		 * }
		 * </code>
		 *
		 * @access public
		 * @abstract
		 * @param string $action Action to take according to Ajax caller
		 * @param mixed $data UNSANITIZED data sent by the Ajax caller
		 * @see _on_ajax_call(), ajax_response()
		 */
		function on_ajax_request($action, $data) {}
		
		/**
		Public Non-admin Events
		**/
		
		/**
		 * Event triggered when a public side starts to load
		 *
		 * This event is triggered when the public side of the WordPress website is about to load
		 *
		 * Note: This event is only triggered on the public side (non-Admin pages)
		 *
		 * @access public
		 * @abstract
		 */
		function on_public_load() {}
		
		/**
		 * Event triggered when queueing Javascripts on the public side
		 *
		 * This event is triggered when WordPress is ready to load / queue Javascript for the public (non-Admin)
		 * side.
		 *
		 * Note: This event is only triggered on the public side (non-Admin pages)
		 *
		 * @access public
		 * @abstract
		 */
		function on_public_scripts() {}
		
		/**
		 * Event triggered when queueing Style sheets on the public side
		 *
		 * This event is triggered when WordPress is ready to load / queue Style sheets for the public (non-Admin)
		 * side.
		 *
		 * Note: This event is only triggered on the public side (non-Admin pages)
		 *
		 * @access public
		 * @abstract
		 */
		function on_public_styles() {}
		
		/**
		 * Event triggered when the footer is about to be displayed on the public side
		 *
		 * Prior to WordPress 2.7 this event is dependent on the 'wp_footer' to be included 
		 * in the theme, though this is most often the case
		 */
		function on_public_footer() {}

		/**
		Callbacks
		**/
		
		/**
		 * Callback that returns an array with the default options for this plugin
		 *
		 * Reserved options (may be used, but not modified):
		 * - 'version'
		 * - '_is_upgraded'
		 *
		 * @access public
		 * @abstract
		 * @return array An array with the default options for the plugin
		 * @see upgrade_options()
		 */
		function get_default_options() {
			return array();
		}
		
		/**
		 * Callback that returns text or HTML code to display in the Admin options page 'Help' tab
		 *
		 * @access public
		 * @abstract
		 * @param string $active_submenu Provides the name of the active submenu (blank if no submenus are specified)
		 * @return string Text or HTML to display in the Admin options page 'Help' tab
		 * @see _on_admin_register()
		 */
		function get_help_context($active_submenu='') {
			return '';
		}
		
		/**
		 * Callback that returns the text to be displayed after the plugin listing
		 *
		 * Note: This is only called if the plugin is active.
		 *
		 * @access public
		 * @abstract
		 * @return string Text or HTML to display after the plugin listing or blank for none
		 * @see _on_after_plugin_row()
		 */
		function get_after_plugin_text() {
			return '';
		}
		
		/**
		 * Callback function that retrieves the sub-menus
		 *
		 * The callback should return an array containing 'name' => 'Display Name' pairs. If
		 * the 'Display Name' value is blank (''), then it will not be rendered on screen, but
		 * is considered a valid option to be passed as the 'submenu' URI component or POST field.
		 *
		 * @access public
		 * @abstract
		 * @return array An array containing 'name'=>'Display Name' pairs with the first one being the default submenu.
		 * @see render_submenus()
		 */
		function get_submenus() {
			return array ();
		}
	}
	
} // !class_exists

//-- That's right, no closing tag!