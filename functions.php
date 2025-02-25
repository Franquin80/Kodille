<?php
// Lataa pääteeman tyylit ja JavaScript
function astra_child_enqueue_styles() {
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_script('custom-js', get_template_directory_uri() . '/custom.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_styles');

function hae_paikkakunnat() {
    if (isset($_GET['maakunta'])) {
        $maakunta_id = intval($_GET['maakunta']);
        $paikkakunnat = get_terms(array(
            'taxonomy'   => 'sijainnit',
            'parent'     => $maakunta_id,
            'hide_empty' => false,
        ));

        echo '<option value="">Kaikki paikkakunnat</option>';
        if (!empty($paikkakunnat) && !is_wp_error($paikkakunnat)) {
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

// Hae palvelut valitun palvelukategorian perusteella
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
