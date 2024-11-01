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
 */

jQuery(document).ready(function($){
	// Bulk actions
	$('.action').click(function(e) {
		e.stopPropagation();
		var selector = $('select', $(this).closest('div'));
		
		if ( selector.val() == 'delete-selected-galleries' ) {
			if ( !confirm(commonL10n.warnDelete) ) {
				// Reset selection
				$('[value=""]', selector).attr('selected', 'selected');
			
				return false;
			}
		} else if ( selector.val() == '' ) {
			// Empty selection
			return false;
		}
	});

	// Delete link
	$('.tbody_delete_link').click(function(e) {
		e.stopPropagation();
		return  confirm(commonL10n['warnDelete']);
	});
});