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
	// Generate the tooltips
	$('.tooltip').each(function() {
		$(this).qtip({
			show: 'mouseover',
			hide: 'mouseout',
			content: $('.tooltip-content', this).html(),
			position: {
				corner: {
					target: 'topMiddle',
					tooltip: 'bottomRight'
				},
				adjust: {
					screen : true
				}
			},
			style: {
				border: {
					radius: 8
				},
				width: {
					max: 450
				},
				name: 'cream',
				tip: 'bottomRight'
			}
		})
	});
});
