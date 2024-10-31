<?php
/*
Plugin Name: Persian bbPress Tools
Plugin URI: http://gilgaz.com
Description: This plugin provides Persian bbPress with Jalali date and some CSS styles. It also changes the default editor of bbPress and adds a user related menu bar above the forums and topics pages.
Author: Hesam Bahrami (Genzo)
Version: 0.6.2
Author URI: http://gilgaz.com
License: GPLv2 or later

/*  Copyright 2013 Hesam Bahrami (Genzo)  (email : hb@gilgaz.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// Manipulating the visual editor

if ( ! is_admin() ) {
	add_filter("mce_buttons", "pbbp_tools_mce_buttons", 0);
	add_filter("mce_buttons_2", "pbbp_tools_mce_buttons_2", 0);
	add_filter("mce_buttons_3", "pbbp_tools_mce_buttons_3", 0);
}

function pbbp_tools_mce_buttons($buttons) {
	return array("bold", "italic", "underline", "strikethrough", "forecolor", "bullist", "numlist", "justifyleft", "justifycenter", "justifyright", "justifyfull", "link", "unlink", "blockquote");
}
function pbbp_tools_mce_buttons_2($buttons) {return array();}
function pbbp_tools_mce_buttons_3($buttons) {return array();}

function pbbp_full_editor_fn( $args = array() ) {
	$args['tinymce'] = true;
	$args['teeny'] = false;
	$args['quicktags'] = false;
    return $args;
}
add_filter( 'bbp_after_get_the_content_parse_args', 'pbbp_full_editor_fn' );


// Manipulating the subforums stack style

function subforums_stack_fn($args = '') {
	$args['count_sep'] = ' ، ';
	$args['separator'] = '';
	return $args;
}
add_filter('bbp_after_list_forums_parse_args', 'subforums_stack_fn');


// Loading scripts and style sheets

function persian_bbpress_tools_scripts(){
	$plugin_directory = plugin_dir_url(__FILE__);
	wp_enqueue_script('jdate', $plugin_directory . '/jdate.js', array( 'jquery' ));
	wp_enqueue_style( 'persian-bbpress-tools', $plugin_directory . 'bbpress-rtl.css' );
}
add_action('wp_enqueue_scripts', 'persian_bbpress_tools_scripts');

function date_converter() {
	$options = get_option('pbbp_tools_options');
	if ($options['jdate_format']== 'بلند') {
		$jdate_format = 'Long';
	} else {
		$jdate_format = 'Short';
	}
		echo '
		<script>
			jQuery(document).ready(function() {

				jQuery(".bbp-reply-post-date").each(function(i, obj) {
					var gdate = jQuery(this).text();
					var gdateArray = gdate.split(" ");

					gdateArray[gdateArray.indexOf("ژانویه")] = 1;
					gdateArray[gdateArray.indexOf("فوریه")] = 2;
					gdateArray[gdateArray.indexOf("مارس")] = 3;
					gdateArray[gdateArray.indexOf("آوریل")] = 4;
					gdateArray[gdateArray.indexOf("می")] = 5;
					gdateArray[gdateArray.indexOf("ژوئن")] = 6;
					gdateArray[gdateArray.indexOf("جولای")] = 7;
					gdateArray[gdateArray.indexOf("آگوست")] = 8;
					gdateArray[gdateArray.indexOf("سپتامبر")] = 9;
					gdateArray[gdateArray.indexOf("اکتبر")] = 10;
					gdateArray[gdateArray.indexOf("نوامبر")] = 11;
					gdateArray[gdateArray.indexOf("دسامبر")] = 12;

					gdateArray[gdateArray.indexOf("January")] = 1;
					gdateArray[gdateArray.indexOf("February")] = 2;
					gdateArray[gdateArray.indexOf("March")] = 3;
					gdateArray[gdateArray.indexOf("April")] = 4;
					gdateArray[gdateArray.indexOf("May")] = 5;
					gdateArray[gdateArray.indexOf("June")] = 6;
					gdateArray[gdateArray.indexOf("July")] = 7;
					gdateArray[gdateArray.indexOf("August")] = 8;
					gdateArray[gdateArray.indexOf("September")] = 9;
					gdateArray[gdateArray.indexOf("October")] = 10;
					gdateArray[gdateArray.indexOf("November")] = 11;
					gdateArray[gdateArray.indexOf("December")] = 12;

					var jdate=ToShamsi(parseInt(gdateArray[2]), parseInt(gdateArray[1]), parseInt(gdateArray[0]), "'.$jdate_format.'");
					jQuery(this).text(jdate);
				});

			});
		</script>
	';
}
add_action('wp_head', 'date_converter');


// Add sub page to the Settings Menu

function pbbp_tools_options_add_page_fn() {
	add_options_page('ابزارهای بی‌بی‌پرس فارسی', 'بی‌بی‌پرس فارسی', 'administrator', __FILE__, 'pbbp_tools_options_page_fn');
}
add_action('admin_menu', 'pbbp_tools_options_add_page_fn');


function add_defaults_fn() {
	$tmp = get_option('pbbp_tools_options');
    if(!is_array($tmp)) {
		$arr = array("jdate_format" => "کوتاه");
		update_option('pbbp_tools_options', $arr);
	}
}
register_activation_hook(__FILE__, 'add_defaults_fn');


// Register our settings. Add the settings section, and settings fields

function pbbp_tools_options_init_fn(){
	register_setting('pbbp_tools_options_group', 'pbbp_tools_options', 'pbbp_tools_options_validate_fn' );
	add_settings_section('main_section', 'تنظیمات تاریخ جلالی', 'first_section_text_fn', __FILE__);
	add_settings_section('uri_section', 'پیوندهای بالای لیست تالارها و موضوع‌ها', 'second_section_text_fn', __FILE__);

	add_settings_field('radio_buttons', 'ساختار تاریخ جلالی', 'jdate_format_fn', __FILE__, 'main_section');
	add_settings_field('login_uri', 'آدرس صفحه ورود به سیستم بی‌بی‌پرس', 'login_uri_fn', __FILE__, 'uri_section');
	add_settings_field('register_uri', 'آدرس صفحه ثبت نام بی‌بی‌پرس', 'register_uri_fn', __FILE__, 'uri_section');
	add_settings_field('lostpass_uri', 'آدرس صفحه بازیابی رمز بی‌بی‌پرس', 'lostpass_uri_fn', __FILE__, 'uri_section');
}
add_action('admin_init', 'pbbp_tools_options_init_fn' );


// ****************************************************************

// Callback functions

// Section HTML, displayed before the first option
function  first_section_text_fn() {}
function  second_section_text_fn() {
	echo '<p>در صورتی که آدرس صفحه‌های مورد نظر را وارد کنید، پیوند هر کدام، در صورت لزوم، بالای لیست تالارها و موضوع‌ها قرار خواهد گرفت. میتوانید کدهای کوتاه (Shortcode) بی‌بی‌پرس را در برگه‌ها قرار داده و آدرس آن برگه‌ها را در فیلدهای پایین قرار دهید. برای اطلاع از کدهای کوتاه (Shortcode) بی‌بی‌پرس <a href="http://codex.bbpress.org/shortcodes/" target="_blank">اینجا کلیک کنید</a>. آدرس برگه‌ها را به صورت کامل (به همراه <bdi style="direction:ltr;">http://</bdi>) وارد کنید.</p>';
}

// RADIO-BUTTON - Name: pbbp_tools_options[jdate_format]
function jdate_format_fn() {
	$options = get_option('pbbp_tools_options');
	$items = array("بلند", "کوتاه");
	foreach($items as $item) {
		$checked = ($options['jdate_format']==$item) ? ' checked="checked" ' : '';
		echo "<label><input ".$checked." value='$item' name='pbbp_tools_options[jdate_format]' type='radio'> $item</label><br>";
	}
	echo '<br>نمونه یک تاریخ با ساختار بلند: شنبه 5 اسفند 1391<br>نمونه یک تاریخ با ساختار کوتاه:
1391/12/5<br><br>';
}

// TEXTBOX - Name: pbbp_tools_options[login_uri_string]
function login_uri_fn() {
	$options = get_option('pbbp_tools_options');
	echo "<input id='login_uri' name='pbbp_tools_options[login_uri_string]' size='40' type='text' value='{$options['login_uri_string']}' style='direction:ltr;' />";
}

// TEXTBOX - Name: pbbp_tools_options[register_uri_string]
function register_uri_fn() {
	$options = get_option('pbbp_tools_options');
	echo "<input id='register_uri' name='pbbp_tools_options[register_uri_string]' size='40' type='text' value='{$options['register_uri_string']}' style='direction:ltr;' />";
}

// TEXTBOX - Name: pbbp_tools_options[lostpass_uri_string]
function lostpass_uri_fn() {
	$options = get_option('pbbp_tools_options');
	echo "<input id='lostpass_uri' name='pbbp_tools_options[lostpass_uri_string]' size='40' type='text' value='{$options['lostpass_uri_string']}' style='direction:ltr;' />";
}


// Display the admin options page
function pbbp_tools_options_page_fn() {
?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>ابزارهای بی‌بی‌پرس فارسی</h2>

		<form action="options.php" method="post">
		<?php
			settings_fields('pbbp_tools_options_group');
			do_settings_sections(__FILE__);

			submit_button();
		?>
		</form>
	</div>
<?php
}

// Validate user data for some/all of your input fields
function pbbp_tools_options_validate_fn($input) {
	// Check our textbox option field contains no HTML tags - if so strip them out
	// $input['text_string'] =  wp_filter_nohtml_kses($input['text_string']);
	return $input; // return validated input
}


function pbbp_tools_user_links_fn() {

	$options = get_option('pbbp_tools_options');
	global $current_user;
	get_currentuserinfo();


	if ( is_user_logged_in() ) {

		echo '<p class="pbbp-tools-user-info">شما با نام کاربری <a href="' . bbp_get_user_profile_url( get_current_user_id() ).'">'. $current_user->user_login . '</a> وارد شده‌اید. (<a href="' . wp_logout_url( $redirect_to ) .'">خروج از سیستم</a>)</p>';

	} else {

		$login_uri = $options['login_uri_string'];
		$signup_uri = $options['register_uri_string'];
		$lostpass_uri = $options['lostpass_uri_string'];

		if (!empty($login_uri) || !empty($signup_uri) || !empty($lostpass_uri)) {
			echo '<p class="pbbp-tools-links-info">';
			$closing_tag = 1;
		}

		$dash = 0;
		if (!empty($login_uri)) {echo '<a href="'.$login_uri.'">ورود به سیستم</a>';$dash++;}
		if (!empty($signup_uri)) {if ($dash > 0) echo ' &nbsp; - &nbsp;  ';echo '<a href="'.$signup_uri.'">ثبت نام</a>';$dash++;}
		if (!empty($lostpass_uri)) {if ($dash > 0) echo ' &nbsp; - &nbsp; ';echo '<a href="'.$lostpass_uri.'">بازیابی رمز عبور</a>';}

		if ($closing_tag == 1) echo '</p>';
	}
}

add_action( 'bbp_template_before_forums_index', 'pbbp_tools_user_links_fn' );
add_action( 'bbp_template_before_topics_index', 'pbbp_tools_user_links_fn' );
add_action( 'bbp_template_before_single_forum', 'pbbp_tools_user_links_fn' );
add_action( 'bbp_template_before_single_reply', 'pbbp_tools_user_links_fn' );
add_action( 'bbp_template_before_lead_topic', 'pbbp_tools_user_links_fn' );
add_action( 'bbp_template_before_single_topic', 'pbbp_tools_user_links_fn' );

?>
