<?php
define( 'WP_CACHE', true /* Modified by NitroPack */ );

/** Enable W3 Total Cache */
 // Added by Hummingbird


define('FORCE_SSL_ADMIN', true);
define('WP_HOME','https://justinforjustice.com');
define('WP_SITEURL','https://justinforjustice.com');

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'justinforjustice_newsite');

/** MySQL database username */
define('DB_USER', 'justinj_nsite');

/** MySQL database password */
define('DB_PASSWORD', 'farahi123@@');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'w4R;3zz;:F}V!JrYp;MnAhEZ=88|Cvp,Y|-g.z&#E@bERX=}|+)~)~NH BeC%H$Y');
define('SECURE_AUTH_KEY',  '9r4+,9U_.?EU0mUT<ibHlq[R#[/,vRhfbYD=tq2uiQCZ/J}=~{N`WPOx*%HA_9 X');
define('LOGGED_IN_KEY',    'KG{-B7D58n{q[{$C}inCJW>)6J(@fm*l(J@*6wc;k;SRvDspu02Vtu,PEnzJ@Zg0');
define('NONCE_KEY',        'S!1EJFZ;FDAP~QNV):K]Cv3:UJ<Oqfn?9!;$8+`5J~YnHHt5lQ5)%I@wDtyAmZAW');
define('AUTH_SALT',        ',(WCD-ZYwC`gsURCeM1+u?_f^T7ogtEEA>4l@%!SyIDf<wJTO*3m,8 CVk+yXW/,');
define('SECURE_AUTH_SALT', 'n8Sq8y^FFUD|x4Yw333Jlhf8Y#u-%!owj^nvb!}m>W]]J[,5E,y]SY/Me]_I3bGF');
define('LOGGED_IN_SALT',   '?xlApi;3?LPw%kbHk0jrYZ=z:@v?L-)d/p|qMaeCqY.0hpFp^0&tY]#Ma8fVw<&O');
define('NONCE_SALT',       'N4$P1-= aaY0,<eE|99D=>&lFeJ7Y=1!x//=l Kv@!D1!C6n&uMA=j/%SjAay`(Z');
define('WP_MEMORY_LIMIT', '299M');
/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);
set_time_limit(9000);
define( 'DUPLICATOR_AUTH_KEY', 'f5eZ),sjr4sVTf=(wc#.pe#6PE#8go]dvEJO <k0)zm&%QnhQ #k|P,re$ta=!H~' );
/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
