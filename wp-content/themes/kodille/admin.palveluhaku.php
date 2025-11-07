<?php
if (!defined('ABSPATH')) exit;

// Ladataan vain admin-puolella
if (is_admin()) {

    add_action('admin_menu', function() {
        add_menu_page(
            'Palveluhaku',
            'Palveluhaku',
            'manage_options',
            'palveluhaku',
            'kodille_palveluhaku_page',
            'dashicons-search',
            7
        );
    });

    function kodille_palveluhaku_page() {
        echo '<div class="wrap"><h1>Palveluntarjoajien haku</h1>';

        echo '<div style="background:#fffbe6;border-left:4px solid #ffb900;padding:10px 15px;margin:15px 0 25px;">
            <p><strong>Tämä työkalu hakee Google Places -palveluntarjoajat kaikille paikkakunnille JSON-listan mukaan.</strong></p>
            <ul style="margin:5px 0;">
                <li>🔍 Valitse haluamasi palvelu alasvetovalikosta (esim. <em>Radon mittaus</em>).</li>
                <li>📍 Käsittelee kaikki paikkakunnat tiedostosta <code>palvelu.paikkakunnat.json</code>.</li>
                <li>⏱️ Suositus: 3 tulosta / paikkakunta, 2 s viive.</li>
                <li>⚙️ Tulokset tallentuvat CPT:ään <strong>palveluntarjoajat</strong>.</li>
            </ul>
        </div>';

        // --- Käyttöasetukset lomakkeesta ---
        $hakusana = isset($_POST['hakusana']) ? sanitize_text_field($_POST['hakusana']) : '';
        $max = isset($_POST['max']) ? intval($_POST['max']) : 3;
        $delay = isset($_POST['delay']) ? intval($_POST['delay']) : 2;

        // --- Painiketta painettu? ---
        if (isset($_POST['run_search']) && !empty($hakusana)) {
            echo '<pre>';
            kodille_aja_palveluhaku_koko_suomi($hakusana, $max, $delay);
            echo '</pre>';
        }

        // --- Palveluvalikko (haetaan kaikki julkaistut palvelut) ---
        $palvelut = get_posts([
            'post_type'      => 'palvelut',
            'numberposts'    => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC'
        ]);

        echo '<form method="post" style="margin-top:20px;">';
        echo '<p><label>Valitse palvelu:</label><br>';
        echo '<select name="hakusana" style="width:300px;">';
        echo '<option value="">-- Valitse palvelu --</option>';
        foreach ($palvelut as $palvelu) {
            $selected = ($hakusana === $palvelu->post_title) ? 'selected' : '';
            echo '<option value="' . esc_attr($palvelu->post_title) . '" ' . $selected . '>' . esc_html($palvelu->post_title) . '</option>';
        }
        echo '</select></p>';

        // --- Muut asetukset ---
        echo '<p><label>Tulosten määrä / paikkakunta: 
                <input type="number" name="max" value="' . esc_attr($max) . '" min="1" max="10">
              </label></p>';
        echo '<p><label>Viive (sekuntia hakujen välissä): 
                <input type="number" name="delay" value="' . esc_attr($delay) . '" min="0" max="10">
              </label></p>';
        echo '<p><button type="submit" name="run_search" class="button button-primary">
                Aja haku kaikille paikkakunnille
              </button></p>';
        echo '</form></div>';
    }
}

/**
 * Varsinainen ajo kaikille paikkakunnille
 */
function kodille_aja_palveluhaku_koko_suomi($hakusana = '', $max = 3, $delay = 2) {
    $json_file = get_stylesheet_directory() . '/palvelu.paikkakunnat.json';
    if (!file_exists($json_file)) {
        echo "❌ Paikkakuntatiedostoa ei löydy.";
        return;
    }

    $data = json_decode(file_get_contents($json_file), true);
    if (!$data || !is_array($data)) {
        echo "❌ JSON-luku epäonnistui.";
        return;
    }

    $total = 0;
    foreach ($data as $slug => $city) {
        $paikkakunta = $city['name'];
        echo "🔹 Haetaan: {$hakusana} {$paikkakunta}\n";

        echo do_shortcode('[tuo_palveluntarjoajat hakusana="' . esc_attr($hakusana) . '" paikkakunta="' . esc_attr($paikkakunta) . '" max="' . intval($max) . '"]');
        $total++;

        flush();
        sleep($delay);
    }

    echo "\n✅ Valmis – {$total} paikkakuntaa käsitelty, {$max} tulosta / paikkakunta.";
}
