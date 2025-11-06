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
<?php get_header(); 

// Tunnista oikea sijainti-taxonomia
$location_taxonomy = taxonomy_exists('toiminta-alueet') ? 'toiminta-alueet' : 'sijainnit';
?>

<div class="provider-archive" style="padding: 40px; font-family: Arial, sans-serif;">
    <h1 style="text-align: center; font-size: 2.5rem; margin-bottom: 30px;">Etsi palveluntarjoajia</h1>

    <!-- Hakulomake -->
    <form method="get" action="" id="provider-search-form">
        <label for="maakunta">Valitse maakunta:</label>
        <select name="maakunta" id="maakunta" style="width: 100%; padding: 10px; margin-bottom: 20px;">
            <option value="">Valitse maakunta</option>
            <?php
            $maakunnat = get_terms(array(
                'taxonomy'   => $location_taxonomy,
                'parent'     => 0,
                'hide_empty' => false,
            ));
            foreach ($maakunnat as $maakunta) {
                $selected = (isset($_GET['maakunta']) && $_GET['maakunta'] == $maakunta->term_id) ? 'selected' : '';
                echo '<option value="' . esc_attr($maakunta->term_id) . '" ' . $selected . '>' . esc_html($maakunta->name) . '</option>';
            }
            ?>
        </select>

        <label for="paikkakunta">Valitse paikkakunta:</label>
        <select name="paikkakunta" id="paikkakunta" style="width: 100%; padding: 10px; margin-bottom: 20px;" <?php echo empty($_GET['maakunta']) ? 'disabled' : ''; ?>>
            <option value="">Valitse ensin maakunta</option>
            <?php
            // Jos maakunta on valittu, näytä sen paikkakunnat
            if (!empty($_GET['maakunta'])) {
                $paikkakunnat = get_terms(array(
                    'taxonomy'   => $location_taxonomy,
                    'parent'     => intval($_GET['maakunta']),
                    'hide_empty' => false,
                ));
                foreach ($paikkakunnat as $paikkakunta) {
                    $selected = (isset($_GET['paikkakunta']) && $_GET['paikkakunta'] == $paikkakunta->term_id) ? 'selected' : '';
                    echo '<option value="' . esc_attr($paikkakunta->term_id) . '" ' . $selected . '>' . esc_html($paikkakunta->name) . '</option>';
                }
            }
            ?>
        </select>

        <label for="palvelukategoria">Valitse palvelukategoria:</label>
        <select name="palvelukategoria" id="palvelukategoria" style="width: 100%; padding: 10px; margin-bottom: 20px;">
            <option value="">Valitse kategoria</option>
            <?php
            $palvelukategoriat = get_terms(array(
                'taxonomy'   => 'palvelukategoriat',
                'parent'     => 0,
                'hide_empty' => false,
            ));
            foreach ($palvelukategoriat as $kategoria) {
                $selected = (isset($_GET['palvelukategoria']) && $_GET['palvelukategoria'] == $kategoria->term_id) ? 'selected' : '';
                echo '<option value="' . esc_attr($kategoria->term_id) . '" ' . $selected . '>' . esc_html($kategoria->name) . '</option>';
            }
            ?>
        </select>

        <label for="service">Valitse palvelu:</label>
        <select name="service" id="service" style="width: 100%; padding: 10px; margin-bottom: 20px;" <?php echo empty($_GET['palvelukategoria']) ? 'disabled' : ''; ?>>
            <option value="">Valitse ensin kategoria</option>
            <?php
            // Jos kategoria on valittu, näytä sen palvelut
            if (!empty($_GET['palvelukategoria'])) {
                $palvelut = get_terms(array(
                    'taxonomy'   => 'palvelukategoriat',
                    'parent'     => intval($_GET['palvelukategoria']),
                    'hide_empty' => false,
                ));
                foreach ($palvelut as $palvelu) {
                    $selected = (isset($_GET['service']) && $_GET['service'] == $palvelu->term_id) ? 'selected' : '';
                    echo '<option value="' . esc_attr($palvelu->term_id) . '" ' . $selected . '>' . esc_html($palvelu->name) . '</option>';
                }
            }
            ?>
        </select>

        <button type="submit" style="padding: 10px 20px; background-color: #0073aa; color: white; border: none; border-radius: 5px;">Hae</button>
    </form>

    <!-- Tulosten näyttö -->
    <div id="provider-results" style="margin-top: 40px;">
        <?php
        if (!empty($_GET['maakunta']) || !empty($_GET['paikkakunta']) || !empty($_GET['palvelukategoria']) || !empty($_GET['service'])) {
            
            $tax_query = array('relation' => 'AND');
            
            // Sijainti
            if (!empty($_GET['paikkakunta'])) {
                $tax_query[] = array(
                    'taxonomy' => $location_taxonomy,
                    'field'    => 'term_id',
                    'terms'    => intval($_GET['paikkakunta']),
                );
            } elseif (!empty($_GET['maakunta'])) {
                // Hae kaikki maakunnan paikkakunnat
                $child_terms = get_term_children(intval($_GET['maakunta']), $location_taxonomy);
                if (!empty($child_terms)) {
                    $tax_query[] = array(
                        'taxonomy' => $location_taxonomy,
                        'field'    => 'term_id',
                        'terms'    => $child_terms,
                        'operator' => 'IN',
                    );
                }
            }
            
            // Palvelu
            if (!empty($_GET['service'])) {
                $tax_query[] = array(
                    'taxonomy' => 'palvelukategoriat',
                    'field'    => 'term_id',
                    'terms'    => intval($_GET['service']),
                );
            } elseif (!empty($_GET['palvelukategoria'])) {
                // Hae kaikki kategorian palvelut
                $child_terms = get_term_children(intval($_GET['palvelukategoria']), 'palvelukategoriat');
                if (!empty($child_terms)) {
                    $tax_query[] = array(
                        'taxonomy' => 'palvelukategoriat',
                        'field'    => 'term_id',
                        'terms'    => $child_terms,
                        'operator' => 'IN',
                    );
                }
            }

            $query_args = array(
                'post_type'      => 'palveluntarjoajat',
                'posts_per_page' => -1,
                'tax_query'      => $tax_query,
            );

            $provider_query = new WP_Query($query_args);

            if ($provider_query->have_posts()) {
                echo '<h2>Löytyi ' . $provider_query->found_posts . ' palveluntarjoajaa</h2>';
                echo '<ul style="list-style: none; padding: 0;">';
                while ($provider_query->have_posts()) {
                    $provider_query->the_post();
                    echo '<li style="padding: 15px; border-bottom: 1px solid #ddd;">';
                    echo '<h3 style="margin: 0;"><a href="' . get_permalink() . '" style="text-decoration: none; color: #0073aa;">' . get_the_title() . '</a></h3>';
                    
                    if ($paikkakunta_field = get_field('paikkakunta')) {
                        echo '<small>' . esc_html($paikkakunta_field) . '</small><br>';
                    }
                    
                    if ($phone = get_field('puhelinnumero')) {
                        echo '<strong>Puh:</strong> <a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a><br>';
                    }
                    
                    if ($rating = get_field('arvostelut')) {
                        echo '<strong>Arvostelut:</strong> ' . esc_html($rating) . ' / 5 ⭐';
                    }
                    
                    echo '</li>';
                }
                echo '</ul>';
                wp_reset_postdata();
            } else {
                echo '<p>Ei tuloksia valituilla hakuehdoilla.</p>';
            }
        }
        ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const maakuntaSelect = document.getElementById('maakunta');
    const paikkakuntaSelect = document.getElementById('paikkakunta');
    const palvelukategoriaSelect = document.getElementById('palvelukategoria');
    const serviceSelect = document.getElementById('service');
    const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

    function fetchOptions(url, targetSelect, emptyMessage) {
        fetch(url)
            .then(response => response.text())
            .then(data => {
                targetSelect.innerHTML = data || '<option value="">' + emptyMessage + '</option>';
                targetSelect.disabled = false;
            })
            .catch(error => {
                console.error('Virhe haussa:', error);
                targetSelect.innerHTML = '<option value="">Virhe ladattaessa</option>';
            });
    }

    maakuntaSelect.addEventListener('change', function() {
        let maakuntaID = this.value;
        if (maakuntaID === "") {
            paikkakuntaSelect.innerHTML = '<option value="">Valitse ensin maakunta</option>';
            paikkakuntaSelect.disabled = true;
        } else {
            fetchOptions(ajaxUrl + '?action=hae_paikkakunnat&maakunta=' + encodeURIComponent(maakuntaID), paikkakuntaSelect, 'Ei paikkakuntia');
        }
    });

    palvelukategoriaSelect.addEventListener('change', function() {
        let kategoriaID = this.value;
        if (kategoriaID === "") {
            serviceSelect.innerHTML = '<option value="">Valitse ensin kategoria</option>';
            serviceSelect.disabled = true;
        } else {
            fetchOptions(ajaxUrl + '?action=hae_palvelut&palvelukategoria=' + encodeURIComponent(kategoriaID), serviceSelect, 'Ei palveluita');
        }
    });
});
</script>

<?php get_footer();

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
