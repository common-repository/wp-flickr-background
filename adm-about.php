<?php 
/*
PLEASE DO NOT DELETE THE FOLLOWING LINE AND LEAVE IT AT THE TOP:
*/
defined('WP_ADMIN') or die ('Restricted Access');

/**
 * Admin include file for the 'About' page
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
 * @version $Id: adm-about.php 369209 2011-04-05 19:05:16Z Myatu $ 
 */
?>

<div class="notice">
	<p>
		<?php printf( __('Thank you for using <strong>%s</strong>! If you really like it, you could:', $this->name), $this->get_plugin_info('Name') ); ?>
	</p>
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

<img src="<?php echo $this->get_plugin_url(); ?>img/gplv3-127x51.png" title="GPL v3" style="float:right;" alt="GPL v3" />

<h3><?php _e('Copyright', $this->name); ?></h3>
<p>
	<?php printf( __('%s version %s - Copyright 2010-2011 <a href="%s" onclick="return ! window.open(this.href);">%s</a>.', $this->name), $this->get_plugin_info('Name'), $this->get_plugin_info('Version'), $this->get_plugin_info('AuthorURI'), $this->get_plugin_info('Author') ); ?>
</p>
<p>
This product uses the Flickr API but is not endorsed or certified by Flickr.
</p>

<h3><?php _e('License', $this->name); ?></h3>
<pre class="large-text code" style="overflow: auto; max-height: 350px; background-color: rgb(255, 255, 255); border: 1px solid rgb(221, 221, 221); padding: 10px;">
<?php 
	if ( @file_exists($this->get_plugin_dir() . 'LICENSE') ) {
		$content = file_get_contents($this->get_plugin_dir() . 'LICENSE');
		echo htmlspecialchars($content);
	} else {
		_e('See http://www.gnu.org/licenses/gpl.html for more details', $this->name);
	}
?>
</pre>


