<?php
// Lataa pääteeman tyylit
function astra_child_enqueue_styles() {
    // Lataa pääteeman tyylit
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');

    // Lataa mukautettu JavaScript-tiedosto
    wp_enqueue_script('custom-js', get_template_directory_uri() . '/custom.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_styles');
?>