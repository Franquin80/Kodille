<?php
/**
 * Google Places API -avustajafunktiot ja tallennus CPT:hen.
 * Sisältää Place Details -haun ja tallennuslogiikan.
 * LOPULLINEN KORJATTU VERSIO
 */
if (!defined('ABSPATH')) exit;

/* -------------------------------------------------
 * 1) Google Place Details -apu
 * ------------------------------------------------- */
function kodille_hae_place_details($place) {
    if (empty($place['place_id']) || !defined('GOOGLE_MAPS_API_KEY')) return array();

    $fields = array(
        'formatted_phone_number',
        'international_phone_number',
        'website',
        'opening_hours',
        'rating',
        'user_ratings_total',
        'address_components',
    );

    $url = add_query_arg(array(
        'place_id' => $place['place_id'],
        'fields'   => implode(',', $fields),
        'key'      => GOOGLE_MAPS_API_KEY,
    ), 'https://maps.googleapis.com/maps/api/place/details/json');

    $res = wp_remote_get($url, array('timeout' => 20)); 
    if (is_wp_error($res)) return array();
    
    $data = json_decode(wp_remote_retrieve_body($res), true);
    return isset($data['result']) ? $data['result'] : array();
}

/* -------------------------------------------------
 * 2) Tallennus CPT:ään + ACF-kenttiin (KORJATTU VERSIO)
 * ------------------------------------------------- */
function kodille_tallenna_palveluntarjoaja_googlesta($place, $details = array()) {
    // Tarkista duplikaatti place_id:llä
    $existing = get_posts(array(
        'post_type'  => 'palveluntarjoajat',
        'meta_key'   => 'google_place_id',
        'meta_value' => isset($place['place_id']) ? $place['place_id'] : '',
        'fields'     => 'ids',
        'numberposts'=> 1,
    ));

    $title = isset($place['name']) ? wp_strip_all_tags($place['name']) : 'Nimetön';
    $postarr = array(
        'post_title'   => $title,
        'post_type'    => 'palveluntarjoajat',
        'post_status'  => 'publish',
    );

    if (!empty($existing)) {
        $post_id = (int)$existing[0];
        wp_update_post(array_merge($postarr, array('ID' => $post_id)));
    } else {
        $post_id = wp_insert_post($postarr);
        if (is_wp_error($post_id) || !$post_id) return 0;
        update_post_meta($post_id, 'google_place_id', sanitize_text_field($place['place_id']));
    }

    // Osoitteen parsinta
    $addr = array('katuosoite' => '', 'postinumero' => '', 'paikkakunta' => '');
    if (!empty($details['address_components'])) {
        $route = ''; $number = '';
        foreach ($details['address_components'] as $comp) {
            if (in_array('route', (array)$comp['types'], true)) $route  = $comp['long_name'];
            if (in_array('street_number', (array)$comp['types'], true)) $number = $comp['long_name'];
            if (in_array('postal_code', (array)$comp['types'], true)) $addr['postinumero'] = $comp['long_name'];
            if (in_array('locality', (array)$comp['types'], true) || in_array('postal_town', (array)$comp['types'], true)) {
                $addr['paikkakunta'] = $comp['long_name'];
            }
        }
        $addr['katuosoite'] = trim($route . ' ' . $number);
    }
    if (!$addr['katuosoite'] && !empty($place['formatted_address'])) {
        $addr['katuosoite'] = $place['formatted_address'];
    }

    // Puhelin, kotisivu, arvostelut
    $phone  = !empty($details['formatted_phone_number']) ? $details['formatted_phone_number'] : (!empty($details['international_phone_number']) ? $details['international_phone_number'] : '');
    $site   = !empty($details['website']) ? esc_url_raw($details['website']) : '';
    $rating = (isset($place['rating']) || isset($details['rating'])) ? max(1, min(5, round((float)($place['rating'] ?? $details['rating']), 1))) : null;


   // ACF-kenttien päivitys
    if (function_exists('update_field')) {
        update_field('palveluntarjoajan_nimi', $title, $post_id);
        if ($addr['katuosoite'])  update_field('katuosoite', $addr['katuosoite'], $post_id);
        if ($addr['postinumero']) update_field('postinumero', $addr['postinumero'], $post_id);
        if ($addr['paikkakunta']) update_field('paikkakunta', $addr['paikkakunta'], $post_id); // Tekstikenttä
        if ($phone)               update_field('puhelinnumero', $phone, $post_id);
        if ($site)                update_field('kotisivu', $site, $post_id);
        if ($rating !== null)     update_field('arvostelut', $rating, $post_id);
    }

    // 🧭 Paikkakunta-taxonomian tallennus (automaattisesti)
    if (!empty($addr['paikkakunta'])) {
        $city_name = sanitize_text_field($addr['paikkakunta']);
        // Yrittää automaattisesti löytää oikean slug-nimen
        $taxonomy_slug = taxonomy_exists('toiminta-alueet') ? 'toiminta-alueet' : 'sijainnit';

        // Luo termi jos sitä ei ole
        $term = term_exists($city_name, $taxonomy_slug);
        if (!$term) {
            $term = wp_insert_term($city_name, $taxonomy_slug);
        }

        // Liitä postaukseen
        if (!is_wp_error($term) && isset($term['term_id'])) {
            wp_set_post_terms($post_id, [(int)$term['term_id']], $taxonomy_slug, true);
        }
    }

    // 🧩 Tarjotut palvelut -kenttä (Älykäs haku + oletus)
    global $kodille_hakusana;
    $palvelu_id = 0;

    // Etsi automaattisesti hakusanan perusteella
    if (!empty($kodille_hakusana)) {
        $palvelu_title = ucfirst($kodille_hakusana); 
        $palvelu_post = get_page_by_title($palvelu_title, OBJECT, 'palvelut');
        if ($palvelu_post && !is_wp_error($palvelu_post)) {
            $palvelu_id = $palvelu_post->ID;
        }
    }

    // Jos ei löytynyt, käytetään oletuksena Rännien puhdistus (ID 274)
    if (!$palvelu_id) {
        $palvelu_id = 274; // Varmistettu oikea ID
    }

    if ($palvelu_id) {
        update_field('tarjotut_palvelut', array((int)$palvelu_id), $post_id);
    }

    return (int)$post_id;
}
// EI YLIMÄÄRÄISTÄ } -MERKKIÄ LOPUSSA
