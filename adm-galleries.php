<?php 
/*
PLEASE DO NOT DELETE THE FOLLOWING LINE AND LEAVE IT AT THE TOP:
*/
defined('WP_ADMIN') or die ('Restricted Access');

/**
 * Admin include file for displaying / editing galleries
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
 * @version $Id: adm-galleries.php 369209 2011-04-05 19:05:16Z Myatu $ 
 */
 
// Doublecheck if the plugin has valid options:
if ( count($plugin_options['galleries']) == 0 )
	$this->upgrade_options(true);

/**
Pagination - hard coded at 25 per page
**/
$galleries_per_page	= 25;
$total_galleries	= count($plugin_options['galleries']);
$total_pages		= ceil($total_galleries  / $galleries_per_page);
$page				= $this->get_var('paged');
if ( $page > $total_pages )
	$page = $total_pages;
$start				= ($page - 1) * $galleries_per_page;

$page_links = paginate_links( array(
	'base' => add_query_arg( array('paged'=>'%#%', 'submenu'=>'galleries'), $this->get_admin_url() ),
	'format' => '',
	'prev_text' => __('&laquo;'),
	'next_text' => __('&raquo;'),
	'total' => $total_pages,
	'current' => $page
));

$page_links_text = sprintf( '<span class="displaying-num">' . __('Displaying %s&#8211;%s of %s', $this->name ) . '</span>%s',
	number_format_i18n( $start + 1 ),
	number_format_i18n( min( $page * $galleries_per_page, $total_galleries ) ),
	'<span class="total-type-count">' . number_format_i18n( $total_galleries ) . '</span>',
	$page_links
);

?>

<form method="post" action="<?php echo htmlspecialchars(add_query_arg(array('submenu'=>'galleries', 'paged'=>$page), $this->get_admin_url())); ?>">
	<?php wp_nonce_field($this->name); ?>

	<div class="tablenav">
		<?php 
			if ( $page_links ) 
				echo '<div class="tablenav-pages">', $page_links_text, '</div>'; 
		?>
		
		<div class="alignleft actions">
			<select name="bulk-action[]">
				<option value="" selected="selected"><?php _e('Bulk Actions', $this->name); ?></option>
				<option value="delete-selected-galleries"><?php _e('Delete Selected', $this->name); ?></option>
			</select>
			<input type="submit" name="bulk-action-1" value="<?php _e('Apply', $this->name); ?>" class="button-secondary action" />
			|
			<a href="<?php echo htmlspecialchars(add_query_arg('submenu', 'gallery-edit', $this->get_admin_url())); ?>" class="button">Add New Gallery</a>
		</div>		
	</div>
	
	<table class="widefat" cellspacing="0" id="gallery-table">
		<thead>
			<tr>
				<th scope="col" class="manage-column check-column"><input type="checkbox" /></th>
				<th scope="col" class="manage-column"><?php _e('Gallery', $this->name); ?></th>
				<th scope="col" class="manage-column"><?php _e('Description', $this->name); ?></th>
			</tr>			
		</thead>

		<tfoot>
			<tr>
				<th scope="col" class="manage-column check-column"><input type="checkbox" /></th>
				<th scope="col" class="manage-column"><?php _e('Gallery', $this->name); ?></th>
				<th scope="col" class="manage-column"><?php _e('Description', $this->name); ?></th>
			</tr>			
		</tfoot>
		
		<tbody class="plugins">
		<?php
			/**
			Note that we borrow the WordPress "plugins" CSS class here. 
			This has nothing to do with plugins - it's only for consistent looks!
			**/

			foreach ( array_slice($plugin_options['galleries'], $start, $galleries_per_page, true) as $gallery_idx => $gallery ) {
				$is_default = ($gallery_idx == 0);

				// 1st row: Title and description
				echo '<tr class="active">';
			
				if ( $is_default ) {
					// The default gallery cannot be deleted
					echo '<td>&nbsp;</td>';
				} else {
					echo '<th scope="row" class="check-column"><input type="checkbox" name="checked[]" value="' . $gallery_idx . '" /></th>';
				}
				
				echo (
						'<td class="plugin-title">' .
							'<strong>' . $gallery['name'] . '</strong>' .
							'<div class="active second">' .
								'<div class="row-actions">' .
									'<span class="edit"><a href="' . htmlspecialchars(add_query_arg(array('submenu'=>'gallery-edit', 'idx'=>$gallery_idx, 'paged'=>$page), $this->get_admin_url())) . '" title="' . __('Edit this gallery', $this->name) . '">' . __('Edit', $this->name) . '</a></span>'
				);
				
				// Default gallery does not get a "Delete" link
				if ( !$is_default ) echo '&nbsp;|&nbsp;<span class="delete"><a href="' . wp_nonce_url(add_query_arg(array('submenu'=>'galleries', 'idx'=>$gallery_idx, 'action'=>'delete-gallery', 'paged'=>$page), $this->get_admin_url()), $this->name) . '" title="' . __('Delete this gallery', $this->name) . '" class="tbody_delete_link">' . __('Delete', $this->name) . '</a></span>';
				
				echo (
								'</div>' .
							'</div>' .
						'</td>' .
						'<td class="desc">' .
							'<p>' . $gallery['desc'] . '&nbsp;</p>' .
							'<div class="desc">' .
								sprintf( _n('%s Photo', '%s Photos', count($gallery['photos']), $this->name), number_format_i18n(count($gallery['photos'])) ) .
							'</div>' .							
						'</td>'.
					'</tr>'
				); // End 1st row
				
			}
		?>
		</tbody>	
	</table>

	<div class="tablenav">
		<?php 
			if ( $page_links ) 
				echo '<div class="tablenav-pages">', $page_links_text, '</div>'; 
		?>
		
		<div class="alignleft actions">
			<select name="bulk-action[]">
				<option value="" selected="selected"><?php _e('Bulk Actions', $this->name); ?></option>
				<option value="delete-selected-galleries"><?php _e('Delete Selected', $this->name); ?></option>
			</select>
			<input type="submit" name="bulk-action-2" value="<?php _e('Apply', $this->name); ?>" class="button-secondary action" />
			|
			<a class="button" href="<?php echo htmlspecialchars(add_query_arg('submenu', 'gallery-edit', $this->get_admin_url())); ?>">Add New Gallery</a>
		</div>		
	</div>
	
</form>
