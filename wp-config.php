<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'howlate_wp');

/** MySQL database username */
define('DB_USER', 'howlate_wp');

/** MySQL database password */
define('DB_PASSWORD', 'ZiqhEWW0ZU');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '!mkkp-L9J^+2JA%RYmBg`#lr3[tapUM)ZIV0@Q*Gbxo(>|xQ/@,&R&Q${+jmm9xM');
define('SECURE_AUTH_KEY',  'MDZQK+g(w8Y=>H0dew`kna/j-kvPHs JB9:~TOrtWPLt`y|}}0$+7(<8~mc~lL&[');
define('LOGGED_IN_KEY',    ',-~VAtC6-!ITW[Wr*p*ZQs6hrK~h6h5Ewk:Vy>-)T]HKqH4QeBu~kXOghb>qNB8<');
define('NONCE_KEY',        'UHEr5&9l.O@;}md`c%aq-Ij)Cz|dN//ZI_6=Ov!(Q)O9Iu+BuD]EnRO>lj5-cySP');
define('AUTH_SALT',        'Hk4p;o~1dG@5lAyX3j_<+d~_+GD<tJ}hFFvdjijNCvUMP(`cEg^KT|?YF2eV64@o');
define('SECURE_AUTH_SALT', 'b5L]eq+n@svX(w(M~9B.~a9Kq6/S,ZQg4a#T13Cdwy,GKAj@T~b)VVk?RQb62&kU');
define('LOGGED_IN_SALT',   'BwCnf@J;c.8$+>g;Gj;$G+eLv~[dX=.flY^-t^8_HzJQv|~GTxbAXl/o:%O1WA6o');
define('NONCE_SALT',       'PA]kSeO%UHkV~Fs8]m2p|LT-`S--#Wj$l{;}&Fy{a>!-(_3|7HZT;DhIa6bu|uLJ');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
