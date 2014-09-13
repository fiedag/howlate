<?php
/**
 * howlate Administration Bootstrap
 * admin.php
 */

ini_set('error_log','my_file.log'); 
 
if ( !function_exists('auth_redirect') ) :
function auth_redirect() {
	// Checks if a user is logged in, if not redirects them to the login page
	if ( validate_auth_cookie() ) {
		return;  // The cookie is good so we're done
	}
	
	// redirect to login page if no good
        if (defined("__SUBDOMAIN"))
            $login_url = 'https://' . __SUBDOMAIN . "." . __DOMAIN . "/login";
        else
            $login_url = 'http://how-late.com';

	header("Location: $login_url");
	exit();
}
endif;

if ( !function_exists('validate_auth_cookie') ) :
function validate_auth_cookie() {
	
	if ( ! $cookie_elements = parse_auth_cookie() ) {
		error_log('auth_cookie_malformed <br> ' . print_r($cookie_elements) . '<br>');
		return false;
	}
	return true;
}
endif;


if ( !function_exists('parse_auth_cookie') ) :
/**
 * Parse a cookie into its components
 *
 * @since 2.7
 *
 * @param string $cookie
 * @param string $scheme Optional. The cookie scheme to use: auth, secure_auth, or logged_in
 * @return array Authentication cookie components
 */
function parse_auth_cookie() {
	$cookie_name = 'howlate_auth_cookie';
	if ( empty($_COOKIE[$cookie_name]) )
		return false;

	$cookie = $_COOKIE[$cookie_name];
	$cookie_elements = explode('|', $cookie);
	if ( count($cookie_elements) != 3 )
		return false;

	list($username, $expiration, $hmac) = $cookie_elements;

	return compact('username', 'expiration', 'hmac', 'scheme');
}
endif;

if ( !function_exists('wp_generate_auth_cookie') ) :
/**
 * Generate authentication cookie contents.
 *
 * @since 2.5
 * @uses apply_filters() Calls 'auth_cookie' hook on $cookie contents, User ID
 *		and expiration of cookie.
 *
 * @param int $user_id User ID
 * @param int $expiration Cookie expiration in seconds
 * @param string $scheme Optional. The cookie scheme to use: auth, secure_auth, or logged_in
 * @return string Authentication cookie contents
 */
function generate_auth_cookie($user_id, $expiration = 600, $scheme = 'auth') {
	$db = new howlate_db();
	
	$user = $db->get_userdata($user_id);
	$pass_frag = substr($user->user_pass, 8, 4);
	$key = wp_hash($user->user_login . $pass_frag . '|' . $expiration, $scheme);
	$hash = hash_hmac('md5', $user->user_login . '|' . $expiration, $key);

	$cookie = $user->user_login . '|' . $expiration . '|' . $hash;

	return apply_filters('auth_cookie', $cookie, $user_id, $expiration, $scheme);
}
endif;


 
function set_auth_cookie() {
	$cookie_name = 'howlate_auth_cookie';
	
	setcookie($cookie_name, $auth_cookie, $expire, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN, $secure, true);


}


auth_redirect();

?>
