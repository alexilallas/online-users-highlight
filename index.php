<?php

/**
 * @package Online_Users_Highlight
 * @version 0.0.1
 */
/*
Plugin Name: Online Users Highlight
Plugin URI: https://github.com/alexilallas/online-users-highlight
Description: This is just a plugin.
Author: Alexi Lallas
Version: 0.0.1
Author URI: http://github.com/alexilallas
*/


// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class OnlineUsersHighlight
{
	/**
	 * Holds the Static instance of this class
	 * @var OnlineUsersHighlight
	 */
	private static $instance;

	/**
	 * Holds the Static instance of this class
	 * @var string
	 */
	private static $option = 'online-users-highlight';


	/**
	 * [instance description]
	 * @return OnlineUsersHighlight return a static instance of this class
	 */
	public static function instance()
	{
		return self::$instance ? self::$instance : self::$instance = new self;
	}

	/**
	 * Initalized the plugin main class
	 * @return bool Boolean on if the init process was successful
	 */
	private function __construct()
	{
		add_action('wp_logout', array($this, 'logout'));
		add_action('set_current_user', array($this, 'login'));

		add_shortcode('online-users-highlight', array($this, 'shortcode_list'));

		return true;
	}

	/**
	 * Whenever the user logs in, we adds its data in the Database option
	 * @return bool return if the user was added
	 */
	public function login()
	{
		$users = get_option(self::$option, array());

		$user = wp_get_current_user();

		if ($user instanceof WP_User === false || $user->exists() === false) {
			return false;
		}

		$users[$user->ID] = $user->data;

		return update_option(self::$option, $users);
	}


	/**
	 * Whenever a User logs out we remove its data from the Database option
	 *
	 * @return bool return if the user was removed
	 */
	public function logout($user = null)
	{
		$users = get_option(self::$option, array());

		if (isset($users[$user]) === false) {
			return false;
		}
		unset($users[$user]);

		return update_option(self::$option, $users);
	}

	/**
	 * A method to get all the users online from the database
	 *
	 * @return array|null Users currently online, array of Wp_User
	 */
	public function get_users()
	{
		$users = get_option(self::$option, array());

		foreach ($users as $user_id => $user) {
			if ($this->user_exists($user_id)) {
				continue;
			}

			unset($users[$user_id]);
		}

		return $users;
	}

	/**
	 * Check if the user ID exists
	 *
	 * @param int $user_id The user id
	 *
	 * @return bool Returns a boolean if the object is valid
	 */
	public function user_exists($user_id)
	{
		$user = new WP_User($user_id);

		return $user->exists();
	}

	/**
	 * Based on the database option that we created in the methods above we will allow the admin to show it on a shortcode
	 *
	 * @param string $atts The atributes from the shortcode
	 *
	 * @return string       The HTML of which users are online
	 */
	public function shortcode_list($atts = null)
	{
		$users   = $this->get_users();

		if (empty($users) == true) {
			return "<p>  " . esc_attr__('There are no users online right now', 'online-users') . " </p>";
		}

		$html = '
		<table class="online-users">
			<tr>
				<th>Costumer ID</th>
				<th>Avatar</th>
				<th>Name</th>
			</tr>';

		foreach ($users as $user) {
			$html .= "<tr>

			<td> <span>$user->ID</span> </td>

			<td>" . get_avatar($user->ID, 96, '', '', ['class' => 'avatar']) . "</td>

			<td><span>$user->display_name</span></td>

			</tr>";
		}

		return $html . '</table>';
	}
}

add_action('plugins_loaded', array('OnlineUsersHighlight', 'instance'));

wp_enqueue_style('online-users-css', plugin_dir_url(__FILE__) . 'style.css');

/**
 * Creates a Globally Acessible function to get all online users
 */
if (!function_exists('get_online_users')) {
	function get_online_users()
	{
		return OnlineUsersHighlight::instance()->get_users();
	}
}
