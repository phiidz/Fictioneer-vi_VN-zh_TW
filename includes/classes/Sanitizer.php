<?php

namespace Fictioneer;

use Fictioneer\Traits\Singleton_Trait;

defined( 'ABSPATH' ) OR exit;

class Sanitizer {
  use Singleton_Trait;

  /**
   * Sanitize a date format string.
   *
   * @since 5.34.0
   * @link https://www.php.net/manual/en/datetime.format.php
   *
   * @param string $format  The string to be sanitized.
   *
   * @return string The sanitized value.
   */

  public static function sanitize_date_format( string $format ) : string {
    if ( ! $format ) {
      return '';
    }

    static $allowed = 'dDjlNSwzWFmMntLoYyaABgGhHisuvIeOTZcrU';

    $format = (string) $format;
    $len = strlen( $format );
    $output = '';

    for ( $i = 0; $i < $len; $i++ ) {
      $char = $format[ $i ];

      if ( $char === '\\' && isset( $format[ $i + 1 ] ) ) {
        $output .= '\\' . $format[ ++$i ];

        continue;
      }

      if ( strpos( $allowed, $char ) !== false ) {
        $output .= $char;

        continue;
      }

      $output .= $char;
    }

    return $output;
  }

  /**
   * Sanitize an integer with options for default, minimum, and maximum.
   *
   * @since 5.34.0
   *
   * @param mixed    $value    The value to be sanitized.
   * @param mixed    $default  Optional. Fallback value. Default 0.
   * @param int|null $min      Optional. Minimum value. Default is no minimum.
   * @param int|null $max      Optional. Maximum value. Default is no maximum.
   *
   * @return int The sanitized integer.
   */

  public static function sanitize_integer( mixed $value, mixed $default = 0, ?int $min = null, ?int $max = null ) : int {
    // Catch customizer sanitizer second parameter
    if ( $default instanceof \WP_Customize_Setting ) {
      $default = $default->default;
    }

    $default = ( filter_var( $default, FILTER_VALIDATE_INT ) === false ) ? 0 : (int) $default;

    // Remove leading/trailing spaces
    if ( is_string( $value ) ) {
      $value = trim( $value );
    }

    // Validate as integer-like value
    if ( $value === '' || filter_var( $value, FILTER_VALIDATE_INT ) === false ) {
      return $default;
    }

    // Cast to integer
    $value = (int) $value;

    // Apply minimum limit if specified
    if ( $min !== null && $value < $min ) {
      return $min;
    }

    // Apply maximum limit if specified
    if ( $max !== null && $value > $max ) {
      return $max;
    }

    return $value;
  }

  /**
   * Sanitize an integer to be 1+ with options for default and maximum.
   *
   * @since 5.34.0
   *
   * @param mixed    $value    The value to be sanitized.
   * @param int      $default  Optional. Fallback value. Default 1.
   * @param int|null $max      Optional. Maximum value. Default is no maximum.
   *
   * @return int The sanitized integer.
   */

  public static function sanitize_integer_one_up( mixed $value, int $default = 1, ?int $max = null ) : int {
    return self::sanitize_integer( $value, max( 1, $default ), 1, $max  );
  }

  /**
   * Sanitize words per minute setting (min 200).
   *
   * @since 5.34.0
   *
   * @param mixed $value  The value to be sanitized.
   *
   * @return int The sanitized integer.
   */

  public static function sanitize_integer_words_per_minute( mixed $value ) : int {
    return self::sanitize_integer( $value, 200, 200 );
  }
}
