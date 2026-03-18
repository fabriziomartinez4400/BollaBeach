<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get affiliate text with custom terminology
 *
 * @param string $default_text The default text to use if no custom terminology is set
 * @param bool $plural Whether to return plural form
 * @return string The affiliate text
 */
if ( ! function_exists( 'wcusage_get_affiliate_text' ) ) {
    function wcusage_get_affiliate_text( $default_text = 'Affiliate', $plural = false ) {

        $custom_term = wcusage_get_setting_value( 'wcusage_field_custom_affiliate_text', '' );
        $custom_term_plural = wcusage_get_setting_value( 'wcusage_field_custom_affiliates_text', '' );

        if(!$custom_term ) {
            return $default_text; // Return default if no custom term is set
        }
        
        if ( empty( $custom_term ) ) {
            $custom_term = $default_text;
        }
        
        if ( $plural ) {
            // Use custom plural term if available, otherwise use simple pluralization
            if ( !empty( $custom_term_plural ) ) {
                $custom_term = $custom_term_plural;
            } else {
                // Simple pluralization - add 's' if not already plural
                if ( substr( $custom_term, -1 ) !== 's' ) {
                    $custom_term .= 's';
                }
            }
        }

        // Keep same case as the default text
        if ( $default_text === strtoupper( $default_text ) ) {
            $custom_term = strtoupper( $custom_term );
        } elseif ( $default_text === strtolower( $default_text ) ) {
            $custom_term = strtolower( $custom_term );
        } elseif ( $default_text === ucfirst( strtolower( $default_text ) ) ) {
            $custom_term = ucfirst( strtolower( $custom_term ) );
        } elseif ( $default_text === ucwords( strtolower( $default_text ) ) ) {
            $custom_term = ucwords( strtolower( $custom_term ) );
        }
        
        return $custom_term;
        
    }
}