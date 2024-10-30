<?php
/*
Plugin Name: Hotlink 2 Link
Plugin URI: http://www.geniosity.co.za/musings/wordpress/hotlink-2-link-wordpress-plugin/
Description: This plugin allows people to hotlink an image from your website by presenting them with a popup containing code that will backlink to your post.
Author: James McMullan
Version: 1.1
Author URI: http://www.geniosity.co.za/
*/

if ( ! class_exists( 'wpHL2L_Admin' ) ) {

	class wpHL2L_Admin {
		function add_config_page() {
			global $wpdb;
			if ( function_exists('add_submenu_page') ) {
				add_submenu_page('plugins.php','Hotlink 2 Link for WordPress', 'Hotlink 2 Link', 1, basename(__FILE__),array('wpHL2L_Admin','config_page'));
			}
		} // end add_config_page()
		
		function config_page() {
			if ( isset($_POST['submit']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the "Hotlink 2 Link for WordPress" plugin options.'));
				check_admin_referer('wphl2l-udpatesettings');
			}

			if ( isset($_POST['submit']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the "Hotlink 2 Link" options.'));
				check_admin_referer('wphl2l-udpatesettings');

				if (isset($_POST['enable-on-posts'])) {
					$options['enable-on-posts'] = $_POST['enable-on-posts'];
				}
				if (isset($_POST['enable-on-pages'])) {
					$options['enable-on-pages'] = $_POST['enable-on-pages'];
				}
				if (isset($_POST['popup-text'])) {
					$options['popup-text'] = $_POST['popup-text'];
				}

				update_option('wphl2l', $options);

			}
			
			$options = get_option('wphl2l');

			?>
			<div class="wrap">
				<h2>Hotlink 2 Link Configuration</h2>
				<fieldset style="margin-top: 25px;">
					<form action="" method="post" id="wphl2l-conf">
						<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('wphl2l-udpatesettings'); } ?>
						<span style="float: right; margin-top: -40px; border: none;" class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></span>
						<table class="form-table">
							<tr>
								<th>
								<label for="enable-on-posts">Enable for posts?:</label>
								</th>
								<td>
									<input type="checkbox" id="enable-on-posts" name="enable-on-posts" <?php if ( $options['enable-on-posts'] == true ) echo ' checked="checked" '; ?>/>
								</td>
							</tr>
							<tr>
								<th>
								<label for="enable-on-pages">Enable for pages?:</label>
								</th>
								<td>
									<input type="checkbox" id="enable-on-pages" name="enable-on-pages" <?php if ( $options['enable-on-pages'] == true ) echo ' checked="checked" '; ?>/>
								</td>
							</tr>
							<tr>
								<th style="padding-top: 25px;">
									<label for="description-text">Popup text:</label>
								</th>
								<td>
									(the text that will be shown to the person who right-clicked on the image)<br/>
									<textarea cols="80" rows="9" name="popup-text" id="popup-text"><?php echo stripslashes($options['popup-text']) ?></textarea>
								</td>
							</tr>
						</table>
						<p class="submit">
							<span style="float: right; margin-top: -30px; border: none;" class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></span>
						</p>
					</form>
				</fieldset>
				<br/><br/>

			</div>
			<div class="wrap" style="padding-bottom: 20px;">
				<h2>Example</h2>
				<p>
					<p><img src="<?php echo (hl2lGetPluginURL() . "img/example.png") ?>" onmouseup="hl2l(event);" style="float:right;"/>
					So this could be your example post. You'd write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write and write <b>and tell people everything they wanted to hear</b>.
					<p>And then, inside your post, you'd have an image like the one on the write. <em>Sorry, on the right....</em></p>
					<p>Go on, right-click and see if it works... :-) (<em>If you don't see your new "popup text", just reload this page</em>)</p>
				</p>
			</div>
			<div class="wrap">
				<h2>About "Hotlink 2 Link for WordPress"</h2>
				<p>
					<h3>Description</h3>
					This plugin will help put all those hot-linked images to good use. A hotlinked image can be a bit of a drain on your website's bandwidth because everytime the image is viewed on another website, it is being downloaded from your host.
					<br />
					Once you've enabled this plugin and set your options, anybody who right-clicks on an image will receive a pop-up "dialog box" asking them to use the code for linking to the image from a normal website, or for use in forums.
				</p>
				<p>
					<h3>Usage</h3>
					If you're reading this, then you probably have the plugin installed and activated on your blog.<br />
					<br />
					If you would like it enabled on all pages on your site, just ensure that you have checked all the checkboxes at the top of this page.
					<br />
					You can also customise the message in the pop-up dialog box by adding your own message in the text box above.
				</p>
				<p>
					<h3>More Info</h3>
					<p>
						This plugin is based on (and uses) the script "<a href="http://www.funscripts.net/javascript-widgets/">LinkMyPics</a>"
					</p>
					You can get more info regarding this plugin from the following pages:
					<ul>
						<li><b><a href="http://www.geniosity.co.za/musings/wordpress/hotlink-2-link-wordpress-plugin/">Plugin Announcement page</a></b> - If you subscribe to the comments feed you'll keep up with release announcements</li>
						<li><b><a href="http://www.geniosity.co.za/wordpress/hotlink-2-link-plugin">Plugin Homepage</a></b></li>
						<li><b><a href="http://www.geniosity.co.za/forums/tools/wordpress/hotlink-2-link-plugin">Hotlink 2 Link Plugin Forum</a></b> - Discuss the plugin in the forums if you've found bugs or have any requests...</li>
					</ul>
				</p>
			</div>

<?php			
		}
	}
}

function hl2lContentFilter($content = '')
{
	$options = get_option('wphl2l');
	
	if((!is_page() && $options['enable-on-posts']) || (is_page() &&  $options['enable-on-pages'])) {
		$preContent = $content;
		$postContet = '';
		$imgSearchString = '/<img /';
		$imgReplaceString = '<img onmouseup="hl2l(event);" ';
		
		$postContet = preg_replace($imgSearchString, $imgReplaceString, $preContent);
		return $postContet;
	} else {
		return $content;
	}
}

function hl2lGetPluginURL() {
	$path = dirname(__FILE__);
	$path = str_replace("\\","/",$path);
	$path = trailingslashit(get_bloginfo('wpurl')) . trailingslashit(substr($path,strpos($path,"wp-content/")));
	return $path;
}

function hl2lFullGetPluginURL() {
	return hl2lGetPluginURL() . basename(__FILE__);
}

function hl2lPrintScriptHeader() {
	$options = get_option('wphl2l');
	
	echo "<script type='text/javascript'>\n";
	echo "/* <![CDATA[ */\n";
	echo "theMessage = '" . stripslashes($options['popup-text']) ."'\n";
	echo "/* ]]> */\n";
	echo "</script>\n";
	echo ('<script type="text/javascript" src="' . hl2lGetPluginURL() . 'js/hl2l.js"></script>'. "\n");
}

function hl2lAddAdminScriptCode() {
	$plugin_page = stripslashes($_GET['page']);
	$plugin_page = plugin_basename($plugin_page);
	if ($plugin_page == 'wphl2l.php') {
		hl2lPrintScriptHeader();
	}
}

function hl2lAddPageScriptCode() {
	$options = get_option('wphl2l');

	if((!is_page() && $options['enable-on-posts']) || (is_page() &&  $options['enable-on-pages'])) {
		hl2lPrintScriptHeader();
	}
}

// FILTERS AND ACTIONS
add_filter('the_content', 'hl2lContentFilter');
add_action('admin_menu', array('wpHL2L_Admin','add_config_page'));
add_action('admin_head', 'hl2lAddAdminScriptCode');
add_action('wp_head', 'hl2lAddPageScriptCode');


?>
