/**
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
 */

// A very simple hash; no collision check
function hash(s) {
	var i;
	var chk = 0x12345678;

	if (s == undefined) return chk;

	for (i = 0; i < s.length; i++) {
		chk += (s.charCodeAt(i) * i);
	}

	return chk;
}

jQuery(document).ready(function($){
	/**
	Helper Functions
	**/
	
	// Replaces a class
	jQuery.fn.replaceClass = function(oldclass, newclass) {
		this.removeClass(oldclass).addClass(newclass); 
		return this;
	}
	
	jQuery.fn.getPhotoBox = function() {
		return this.closest('.photo_box');
	}
	
	jQuery.fn.togglePhotoBoxHilite = function() {
		var photo_box = this.getPhotoBox();
	
		if ( photo_box )
			photo_box.toggleClass('photo_box_select_hilite', this.attr('checked'));
		
		return this;
	}

	// Handler for WP Gallery 'Add Image'
	window.send_to_editor = function(html) {
		$('#ajax_notice').removeClass().hide('fast'); // Remove notices

		tb_remove(); // Close ThickBox window

		var img_token = jQuery('img',html);
		if ( img_token.length == 0 ) {
			// this *is* the img token
			img_token = jQuery(html);
		}

		if ( img_token.length == 0 ) return; // If we still have no image token, then give up trying.
			
		var img_url = img_token.attr('src');

		// Build data for adding photo
		var data = {
			id: 'L'+hash(img_url),
			title: img_token.attr('title'),
			owner: img_token.attr('alt'),
			photopage: img_url,
			thumbnail: img_url,
			background: img_url,
			licensename: '',
			licenseurl: '',
		};
	
		if ( !$('.photo_' + data.id)[0] ) {					
			add_photo(data);
			show_ajax_notice(galleryeditL10n.photo_added);
		} else {
			show_ajax_notice(galleryeditL10n.photo_already_present, true);
		}
	}	


	// Shows the photo message if no photos are available
	function toggle_photos_message() {
		( $('.photo_placeholder li')[0] ) ? $('#no_photos').hide() : $('#no_photos').show();
	}

	// Shows a warning (is_error = false or undefined) or error (is_error = true)
	function show_ajax_notice(message, is_error) {
		var notice_class = ( !is_error ) ? 'updated' : 'error';
		
		$('#ajax_notice').addClass(notice_class).html(message).show('fast');
	}
	
	// Adds a photo to the table, based on the given Ajax response data
	function add_photo(data) {
		if ( !data ) return;
		
		var photo_id = 'photo_'+data.id;

		// Don't add duplicates
		if ( $('#'+photo_id).length > 0 ) {
			return;
		}

		var _title = data.title + ' ' + galleryeditL10n.by + ' ' + data.owner;
		var _thickboxtitle = 
			'<strong>' + data.title + '</strong><br /> ' + galleryeditL10n.by + ' <a href="' + data.photopage + '" onclick="return ! window.open(this.href);">' + data.owner + '</a>' + 
			' (' + galleryeditL10n.license + ': <a href="' + data.licenseurl + '" onclick="return ! window.open(this.href);">' + data.licensename + '</a>)';
		
		// Clone fields and replace "photo_" with actual "photo_<id>"
		var _f = $('#clonable_fields fieldset').clone().replaceClass('photo_', photo_id);
		
		// Populate data fields
		_f.children('input')
			.each(function(i){
				var field = this.name.match(/photos_?(.+)\[\]/)[1];
				if ( data[field] != undefined )
					this.value = data[field];
			});		
		
		// Clone photo box
		var _b = $('#clonable_box li').clone(true).replaceClass('photo_', photo_id).attr('id', photo_id).css('opacity', 0);

		// Show "Now Photo" indicator
		$('.overlay_new', _b).css('display','block');
		
		// Add thumbnail
		$('.thumbnail_link', _b).attr({
			'href': data.background,
			'title': _thickboxtitle
		});
		$('.thumbnail_image', _b).attr({
			'src': data.thumbnail,
			'title': _title
		});
		
		// Add the cloned fields and tbody to the form and photo table respectivly
		_f.prependTo('#gallery_form');		
		_b.prependTo('.photo_placeholder ul').animate( { opacity: 1.0 }, 600 );
		
		// Hide "no photos" messsage
		toggle_photos_message();
	}
	
	// Deletes a single photo
	function delete_photo(photo_id) {
		// Extra safeguard,
		if ( photo_id == 'photo_' || photo_id == '' ) return;

		var photo_class = '.' + photo_id;
		
		$(photo_class + ' .thumbnail').fadeOut('slow', function() { 
			$(photo_class).remove(); 
			toggle_photos_message();
		});
	}
	
	/**
	Events
	**/
	
	// Global Ajax events
	$(document).bind('ajaxStart', function() {
		var purlo = $('#photo_url').offset();
		$('#ajax_notice').removeClass().hide('fast');
		$('#ajax_loading').show();
		$('#add_photo_button').attr('disabled','disabled');
	}).bind('ajaxStop', function() {
		$('#ajax_loading').hide();
		$('#add_photo_button').removeAttr('disabled');
	});

	
	// "Add Photo" event
	$('#add_photo_button').click(function(e) {
		e.stopPropagation();
		
		if ( $('#photo_url').val() == '' ) return;
	
		$.ajax({
			type : 'POST',
			dataType : 'json',
			url : ajaxurl,
			timeout : 30000,
	        data : {
				action: ajaxaction,
				func: 'get_photo_details',
				data: $('#photo_url').val(),
				_ajax_nonce: ajaxnonce
			},
			success : function(resp){
				if (resp.nonce != ajaxnonceresponse) {
					show_ajax_notice(galleryeditL10n.authenticity_error, true);
					return;
				}
			
				if (resp.stat == 'fail') {
					$('#ajax_notice').addClass('error').html(resp.data).show('fast');
				} else {
					if ( !$('.photo_' + resp.data.id)[0] ) {					
						add_photo(resp.data);
						show_ajax_notice(galleryeditL10n.photo_added);
					} else {
						show_ajax_notice(galleryeditL10n.photo_already_present, true);
					}
						
					$('#photo_url').val('');
				}
			},
			error : function(resp){
				show_ajax_notice(galleryeditL10n.error + ': ' + galleryeditL10n.ajaxerror, true);
			}
		});	
	});

	// Delete a single photo
	$('.photo_delete_link').click(function(e) {
		var photo_id = $(this).getPhotoBox().attr('id');
		
		if ( !photo_id || !confirm(commonL10n['warnDelete']) ) return false;
		
		delete_photo( photo_id );
	});
	
	// Bulk actions
	$('#bulk_apply_button').click(function(e) {
		if ( $('#bulk_action').val() == 'delete-selected' ) {
			if ( !confirm(commonL10n.warnDelete) ) {
				$('#bulk_action [value=""]').attr('selected', 'selected');
				
				return false;
			}
			
			var photo_id = '';
		
			$('.thumbnail :checked').each(function(i) {
				photo_id = $(this).getPhotoBox().attr('id');
				
				if ( photo_id )
					delete_photo( photo_id );
			});			
		}
		
		// Reset selection
		$('#bulk_action [value=""]').attr('selected', 'selected');	
	});
	
	// Form a preview link based on the selected photo and show in a new window
	$('.photo_preview_link').click(function(e) {
		var photo_id = $(this).getPhotoBox().attr('id');
		
		if ( !photo_id ) return;

		var photo_url = encodeURIComponent( $("." + photo_id + " input[name='photos_background[]']").val() );
		var photo_css = encodeURIComponent( $('#customcss').val() );
		
		window.open(gallery_preview_url + '&bg=' + photo_url + '&css=' + photo_css, 'preview');
	});	
	
	// Make sure the gallery has at least a name upon submit
	$('#submit_form_button').click(function(e) {
		if ( $('#gallery_name').val() == '' ) {
			e.stopPropagation();
			alert(galleryeditL10n.enter_name_before_saving);
			return false;
		}
	});
	
	// Slides individual actions into view when the mouse is over a photo
	$('.photo_box').mouseenter(function() {
		$('.overlay_actions', this).slideDown();
	}).mouseleave(function() {
		$('.overlay_actions', this).slideUp();
	});
	
	// Toggles the highlighting of a selected photo
	$('.thumbnail :checkbox').click(function() {
		$(this).togglePhotoBoxHilite();
	}).each(function(i) {
		$(this).togglePhotoBoxHilite();
	});
	
	/**
	Init
	**/
	
	toggle_photos_message();
});
