<?php
/**
 * Helper functions for working with Google Places results.
 *
 * These implementations focus on being safe defaults so the theme works out of the box
 * even before a full Google Places integration is completed.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('kodille_normalize_place_array')) {
    /**
     * Normalises a Places API result to a plain associative array.
     */
    function kodille_normalize_place_array($place)
    {
        if (is_array($place)) {
            return $place;
        }

        if ($place instanceof Traversable) {
            return iterator_to_array($place);
        }

        return array();
    }
}

if (!function_exists('kodille_hae_place_details')) {
    /**
     * Returns a minimal details array for a Google Places result.
     *
     * The official Place Details request requires an additional API call.
     * For now we expose the information that is already available in the
     * Text Search response and keep the integration extensible.
     */
    function kodille_hae_place_details($place)
    {
        $place = kodille_normalize_place_array($place);

        return array(
            'formatted_address'    => isset($place['formatted_address']) ? sanitize_text_field($place['formatted_address']) : '',
            'rating'               => isset($place['rating']) ? floatval($place['rating']) : null,
            'user_ratings_total'   => isset($place['user_ratings_total']) ? intval($place['user_ratings_total']) : 0,
            'business_status'      => isset($place['business_status']) ? sanitize_text_field($place['business_status']) : '',
            'geometry'             => isset($place['geometry']) ? $place['geometry'] : array(),
            'place_id'             => isset($place['place_id']) ? sanitize_text_field($place['place_id']) : '',
            'name'                 => isset($place['name']) ? sanitize_text_field($place['name']) : '',
        );
    }
}

if (!function_exists('kodille_tallenna_palveluntarjoaja_googlesta')) {
    /**
     * Creates or updates a `palveluntarjoajat` post based on a Google Places result.
     *
     * @return int Post ID on success, 0 on failure.
     */
    function kodille_tallenna_palveluntarjoaja_googlesta($place, $details)
    {
        $place     = kodille_normalize_place_array($place);
        $details   = is_array($details) ? $details : array();
        $place_id  = isset($place['place_id']) ? sanitize_text_field($place['place_id']) : '';
        $name      = isset($place['name']) ? sanitize_text_field($place['name']) : '';

        if (empty($place_id) || empty($name)) {
            return 0;
        }

        $existing = get_posts(array(
            'post_type'      => 'palveluntarjoajat',
            'posts_per_page' => 1,
            'post_status'    => array('publish', 'draft', 'pending'),
            'meta_key'       => 'google_place_id',
            'meta_value'     => $place_id,
            'fields'         => 'ids',
        ));

        $post_data = array(
            'post_title'  => $name,
            'post_type'   => 'palveluntarjoajat',
            'post_status' => 'draft',
        );

        if (!empty($existing)) {
            $post_data['ID'] = (int) $existing[0];
            $post_id = wp_update_post($post_data, true);
        } else {
            $post_id = wp_insert_post($post_data, true);
        }

        if (is_wp_error($post_id) || !$post_id) {
            return 0;
        }

        update_post_meta($post_id, 'google_place_id', $place_id);

        if (isset($place['rating'])) {
            update_post_meta($post_id, 'google_rating', floatval($place['rating']));
        }
        if (isset($place['user_ratings_total'])) {
            update_post_meta($post_id, 'google_user_ratings_total', intval($place['user_ratings_total']));
        }
        if (!empty($details['formatted_address'])) {
            update_post_meta($post_id, 'google_formatted_address', $details['formatted_address']);
        }
        if (!empty($details['business_status'])) {
            update_post_meta($post_id, 'google_business_status', $details['business_status']);
        }

        if (function_exists('update_field')) {
            if (!empty($details['formatted_address'])) {
                update_field('kayntiosoite', $details['formatted_address'], $post_id);
            }
            if (!empty($details['rating'])) {
                update_field('google_rating', $details['rating'], $post_id);
            }
        }

        return (int) $post_id;
    }
}
