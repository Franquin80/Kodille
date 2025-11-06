<?php
/**
 * Astra Child - functions.php (KORJATTU JA VAKAA VERSIO)
 * Huom: Lisää GOOGLE_MAPS_API_KEY wp-config.php-tiedostoon!
 */

/* -------------------------------------------------
 * 0) Ladataan omat funktiot (KORJATTU JA OIKEA VERSIO)
 * ------------------------------------------------- */

  // TÄMÄ LADATAAN AINA (Shortcode tarvitsee sitä)
require_once get_stylesheet_directory() . '/includes/google-places-helpers.php';

// TÄMÄ LADATAAN VAIN ADMIN-PUOLELLA
if (is_admin()) {
    require_once get_stylesheet_directory() . '/includes/palveluntarjoajahaku.php';  // Massatuonti (vain admin)
}

/* -------------------------------------------------
 * 1) Shortcode: [tuo_palveluntarjoajat hakusana="" paikkakunta="" koordinaatit="lat,lng" max="5"]
 * (Päivitetty näyttämään kotisivu ja asettamaan $kodille_hakusana)
 * ------------------------------------------------- */
add_shortcode('tuo_palveluntarjoajat', function ($atts) {
    // Tarkistetaan, että tarvittavat apufunktiot ovat käytettävissä.
    if (!function_exists('kodille_tallenna_palveluntarjoaja_googlesta') || !function_exists('kodille_hae_place_details')) {
        return '<div class="notice notice-error">Sisäinen virhe: Apufunktioita (tallennus/details) ei ole ladattu. Tarkista includes-tiedostot.</div>';
    }

    $atts = shortcode_atts(array(
        'hakusana'     => '',
        'paikkakunta'  => '',
        'koordinaatit' => '65.0121,25.4651', // Oulu oletuksena
        'max'          => 5,
    ), $atts);

    // --- LISÄTTY ÄLYKÄS HAKUSANAN ASETUS ---
    // Asetetaan globaali muuttuja, jota tallennusfunktio voi käyttää
    global $kodille_hakusana;
    $kodille_hakusana = $atts['hakusana'];
    // ------------------------------------

    if (empty($atts['hakusana']) || empty($atts['paikkakunta'])) {
        return '<div class="notice notice-error">Puuttuva hakusana tai paikkakunta.</div>';
    }
    if (!defined('GOOGLE_MAPS_API_KEY') || !GOOGLE_MAPS_API_KEY) {
        return '<div class="notice notice-error">Google API -avain puuttuu.</div>';
    }

    $query    = urlencode(trim($atts['hakusana'] . ' ' . $atts['paikkakunta']));
    $coords   = explode(',', $atts['koordinaatit']);
    $latlng   = (count($coords) === 2) ? trim($coords[0]) . ',' . trim($coords[1]) : '65.0121,25.4651';

    $textsearch_url = add_query_arg(array(
        'query'    => $query,
        'location' => $latlng,
        'radius'   => 10000,
        'key'      => GOOGLE_MAPS_API_KEY,
    ), 'https://maps.googleapis.com/maps/api/place/textsearch/json');

    $res = wp_remote_get($textsearch_url, array('timeout' => 20));
    if (is_wp_error($res)) {
        return '<div class="notice notice-error">API-kutsu epäonnistui: ' . esc_html($res->get_error_message()) . '</div>';
    }

    $data = json_decode(wp_remote_retrieve_body($res), true);

    if (isset($data['status']) && $data['status'] !== 'OK' && $data['status'] !== 'ZERO_RESULTS') {
        return '<div style="color:red;">Google API Status: ' . esc_html($data['status']) .
               ' ' . (!empty($data['error_message']) ? esc_html($data['error_message']) : '') . '</div>';
    }

    if (empty($data['results'])) {
        return '<div>Ei tuloksia haulla "' . esc_html($atts['hakusana']) . ' ' . esc_html($atts['paikkakunta']) . '".</div>';
    }

    $results = array_slice($data['results'], 0, (int)$atts['max']);
    $out     = '<ul class="tuodut-palveluntarjoajat">';

    // TÄMÄ OSIO ON MUUTETTU TULOSTAMAAN LISÄTIETOJA:
    foreach ($results as $place) {
        $details = kodille_hae_place_details($place);
        $post_id = kodille_tallenna_palveluntarjoaja_googlesta($place, $details);
        
        if ($post_id) {
            
            // 1. HAE TALLENNETUT ACF-KENTÄT
            $phone = get_field('puhelinnumero', $post_id);
            $website = get_field('kotisivu', $post_id); // Lisätty kotisivun haku
            $services_field = get_field('tarjotut_palvelut', $post_id);
            
            // 2. KÄSITTELE RELATIONSHIP-KENTÄT (TARJOTUT PALVELUT)
            $service_names = [];
            if (!empty($services_field) && is_array($services_field)) {
                foreach ($services_field as $service_post) {
                    if ($service_post) { // Varmistus, että objekti on validi
                        $service_names[] = get_the_title($service_post);
                    }
                }
            }
            $services_out = !empty($service_names) ? implode(', ', $service_names) : 'Ei määritelty';

            // 3. RAKENNA TULOSTUS
            $out .= '<li>';
            $out .= '<h3><a href="' . esc_url(get_permalink($post_id)) . '">' . esc_html(get_the_title($post_id)) . '</a></h3>';
            
            if ($phone) {
                $out .= '<p><strong>Puhelin:</strong> ' . esc_html($phone) . '</p>';
            }
            // --- LISÄTTY KOTISIVUN TULOSTUS ---
            if ($website) {
                $out .= '<p><strong>Kotisivu:</strong> <a href="' . esc_url($website) . '" target="_blank" rel="noopener">' . esc_html(wp_parse_url($website, PHP_URL_HOST) ?? $website) . '</a></p>';
            }
            // ---------------------------------
            $out .= '<p><strong>Tarjotut palvelut:</strong> ' . esc_html($services_out) . '</p>';

            $out .= '</li>';
        }
    }
    // LOPETUS

    $out .= '</ul>';
    return $out;
});

/* -------------------------------------------------
 * 2) Tyylit ja skriptit
 * ------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'astra-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('astra-parent-style'),
        wp_get_theme()->get('Version')
    );

    if (is_post_type_archive('palveluntarjoajat') || is_front_page()) {
        $child_js = get_stylesheet_directory() . '/js/custom.js';
        wp_enqueue_script(
            'kodille-custom',
            get_stylesheet_directory_uri() . '/js/custom.js',
            array('jquery'),
            file_exists($child_js) ? filemtime($child_js) : '1.0.0',
            true
        );
        wp_localize_script('kodille-custom', 'kodilleAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => esc_url_raw(rest_url()),
            'nonce'    => wp_create_nonce('kodille_nonce'),
        ));
    }

    if (defined('GOOGLE_MAPS_API_KEY') && GOOGLE_MAPS_API_KEY) {
        wp_enqueue_script(
            'google-maps',
            'https://maps.googleapis.com/maps/api/js?key=' . urlencode(GOOGLE_MAPS_API_KEY) . '&libraries=places',
            array(),
            null,
            true
        );
    }
});

/* -------------------------------------------------
 * 3) Reititys / query var
 * ------------------------------------------------- */
add_action('init', function () {
    add_rewrite_rule(
        '^opas/([^/]+)/([^/]+)/?$',
        'index.php?post_type=opas&name=$matches[1]&paikkakunta=$matches[2]',
        'top'
    );
});
add_filter('query_vars', function ($vars) {
    $vars[] = 'paikkakunta';
    return $vars;
});

/* -------------------------------------------------
 * 4) Admin-debug footer
 * ------------------------------------------------- */
add_action('wp_footer', function () {
    if (!is_user_logged_in() || !current_user_can('administrator')) return;
    if (!is_singular()) return;
    global $post, $template;
    echo '<div style="position:fixed;bottom:0;left:0;background:#000;color:#fff;padding:10px;z-index:9999;font:12px/1.4 monospace">';
    echo 'Post type: ' . esc_html(get_post_type($post)) . '<br>';
    echo 'Template: ' . esc_html(basename($template)) . '<br>';
    echo 'Post ID: ' . intval($post->ID) . '<br>';
    echo '</div>';
});

/* -------------------------------------------------
 * 5) RSS pois
 * ------------------------------------------------- */
function kodille_disable_rss_feeds() {
    wp_die(
        wp_kses_post(
            'No feed available, please visit the <a href="' . esc_url(home_url('/')) . '">homepage</a>!'
        )
    );
}
add_action('do_feed', 'kodille_disable_rss_feeds', 1);
add_action('do_feed_rdf', 'kodille_disable_rss_feeds', 1);
add_action('do_feed_rss', 'kodille_disable_rss_feeds', 1);
add_action('do_feed_rss2', 'kodille_disable_rss_feeds', 1);
add_action('do_feed_atom', 'kodille_disable_rss_feeds', 1);
add_action('do_feed_rss2_comments', 'kodille_disable_rss_feeds', 1);
add_action('do_feed_atom_comments', 'kodille_disable_rss_feeds', 1);
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);

// KORJATTU: YLIMÄÄRÄINEN } POISTETTU TÄSTÄ LOPUSTA
