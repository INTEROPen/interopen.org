<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Upgrade Throttle
 * @param int $threshold
 * @option int $compare
 * @return bool
 */
function ninja_forms_three_throttle( $threshold = 5 ) {
    $compare = get_option( 'ninja_forms_three_throttle', 0 );
    if( ! $compare ){
        $compare = rand( 1, 100 );
        update_option( 'ninja_forms_three_throttle', $compare );
    }
    return ( $threshold >= $compare );
}
