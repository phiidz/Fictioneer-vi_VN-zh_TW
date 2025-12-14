<?php

namespace Fictioneer;

use Fictioneer\Traits\Singleton_Trait;

defined( 'ABSPATH' ) OR exit;

class Utils {
  use Singleton_Trait;

  /**
   * Wrapper for wp_parse_list() with optional sanitizer.
   *
   * @since 5.34.0
   *
   * @param array|string $input_list  List of values.
   *
   * @return array Array of values.
   */

  public static function parse_list( array|string $input_list, string|null $sanitizer = null ) : array {
    $values = wp_parse_list( $input_list ?? '' );

    if ( $sanitizer && is_callable( $sanitizer ) ) {
      $values = array_map( $sanitizer, $values );
      $values = array_filter( $values, 'strlen' );
      $values = array_values( $values );
    }

    return $values;
  }
}
