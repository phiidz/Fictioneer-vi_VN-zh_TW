<?php
/**
 * Plugin Name: Fictioneer Admin Guard
 * Description: Enforces an admin allowlist by user ID; if an unlisted user has admin-like privileges,
 * their per-user caps are wiped, they are downgraded, logged out, and the request is terminated. This
 * is no protection against arbitrary code execution or other vectors, only injected admins. Define the
 * allowlist array as `define( 'FICTIONEER_ADMIN_ID_ALLOWLIST', [...] )` into your config.php!
 * Version: 1.0.0
 * Author: Tetrakern
 * Author URI: https://github.com/Tetrakern
 * License: GNU General Public License v3.0 or later
 * License URI: http://www.gnu.org/licenses/gpl.html
 */


defined( 'ABSPATH' ) OR exit;

/**
 * Return allowlisted admin user IDs.
 *
 * @since 1.0.0
 *
 * @return array Array of user IDs (ID => true).
 */

function fictioneer_mu_003_admin_allowlist() : array {
  static $list = null;

  if ( $list !== null ) {
    return $list;
  }

  $ids = [1]; // Main admin

  if ( defined( 'FICTIONEER_ADMIN_ID_ALLOWLIST' ) ) {
    $extra = constant( 'FICTIONEER_ADMIN_ID_ALLOWLIST' );

    if ( is_int( $extra ) ) {
      $extra = [ $extra ];
    }

    if ( is_array( $extra ) ) {
      $ids = array_merge( $ids, array_map( 'absint', $extra ) );
    }
  }

  $ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
  $list = array_fill_keys( $ids, true );

  return $list;
}

/**
 * Determine whether a user is "admin-like".
 *
 * @since 1.0.0
 *
 * @param WP_User $user  User object.
 *
 * @return bool True if user has admin-like capabilities, false if not.
 */

function fictioneer_mu_003_user_is_admin_like( $user ) : bool {
  if ( empty( $user->ID ) ) {
    return false;
  }

  if ( in_array( 'administrator', (array) $user->roles, true ) ) {
    return true;
  }

  $caps = (array) $user->caps;

  return (
    ! empty( $caps['administrator'] ) ||
    ! empty( $caps['manage_options'] ) ||
    ! empty( $caps['edit_users'] ) ||
    ! empty( $caps['promote_users'] ) ||
    ! empty( $caps['delete_users'] ) ||
    ! empty( $caps['install_plugins'] ) ||
    ! empty( $caps['update_plugins'] ) ||
    ! empty( $caps['edit_plugins'] ) ||
    ! empty( $caps['switch_themes'] ) ||
    ! empty( $caps['edit_theme_options'] ) ||
    ! empty( $caps['update_core'] )
  );
}

/**
 * Wipe all per-user caps (including any role keys) for this site.
 *
 * @since 1.0.0
 *
 * @param WP_User $user  User object.
 *
 * @return bool True if anything changed.
 */

function fictioneer_mu_003_wipe_user_caps( $user ) : bool {
  $caps = (array) $user->caps;

  if ( empty( $caps ) ) {
    return false;
  }

  update_user_meta( $user->ID, $user->cap_key, [] );

  $user->caps = []; // In-memory user

  if ( method_exists( $user, 'get_role_caps' ) ) {
    $user->get_role_caps(); // Rebuild in-memory user
  }

  return true;
}

/**
 * Terminate the request.
 *
 * @since 1.0.0
 *
 * @param int $user_id  User ID.
 */

function fictioneer_mu_003_terminate_request( $user_id ) : void {
  $message = sprintf(
    'Security Policy: User ID %d had disallowed administrative privileges and has been neutralized.',
    $user_id
  );

  if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( '[Fictioneer MU Admin Guard] ' . $message );
  }

  if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
    wp_send_json_error( array( 'message' => $message ), 403 );
  }

  if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
    wp_send_json_error( array( 'message' => $message ), 403 );
  }

  if ( function_exists( 'wp_die' ) ) {
    wp_die( esc_html( $message ), 'Forbidden', array( 'response' => 403 ) );
  }

  status_header( 403 );
  exit;
}

/**
 * Guard admin access.
 *
 * @since 1.0.0
 */

function fictioneer_mu_003_guard_admin_access() : void {
  if ( ! defined( 'FICTIONEER_ADMIN_ID_ALLOWLIST' ) ) {
    return;
  }

  $user = wp_get_current_user();

  if ( ! ( $user instanceof WP_User ) || empty( $user->ID ) ) {
    return;
  }

  $user_id = (int) $user->ID;

  if ( $user_id === 1 ) {
    return;
  }

  if ( is_multisite() && is_super_admin( $user_id ) ) {
    return;
  }

  $allowed = fictioneer_mu_003_admin_allowlist();

  if ( isset( $allowed[ $user_id ] ) ) {
    return;
  }

  if ( ! fictioneer_mu_003_user_is_admin_like( $user ) ) {
    return;
  }

  fictioneer_mu_003_wipe_user_caps( $user );

  $user->set_role( 'subscriber' );

  wp_logout();

  fictioneer_mu_003_terminate_request( $user_id );
}
add_action( 'plugins_loaded', 'fictioneer_mu_003_guard_admin_access', 1 );
add_action( 'init', 'fictioneer_mu_003_guard_admin_access', 0 ); // Fallback

/**
 * Cleanup on user events.
 *
 * @since 1.0.0
 *
 * @param int $user_id  User ID.
 */

function fictioneer_mu_003_guard_admin_user_event( $user_id ) : void {
  if ( ! defined( 'FICTIONEER_ADMIN_ID_ALLOWLIST' ) ) {
    return;
  }

  $user_id = (int) $user_id;

  if ( $user_id < 1 || $user_id === 1 ) {
    return;
  }

  if ( is_multisite() && is_super_admin( $user_id ) ) {
    return;
  }

  $allowed = fictioneer_mu_003_admin_allowlist();

  if ( isset( $allowed[ $user_id ] ) ) {
    return;
  }

  $user = get_user_by( 'id', $user_id );

  if ( ! ( $user instanceof WP_User ) ) {
    return;
  }

  if ( fictioneer_mu_003_user_is_admin_like( $user ) ) {
    fictioneer_mu_003_wipe_user_caps( $user );

    $user->set_role( 'subscriber' );

    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
      error_log( sprintf( '[Fictioneer MU Admin Guard] Neutralized unlisted admin-like user ID %d on user event.', $user_id ) );
    }
  }
}
add_action( 'user_register', 'fictioneer_mu_003_guard_admin_user_event', 10, 1 );
add_action( 'set_user_role', 'fictioneer_mu_003_guard_admin_user_event', 10, 1 );
