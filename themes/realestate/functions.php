<?php
/**
 * Real Estate Theme Functions
 * Additional theme-specific functions and hooks
 */

/**
 * Get formatted price
 */
if (!function_exists('realestate_format_price')) {
    function realestate_format_price($price, $currency = '₺') {
        return $currency . number_format($price, 0, '.', '.');
    }
}

/**
 * Get property type label
 */
if (!function_exists('realestate_get_property_type_label')) {
    function realestate_get_property_type_label($type) {
        $types = [
            'house' => __('House'),
            'apartment' => __('Apartment'),
            'villa' => __('Villa'),
            'commercial' => __('Commercial'),
            'land' => __('Land')
        ];
        
        return $types[$type] ?? ucfirst($type);
    }
}

/**
 * Get area with unit
 */
if (!function_exists('realestate_format_area')) {
    function realestate_format_area($area, $unit = 'sqft') {
        $formatted = number_format($area, 0);
        $unitLabel = $unit === 'sqft' ? __('sqft') : __('sqm');
        return $formatted . ' ' . $unitLabel;
    }
}
