<?php
/**
 * Style sheet loader
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
 * @subpackage externalLoaders
 * @author Mike Green (Myatu) <me@myatus.co.uk>
 * @copyright Copyright 2010-2011 Mike Green (Myatu)
 * @license http://www.gnu.org/licenses/gpl.html
 * @link http://www.myatus.co.uk
 * @version $Id: pub-style.css.php 353401 2011-02-28 20:54:08Z Myatu $ 
 */
 
require_once(dirname(__FILE__) . '/inc/ext-include.php');

header('Content-Type: text/css; charset=UTF-8');

ob_start();
do_action('wp_flickr_background_stylesheet', ($is_preview) ? $preview_css : '');
$out = ob_get_contents();
ob_end_clean();

if ( !ini_get('zlib.output_compression') && 
	 (ini_get('output_handler') != 'ob_gzhandler') && 
	 isset($_SERVER['HTTP_ACCEPT_ENCODING']) ) {
	
	header('Vary: Accept-Encoding');
	
	if ( (strpos(strtolower($_SERVER['HTTP_ACCEPT_ENCODING']), 'gzip') !== false) && function_exists('gzencode') ) {
		header('Content-Encoding: gzip');
		$out = gzencode($out, 3);
	} elseif ( (strpos(strtolower($_SERVER['HTTP_ACCEPT_ENCODING']), 'deflate') !== false) && function_exists('gzdeflate') ) {
		header('Content-Encoding: deflate');
		$out = gzdeflate($out, 3);
	}
}

echo $out;

die;
//-- That's right, no closing tag!

