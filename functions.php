// functions.php
<?php
// Lataa teeman resurssit
function astra_child_enqueue_styles() {
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');
    if (is_archive('palveluntarjoajat') || is_front_page()) {
        wp_enqueue_script('custom-js', get_template_directory_uri() . '/custom.js', array('jquery'), null, true);
        wp_localize_script('custom-js', 'ajax_params', array(
            'rest_url' => rest_url(),
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_styles');

// Rekisteröi sijainnit REST API:lle (säilytetään, jos REST API käytössä muualla)
function register_sijainnit_taxonomy() {
    register_taxonomy('sijainnit', 'palveluntarjoajat', array(
        'label'        => 'Sijainnit',
        'hierarchical' => true,
        'show_in_rest' => true,
        'rest_base'    => 'sijainnit',
    ));
}
add_action('init', 'register_sijainnit_taxonomy');

// Hae paikkakunnat AJAXilla
function hae_paikkakunnat() {
    if (isset($_GET['maakunta'])) {
        $maakunta_id = intval($_GET['maakunta']);
        $paikkakunnat = get_terms(array(
            'taxonomy'   => 'sijainnit',
            'parent'     => $maakunta_id,
            'hide_empty' => false,
        ));

        if (!empty($paikkakunnat) && !is_wp_error($paikkakunnat)) {
            echo '<option value="">Kaikki paikkakunnat</option>';
            foreach ($paikkakunnat as $paikkakunta) {
                echo '<option value="' . esc_attr($paikkakunta->term_id) . '">' . esc_html($paikkakunta->name) . '</option>';
            }
        } else {
            echo '<option value="">Ei paikkakuntia</option>';
        }
    }
    wp_die();
}
add_action('wp_ajax_hae_paikkakunnat', 'hae_paikkakunnat');
add_action('wp_ajax_nopriv_hae_paikkakunnat', 'hae_paikkakunnat');

// Hae palvelut
function hae_palvelut() {
    if (isset($_GET['palvelukategoria'])) {
        $palvelukategoria_id = intval($_GET['palvelukategoria']);
        $services = get_posts(array(
            'post_type'      => 'palvelut',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'palvelukategoriat',
                    'field'    => 'term_id',
                    'terms'    => $palvelukategoria_id,
                ),
            ),
        ));

        if (!empty($services)) {
            echo '<option value="">Kaikki palvelut</option>';
            foreach ($services as $service) {
                echo '<option value="' . esc_attr($service->ID) . '">' . esc_html($service->post_title) . '</option>';
            }
        } else {
            echo '<option value="">Ei palveluita</option>';
        }
    } else {
        echo '<option value="">Valitse kategoria ensin</option>';
    }
    wp_die();
}
add_action('wp_ajax_hae_palvelut', 'hae_palvelut');
add_action('wp_ajax_nopriv_hae_palvelut', 'hae_palvelut');

// Hae palveluntarjoajat
function hae_palveluntarjoajat() {
    $args = array(
        'post_type'      => 'palveluntarjoajat',
        'posts_per_page' => -1,
    );
    if (!empty($_GET['paikkakunta'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'sijainnit',
                'field'    => 'term_id',
                'terms'    => intval($_GET['paikkakunta']),
            ),
        );
    }
    if (!empty($_GET['service'])) {
        $args['post__in'] = array(intval($_GET['service']));
    }
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo '<div class="provider-result">';
            echo '<h2>' . get_the_title() . '</h2>';
            echo '<p>' . get_the_excerpt() . '</p>';
            echo '<a href="' . get_permalink() . '">Siirry sivulle</a>';
            echo '</div>';
        }
    } else {
        echo '<p>Ei palveluntarjoajia löydetty.</p>';
    }
    wp_die();
}
add_action('wp_ajax_hae_palveluntarjoajat', 'hae_palveluntarjoajat');
add_action('wp_ajax_nopriv_hae_palveluntarjoajat', 'hae_palveluntarjoajat');
// Lisää Google Maps API-avain WordPressiin
define('GOOGLE_MAPS_API_KEY', 'sinun-api-avaimesi');

function kodille_enqueue_scripts() {
    wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . GOOGLE_MAPS_API_KEY . '&libraries=places', [], null, true);
    wp_enqueue_script('kodille-custom', get_template_directory_uri() . '/js/custom.js', ['google-maps'], '1.0', true);
}
add_action('wp_enqueue_scripts', 'kodille_enqueue_scripts');

?>
