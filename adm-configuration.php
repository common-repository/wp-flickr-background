<?php 
/*
PLEASE DO NOT DELETE THE FOLLOWING LINE AND LEAVE IT AT THE TOP:
*/
defined('WP_ADMIN') or die ('Restricted Access');

/**
 * Admin include file for plugin configuration
 *
 * Called by wpFlickrBackground::on_admin_render()
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
 * @subpackage adminIncludes
 * @author Mike Green (Myatu) <me@myatus.co.uk>
 * @copyright Copyright 2010-2011 Mike Green (Myatu)
 * @license http://www.gnu.org/licenses/gpl.html
 * @link http://www.myatus.co.uk
 * @version $Id: adm-configuration.php 369209 2011-04-05 19:05:16Z Myatu $ 
 */
 
$image_alignment_text = array(
		__('Top Left', $this->name),
		__('Top Right', $this->name), 
		__('Bottom Left', $this->name), 
		__('Bottom Right', $this->name)
	);

/**
 * Predefined change frequencies
 */
$change_freq_options = array(
		array('desc' => __('browser session', $this->name),	'val' => ''),
		array('desc' => __('page (re)load', $this->name),	'val' => '0'),		// Since 1.0.4
		array('desc' => __('5 minutes', $this->name),		'val' => '5'),		// Since 1.0.4
		array('desc' => __('15 minutes', $this->name),		'val' => '15'),
		array('desc' => __('1 hour', $this->name),			'val' => '60'),
		array('desc' => __('3 hours', $this->name),			'val' => '180'),
		array('desc' => __('12 hours', $this->name),		'val' => '720'),
		array('desc' => __('day', $this->name),				'val' => '1440'),
		array('desc' => __('week', $this->name),			'val' => '10080'),
		array('desc' => __('month', $this->name),			'val' => '43200')
	);						

/**
Figure out which WordPress Cache is installed
**/

$installed_wordpress_cache = false;

// Find one of the known WordPress caches among the active installed plugins:
$_installed_wordpress_caches = $this->is_plugin_installed(array(
		'WP Super Cache',
		'WP-Cache',
		'cos-html-cache',
		'Plugin Output Cache',
		'Hyper Cache',
		'W3 Total Cache'
	), true);

// If we found one or more installed WordPress caches, pick the first one:
if ( $_installed_wordpress_caches )
	$installed_wordpress_cache = $_installed_wordpress_caches[0]['Name'];
?>

<div id="adm-items" class="metabox-holder has-right-sidebar">
	<div id="side-info-column" class="inner-sidebar">
		<div id="side-sortables" class="meta-box-sortables ui-sortable">
			<div id="hi-box" class="postbox">
				<div title="Click to toggle" class="handlediv"><br /></div>
				<h3 class="hndle"><span><?php _e('Thanks!'); ?></span></h3>
				<div class="inside">
					<?php printf( __('<p>Thanks for using <strong>%s</strong>!</p><p>If you really like it, you could:</p>', $this->name), $this->get_plugin_info('Name') ); ?>
					<ul class="about_list">
						<li><?php printf( __('<a href="%s">Link to it</a> so others can find out about it,', $this->name), $this->get_plugin_info('PluginURI') ); ?></li>
						<li><?php _e('Give it a good rating on <a href="http://wordpress.org/extend/plugins/wp-flickr-background/">WordPress.org</a>, or', $this->name); ?></li>
						<li>
							<?php _e('Consider a small donation as a token of your appreciation:', $this->name); ?>
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
								<input type="hidden" name="cmd" value="_s-xclick" />
								<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----" />
								<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donate_SM.gif" name="submit" alt="PayPal - The safer, easier way to pay online." />
								<img alt="" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1" />
							</form>
						</li>
					</ul>
				</div>
			</div>
			

			<?php
				// Display the config hash file tip if it isn't writeable and the "cacheable" option is disabled (not used when enabled)
				if ( $this->_has_writeable_config_hash_file() === false &&
					   ( !isset($plugin_options['is_cacheable']) || !$plugin_options['is_cacheable']) ) { 
			?>
			<div id="another-hi-box" class="postbox">
				<div title="Click to toggle" class="handlediv"><br /></div>
				<h3 class="hndle"><span><?php _e('Tip!', $this->name); ?></span></h3>
				<div class="inside">				
					<p>
						<?php printf( __('Did you know you could make %s load faster? Simply create a writable <code>.confighash</code> in the plugin\'s directory!', $this->name), $this->get_plugin_info('Name') ); ?>
					</p>
					<?php printf( __('<p>If you are using Linux or a similar operating system, this can be done using the following two commands (actual directory path may vary): </p>'.
						'<ul>' .
							'<li><code>touch %1$s</code></li>' .
							'<li><code>chmod 666 %1$s</code></li>' .
						'</ul>', $this->name), $this->get_plugin_dir() . '.confighash' ); ?>
				</div>
			</div>
				
			<?php } ?>					
		</div>
	</div>
	
	<form method="post" action="<?php echo $this->get_admin_url(); ?>">
	<?php wp_nonce_field($this->name); ?>
	<input type="hidden" name="action" value="update-config" />
	
	<div id="post-body" class="has-sidebar">
		<div id="post-body-content" class="has-sidebar-content">				
			<div id="normal-sortables" class="meta-box-sortables ui-sortable">
				<div id="gen-options" class="postbox">
					<div title="Click to toggle" class="handlediv"><br /></div>
					<h3 class="hndle"><span><?php _e('General Configuration', $this->name); ?></span></h3>
					<div class="inside">
						<table class="form-table">
							<tr valign="top">
								<th scope="row"><?php _e('Active Gallery', $this->name); ?></th>
								<td>
									<select id="active_gallery" name="active_gallery">
										<?php
											foreach ($plugin_options['galleries'] as $gallery_idx => $gallery) {
												$selected = ( $gallery_idx == $plugin_options['active_gallery'] ) ? 'selected="selected"' : '';
												
												echo('<option value="' . $gallery_idx . '" ' . $selected . '>' . $gallery['name'] . '</option>');
											}
										?>
									</select>
									<?php $this->tooltip( __('<p>This option allows you to select a gallery from which the plugin will randomly select photos</p>'.
										'<p>These random photos will be displayed as the background for the active WordPress theme.</p>') ); ?>
								</td>
							</tr>
							
							<tr valign="top">
								<th scope="row"><?php _e('Change Frequency', $this->name); ?></th>
								<td>
									<?php _e('Change the background every', $this->name); ?>
									<select id="change_freq" name="change_freq">
										<?php
											foreach ($change_freq_options as $freq_option) {
												$selected = ( $freq_option['val'] == $plugin_options['change_freq'] ) ? 'selected="selected"' : '';
												
												echo('<option value="' . $freq_option['val'] . '" ' . $selected . '>' . $freq_option['desc'] . '</option>');
											}
										?>
									</select>
									<?php $this->tooltip( __('<p>With this option you can determine how often the background should change for the visitor.</p>'.
										'<p>By default the background will remain the same until the visitor exits the browser (a browser session).</p>') ); ?>
								</td>
							</tr>		
						</table>
					</div>
				</div>
			
			
				<div id="img-options" class="postbox">
					<div title="Click to toggle" class="handlediv"><br /></div>
					<h3 class="hndle"><span><?php _e('Background Image Configuration', $this->name); ?></span></h3>
					<div class="inside">
						<p>
							<?php _e(
								'For most configurations, stretching the image horizontally and bottom-left alignment gives the best results. However, you may need to adjust ' .
								'the image stretching and and alignment below. If the image is stretched both vertically <i>and</i> horizontally, then the ' .
								'image alignment option will be ignored.'); ?>
						</p>
						
						<table class="form-table">		
							<tr valign="top">
								<th scope="row"><?php _e('Stretch Image', $this->name); ?></th>
								<td>
									<ul class="stretch-images">
										<li>
											<div class="stretch-img stretch-img-v"> </div>
											<br />
											<input type="checkbox" id="image_v_stretch" name="image_v_stretch" value="true" <?php if ( isset($plugin_options['image_v_stretch']) && $plugin_options['image_v_stretch'] ) echo 'checked="checked"'; ?> />
											<label for="image_v_stretch"><?php _e('Strech Vertically', $this->name); ?></label>
										</li>
										<li>
											<div class="stretch-img stretch-img-h"> </div>
											<br />
											<input type="checkbox" id="image_h_stretch" name="image_h_stretch" value="true" <?php if ( isset($plugin_options['image_h_stretch']) && $plugin_options['image_h_stretch'] ) echo 'checked="checked"'; ?> />
											<label for="image_h_stretch"><?php _e('Strech Horizontally', $this->name); ?></label>
										</li>					
									</ul>
								</td>
							</tr>
							
							<tr valign="top">
								<th scope="row"><?php _e('Image Alignment', $this->name); ?></th>
								<td>
									<?php
										for ($i=0; $i<4; $i++) {
											if ( $i % 2 == 0 ) { 
												echo '<ul class="alignment-images">';
											}
									?>
											
											<li class="align-desc-<?php echo $i;?>">
												<div class="align-img align-img-<?php echo $i;?>"> </div>
												<br />
												<input type="radio" name="image_alignment" value="<?php echo $i;?>" id="image_alignment_<?php echo $i;?>" <?php if ( $plugin_options['image_alignment'] == $i ) echo 'checked="checked"';?> />
												<label for="image_alignment_<?php echo $i; ?>"><?php echo $image_alignment_text[$i]; ?></label>
											</li>
										
									<?php
											if ( $i % 2 != 0 ) {
												echo '</ul>';
											}
										} 
									?>
								</td>
							</tr>		
						</table>
					</div>
				</div>
				
				<div id="adv-options" class="postbox closed">
					<div title="Click to toggle" class="handlediv"><br /></div>
					<h3 class="hndle"><span><?php _e('Advanced Configuration', $this->name); ?></span></h3>
					<div class="inside">			
						<table class="form-table">
							<tr valign="top">
								<th scope="row"><?php _e('Theme Background', $this->name); ?></th>
								<td>
									<input type="checkbox" id="disable_orig_bg" name="disable_orig_bg" value="true" <?php if ( isset($plugin_options['disable_orig_bg']) && $plugin_options['disable_orig_bg'] ) echo 'checked="checked"'; ?> />
									<label for="disable_orig_bg">
										<?php printf( __('Disable the <strong>%s</strong> theme background.', $this->name), get_current_theme()); ?>
									</label>
									<?php 
										$this->tooltip(__('<p>This option will prevent the background image of the active WordPress theme from loading, which would otherwise be redundant with this plugin.</p>' .
											'<p>It will <strong>not modify</strong> the actual theme theme itself and therefore is not guaranteed to work with all WordPress themes.</p>')); 
									?>
								</td>
							</tr>
							
							<tr valign="top">
								<th scope="row"><?php _e('Javascript Compression', $this->name); ?></th>
								<td>
									<input type="checkbox" id="pack_js" name="pack_js" value="true" <?php if ( isset($plugin_options['pack_js']) && $plugin_options['pack_js'] ) echo 'checked="checked"'; ?> />
									<label for="pack_js">
										<?php _e('Enable Javascript compression.', $this->name); ?>
									</label>
									<?php 
										$this->tooltip(__('<p>Enabling Javascript compression can reduce the amount of time it takes for the visitor to download the plugin\'s Javascript code.</p>'.
											'<p>If your visitors are experiencing problems viewing the background images or are receiving browser error messages, you may wish to disable Javascript compression.</p>')); 
									?>
								</td>
							</tr>		
							
							<tr valign="top">
								<th scope="row"><?php _e('Cacheable', $this->name); ?></th>
								<td>
									<input type="checkbox" id="is_cacheable" name="is_cacheable" value="true" <?php if ( isset($plugin_options['is_cacheable']) && $plugin_options['is_cacheable'] ) echo 'checked="checked"'; ?> />
									<label for="is_cacheable">
										<?php
											if ( !$installed_wordpress_cache ) {
												_e('Allow the plugin to be cacheable by a WordPress cache such as <strong>WP Super Cache</strong>.');
											} else {
												printf( __('Allow the plugin to be cacheable by <strong>%s</strong>.', $this->name), $installed_wordpress_cache);
											}
										?>
									</label>
									<?php 
										$this->tooltip(__('<p>By default this plugin will call an external Javascript and style sheet, which are not cached by most WordPress caches.</p>' .
											'<p>Enabling this option will embed said Javascript and style sheet directly inside a requested web page, allowing it to be cached.</p>' .
											'<p>A disadvantage is that the a visitor may need to wait an extended period before any changes to this plugin\'s configuration or galleries become apparent.</p>'.
											'<p>However, it may significantly speed up the website response time under heavy traffic loads.</p>')); 
									?>
								</td>
							</tr>
							
							<tr valign="top">
								<th scope="row"><?php _e('License and Attribution', $this->name); ?></th>
								<td>
									<input type="checkbox" id="hide_lic_attr" name="hide_lic_attr" value="true" <?php if ( isset($plugin_options['hide_lic_attr']) && $plugin_options['hide_lic_attr'] ) echo 'checked="checked"'; ?> />
									<label for="hide_lic_attr">
										<?php _e('Hide the license and attribution information shown in the footer.', $this->name); ?>
									</label>
									<?php 
										$this->tooltip(__('<p>By default a small box containing the title, owner\'s name and the license of the photo is displayed in the footer.</p>' .
											'<p>Enabling this option will hide the information, however <strong>you are responsible for making use of photos in compliance with the photo owner\'s requirements or restrictions</strong>.</p>')); 
									?>
								</td>
							</tr>
							
							<tr valign="top">
								<th scope="row"><?php _e('License Location', $this->name); ?></th>
								<td>
									<input type="radio" name="license_location" id="license_location_bl" value="left" <?php if ( $plugin_options['license_location'] == 'left' ) echo 'checked="checked"';?> />
									<label for="license_location_bl"><?php _e('Bottom Left'); ?></label>
									<input type="radio" name="license_location" id="license_location_br" value="right" <?php if ( $plugin_options['license_location'] == 'right' ) echo 'checked="checked"';?> />
									<label for="license_location_br"><?php _e('Bottom Right'); ?></label>

									<?php 
										$this->tooltip(__('<p>This determines where in the footer the license and attribution information should appear.</p><p>If the license and attribution is hidden, then this option will be ignored.</p>')); 
									?>
								</td>
							</tr>								
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div style="clear:both;"></div>
	
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>
	</form>
</div>