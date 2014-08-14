<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */

/**
 * Cookie Settings
 *
 * These settings are used when creating Oxygen-related cookies.
 *
 * cookie_domain      - The domain cookies are restricted to
 * cookie_path        - The path cookies are restricted to
 * cookie_expiration  - Time until the cookie expires
 * cookie_secure      - Only transmit cookies over a secure connection
 * cookie_salt        - Magic salt added to cookies, yum.
 * remember_me_days   - The days to keep a user logged in if they select remember me
 */
$config['cookie_domain'] = '';
$config['cookie_path'] = '/';
$config['cookie_expiration'] = 3600; // 1 Hour
$config['cookie_secure'] = false;
$config['cookie_salt'] = '1234567890abc';
$config['remember_me_days'] = 14;

/**
 * Login Target
 *
 * This is the target URI that a user is sent to after they login.
 */
$config['login_target'] = 'dashboard';

/**
 * Default System Timezone
 */
$config['timezone'] = 'UTC';

/**
 * Resource version (used for Minify cache keys)
 */
$config['resource_version'] = 0;

/**
 * Cache location
 */
$config['cache_dir'] = DOCROOT.'cache';

