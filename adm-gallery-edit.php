<?php 
/*
PLEASE DO NOT DELETE THE FOLLOWING LINE AND LEAVE IT AT THE TOP:
*/
defined('WP_ADMIN') or die ('Restricted Access');

/**
 * Admin include file for editing a single gallery
 *
 * Called by wpFlickrBackground::on_admin_render()
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
 * @subpackage adminIncludes
 * @author Mike Green (Myatu) <me@myatus.co.uk>
 * @copyright Copyright 2010-2011 Mike Green (Myatu)
 * @license http://www.gnu.org/licenses/gpl.html
 * @link http://www.myatus.co.uk
 * @version $Id: adm-gallery-edit.php 369209 2011-04-05 19:05:16Z Myatu $ 
 */

$is_new = ( !isset($_GET['idx']) || !array_key_exists((int)$_GET['idx'], $plugin_options['galleries']) );
$edit_id = ( !$is_new ) ? (int)$_GET['idx'] : -1;

/**
 * Outputs a keyed value from an (indexed) array if it exists (internal)
 *
 * @access private
 * @param array $data (Indexed) array containing key/value pairs from which the value needs to be extracted
 * @param string $key The key for the value to extract
 * @param int $idx Optional index specifier if the $data array is indexed (ie.: $data['galleries'][$idx])
 * @return string The keyed value if it exists, or an empty string otherwise
 * @see _eov()
 */
function __ov($data, $key, $idx=-1) {
	$res = '';
	
	if ( $idx >= 0 ) {
		if ( array_key_exists($idx, $data) &&
		     array_key_exists($key, $data[$idx]) ) {
			$res = urldecode($data[$idx][$key]);
		}
	} else {
		if ( array_key_exists($key, $data) )
			$res = urldecode($data[$key]);
	}
	
	return $res;
}

/**
 * Echoes the output of a keyed value from an (indexed) array (internal)
 *
 * @access private
 * @param array $data (Indexed) array containing key/value pairs from which the value needs to be extracted
 * @param string $key The key for the value to extract
 * @param int $idx Optional index specifier if the $data array is indexed (ie.: $data['galleries'][$idx])
 * @see __ov()
 */
function _eov($data, $key, $idx=-1) {
	echo __ov($data, $key, $idx);
}

/**
 * Replaces the apostrophe (') with an prime (minute divider) character (internal)
 *
 * This is used by the Thickbox 'hack' (setting the title to include HTML)
 *
 * @access private
 * @param string $str The string for which the apostrophe needs to be replaced
 * @return string A string with the apostrophe replaced by a prime character
 */
function _sa($str) {
	return preg_replace('/(\&#039;|\')/', '&prime;', $str);
}

/**
 * Generates a fieldset containing the details of a single photo
 *
 * The fieldset has a class, 'photo_<id>', which is shared with the visible photo/thumbnail
 * to ease the Javascript/JQuery code for adding/deleting a photo to the gallery.
 *
 * @access private
 * @param array $data Array containing data about the photo
 * @see wpFlickrBackground::get_default_options()
 */ 
function _photo_fields($data=array()) {
?>
	<fieldset class="photo_<?php _eov($data, 'id');?>">
		<input type="hidden" name="photos_id[]" value="<?php _eov($data, 'id');?>" />
		<input type="hidden" name="photos_title[]" value="<?php _eov($data, 'title');?>" />
		<input type="hidden" name="photos_owner[]" value="<?php _eov($data, 'owner');?>" />
		<input type="hidden" name="photos_photopage[]" value="<?php _eov($data, 'photopage');?>" />
		<input type="hidden" name="photos_thumbnail[]" value="<?php _eov($data, 'thumbnail');?>" />
		<input type="hidden" name="photos_background[]" value="<?php _eov($data, 'background');?>" />
		<input type="hidden" name="photos_licensename[]" value="<?php _eov($data, 'licensename');?>" />
		<input type="hidden" name="photos_licenseurl[]" value="<?php _eov($data, 'licenseurl');?>" />
	</fieldset>
<?php
}

/**
 * Generates a single photo box
 *
 * A 'photo box' is a visible thumbnail with a checkbox for bulk actions, a 'new photo' indicator,
 * as well as links to preview or delete the photo from the gallery.
 *
 * @access private
 * @param object $_owner Exposes the underlying class to this nested function
 * @param array $data Array containing data about the photo
 */
function _photo_box($_owner, $data=array()) {
	$_title = __ov($data, 'title') . ' ' . __('by', $_owner->name) . ' ' . __ov($data, 'owner');
	
	// This is a bit of a hack, but it works. Left jumbled to keep in line with JS.
	$_thickboxtitle = htmlspecialchars(
		'<strong>' . __ov($data, 'title') . '</strong><br /> ' . __('Photo by', $_owner->name) . ' <a href="' . __ov($data, 'photopage') . '" onclick="return ! window.open(this.href);">' . __ov($data, 'owner') . '</a>' .
		' (' . __('License', $_owner->name) . ': <a href="' . __ov($data, 'licenseurl') . '" onclick="return ! window.open(this.href);">' . _sa(__ov($data, 'licensename')) . '</a>)'
	);
	
?>
	<li class="photo_box photo_<?php _eov($data, 'id');?>" id="photo_<?php _eov($data, 'id');?>">
		<div class="thumbnail">
			<div class="overlay_new"><img src="<?php echo $_owner->get_plugin_url(); ?>img/new-photo-indicator.png" title="New Photo" alt="New Photo" /></div>
			<input type="checkbox" name="checked[]" class="overlay_checkbox" value="" />
			<a href="<?php _eov($data, 'background'); ?>" title="<?php echo $_thickboxtitle; ?>" class="thumbnail_link thickbox"><img src="<?php _eov($data, 'thumbnail');?>" alt="Thumbnail" class="thumbnail_image" title="<?php echo $_title; ?> (<?php _eov($data, 'licensename'); ?>)" /></a>
			<div class="overlay_actions">
				<span class="photo_preview_link"><?php _e('Preview', $_owner->name); ?></span>
				|
				<span class="photo_delete_link"><?php _e('Delete', $_owner->name); ?></span>
			</div>
		</div>
	</li>
<?php	
}
?>

<form method="post" id="gallery_form" action="#">
	<?php wp_nonce_field($this->name); ?>
	
	<input type="hidden" name="paged" value="<?php echo $this->get_var('paged'); ?>" />
	<?php if ( !$is_new ) { ?>
		<input type="hidden" name="idx" value="<?php echo $edit_id; ?>" />
		<input type="hidden" name="action" value="save-gallery" />
		
		<?php
			foreach ( $plugin_options['galleries'][$edit_id]['photos'] as $photo )
				_photo_fields($photo);
		?>
	<?php } else { ?>
		<input type="hidden" name="action" value="add-gallery" />
	<?php } ?>	

	<table class="form-table">
	
		<tr valign="top">
			<th scope="row"><?php _e('Gallery Name', $this->name); ?></th>
			<td>
				<input type="text" name="name" id="gallery_name" value="<?php _eov($plugin_options['galleries'], 'name', $edit_id);  ?>" class="regular-text" />
				<span class="description"><?php _e('Enter a short name for this galllery', $this->name); ?></span>
			</td>
		</tr>
	 
		<tr valign="top">
			<th scope="row"><?php _e('Description', $this->name); ?></th>
			<td>
				<textarea name="desc" class="large-text" cols="50" rows="3"><?php _eov($plugin_options['galleries'], 'desc', $edit_id);  ?></textarea>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><?php _e('Custom CSS Styling', $this->name); ?></th>
			<td>
				<p>
					<label for="customcss">
					<?php _e('You can optionally provide additional CSS Styling Code that will be loaded when this gallery is active or previewed. ' .
						     'As an example, this could be used to color-match the WordPress theme with the colors of the photos.', $this->name); ?>
					</label>
				</p>
				<p>
					<textarea id="customcss" name="customcss" class="large-text code" cols="50" rows="3"><?php _eov($plugin_options['galleries'], 'customcss', $edit_id);  ?></textarea>
				</p>
			</td>
		</tr>
		
	</table>
	
	<h3><?php _e('Photos in this gallery', $this->name); ?></h3>
	
	<div class="tablenav">
		<div class="alignleft actions">
			<select id="bulk_action">
				<option value="" selected="selected"><?php _e('Bulk Actions', $this->name); ?></option>
				<option value="delete-selected"><?php _e('Delete Selected', $this->name); ?></option>
			</select>
			<input type="button" id="bulk_apply_button" value="<?php _e('Apply', $this->name); ?>" class="button-secondary action" />		
			|
			<input id="photo_url" type="text" name="photo_url" value="" class="regular-text code" />
			<img src="<?php echo $this->get_plugin_url(); ?>img/ajax-loader.gif" id="ajax_loading" alt="Loading..."/>
			<input type="button" id="add_photo_button" value="<?php _e('Add Flickr Photo', $this->name);?>" class="button-secondary action" />
			<?php $this->tooltip(__('<p>Adding a Flickr photo is simple. Visit <strong>Flickr</strong> and search for photo\'s that interest you. '.
				'Once you have found the desired photo, click the <strong>Share This</strong> button above that photo and select <strong>Grab the link</strong>. '.
				'Then just copy &amp; paste the link into the provided field here and click the <strong>Add Flickr Photo</strong> button.</p>'.
				'<p><i>Tip: With many browsers you can also drag a Flickr photo directly onto the provided field, which will automatically fill in the URL for you.</i></p>'
			), 'How?'); ?>
			
			<?php if ( current_user_can('upload_files') ) { ?>
			|
			<input type="button" id="add_library_photo_button" value="<?php _e('Add Media Library Photo', $this->name);?>" class="button-secondary action thickbox" alt="<?php echo esc_url( get_upload_iframe_src('image') ); ?>" />
			<?php $this->tooltip(__('<p>You can upload your own photos using the <strong>Add Media Library Photo</strong> button. It works exactly as '.
				'adding a photo to a blog post. Just ensure you use the <u>full size</u> before you select <strong>Insert into Post</strong>.</p>'
			), 'How?'); ?>			
			<?php } ?>
		</div>		
	</div>
	
	<div id="ajax_notice" style="display:none;clear:both;"> </div>
	
	<div class="photo_placeholder">
		<div id="no_photos" class="no_photos_message">
			<h2><?php _e('You do not have any photos in this gallery yet.', $this->name);?></h2>
			<p>			
				<label for="photo_url">
					<?php _e('To start adding photos to this gallery, enter a Flickr Photo link in the provided field above and click the <strong>Add Flickr Photo</strong> button, or click the <strong>Add Media Library Photo</strong> to upload your own photos.', $this->name);?>
				</label>
			</p>
			<div class="notice">
			<h3>Notice!</h3>
				<?php _e('<p>Please note that you are responsible for using photos in compliance with the photo owner\'s requirements and restrictions.</p>' .
					'<p>If you use Flickr photos for commercial purposes, the photos must be marked with a Creative Commons license that allows for such use, '.
					'unless otherwise agreed upon between you and the owner. You can read more about this here: ' .
					'<a href="www.creativecommons.org" onclick="return ! window.open(this.href);">www.creativecommons.org</a> or <a href="www.flickr.com/creativecommons" onclick="return ! window.open(this.href);">www.flickr.com/creativecommons</a></p>', $this->name);
				?>
			</div>
			<br />
		</div>
		<ul>
		<?php
			if ( !$is_new ) {
				foreach ( $plugin_options['galleries'][$edit_id]['photos'] as $photo )
					_photo_box($this, $photo);
			}
		?>
		</ul>
	</div>
	
	<p class="submit">
		<input type="submit" class="button-primary" id="submit_form_button" value="<?php if ( !$is_new ) { _e('Save Changes', $this->name); } else { _e('Add Gallery', $this->name); } ?>" />
	</p>
	
</form>

<!-- Clone Elements -->
<div style="display:none;">
	<form id="clonable_fields" action="#"><?php _photo_fields(); ?></form>
	<ul id="clonable_box"><?php _photo_box($this); ?></ul>
</div>
<!-- End Clone Elements -->