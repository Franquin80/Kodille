<?php
/**
 * Admin tools for managing Google Places imports.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=palveluntarjoajat',
        __('Google Places -haku', 'kodille'),
        __('Google Places -haku', 'kodille'),
        'manage_options',
        'kodille-google-places',
        'kodille_render_google_places_admin_page'
    );
});

add_action('admin_post_kodille_google_places_fetch', 'kodille_handle_google_places_admin_fetch');

/**
 * Renders the admin page content.
 */
function kodille_render_google_places_admin_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Sinulla ei ole oikeuksia nähdä tätä sivua.', 'kodille'));
    }

    $message = '';
    if (!empty($_GET['kodille_message'])) {
        $message = sanitize_text_field(wp_unslash($_GET['kodille_message']));
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Google Places -haku', 'kodille'); ?></h1>
        <?php if ($message) : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <?php if (!defined('GOOGLE_MAPS_API_KEY') || !GOOGLE_MAPS_API_KEY) : ?>
            <div class="notice notice-warning">
                <p><?php esc_html_e('Lisää GOOGLE_MAPS_API_KEY wp-config.php-tiedostoon ennen hakujen suorittamista.', 'kodille'); ?></p>
            </div>
        <?php endif; ?>
        <p><?php esc_html_e('Työkalu hakee palveluntarjoajia Google Places -rajapinnasta ja tallentaa ne "palveluntarjoajat"-tyyppinä.', 'kodille'); ?></p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('kodille_google_places_fetch'); ?>
            <input type="hidden" name="action" value="kodille_google_places_fetch" />
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><label for="kodille_hakusana"><?php esc_html_e('Hakusana', 'kodille'); ?></label></th>
                    <td><input name="kodille_hakusana" type="text" id="kodille_hakusana" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="kodille_paikkakunta"><?php esc_html_e('Paikkakunta', 'kodille'); ?></label></th>
                    <td><input name="kodille_paikkakunta" type="text" id="kodille_paikkakunta" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="kodille_coords"><?php esc_html_e('Koordinaatit (lat,lng)', 'kodille'); ?></label></th>
                    <td><input name="kodille_coords" type="text" id="kodille_coords" class="regular-text" value="65.0121,25.4651" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="kodille_max"><?php esc_html_e('Tulosten määrä', 'kodille'); ?></label></th>
                    <td><input name="kodille_max" type="number" id="kodille_max" min="1" max="20" value="5" /></td>
                </tr>
                </tbody>
            </table>
            <?php submit_button(__('Hae ja tallenna palveluntarjoajat', 'kodille')); ?>
        </form>
        <p><?php esc_html_e('Voit myös käyttää lyhytkoodia [tuo_palveluntarjoajat] missä tahansa sivulla.', 'kodille'); ?></p>
    </div>
    <?php
}

/**
 * Handles the admin form submission.
 */
function kodille_handle_google_places_admin_fetch()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Sinulla ei ole oikeuksia suorittaa tätä toimintoa.', 'kodille'));
    }

    check_admin_referer('kodille_google_places_fetch');

    $hakusana    = isset($_POST['kodille_hakusana']) ? sanitize_text_field(wp_unslash($_POST['kodille_hakusana'])) : '';
    $paikkakunta = isset($_POST['kodille_paikkakunta']) ? sanitize_text_field(wp_unslash($_POST['kodille_paikkakunta'])) : '';
    $coords      = isset($_POST['kodille_coords']) ? sanitize_text_field(wp_unslash($_POST['kodille_coords'])) : '65.0121,25.4651';
    $max         = isset($_POST['kodille_max']) ? absint($_POST['kodille_max']) : 5;

    $redirect_url = add_query_arg(
        array('post_type' => 'palveluntarjoajat'),
        admin_url('edit.php')
    );

    if (!$hakusana || !$paikkakunta) {
        $redirect_url = add_query_arg('kodille_message', rawurlencode(__('Anna hakusana ja paikkakunta.', 'kodille')), $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    if (!defined('GOOGLE_MAPS_API_KEY') || !GOOGLE_MAPS_API_KEY) {
        $redirect_url = add_query_arg('kodille_message', rawurlencode(__('Google API -avain puuttuu.', 'kodille')), $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    $coords_parts = array_map('trim', explode(',', $coords));
    $latlng       = (count($coords_parts) === 2) ? implode(',', $coords_parts) : '65.0121,25.4651';

    $textsearch_url = add_query_arg(array(
        'query'    => rawurlencode($hakusana . ' ' . $paikkakunta),
        'location' => $latlng,
        'radius'   => 10000,
        'key'      => GOOGLE_MAPS_API_KEY,
    ), 'https://maps.googleapis.com/maps/api/place/textsearch/json');

    $response = wp_remote_get($textsearch_url, array('timeout' => 20));
    if (is_wp_error($response)) {
        $redirect_url = add_query_arg('kodille_message', rawurlencode($response->get_error_message()), $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    $payload = json_decode(wp_remote_retrieve_body($response), true);
    if (!$payload || empty($payload['results'])) {
        $redirect_url = add_query_arg('kodille_message', rawurlencode(__('Tuloksia ei löytynyt.', 'kodille')), $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    $count = 0;
    foreach (array_slice($payload['results'], 0, max(1, $max)) as $place) {
        $details = function_exists('kodille_hae_place_details') ? kodille_hae_place_details($place) : array();
        if (function_exists('kodille_tallenna_palveluntarjoaja_googlesta')) {
            $post_id = kodille_tallenna_palveluntarjoaja_googlesta($place, $details);
            if ($post_id) {
                $count++;
            }
        }
    }

    $message = sprintf(_n('%d palveluntarjoaja tallennettiin.', '%d palveluntarjoajaa tallennettiin.', $count, 'kodille'), $count);
    $redirect_url = add_query_arg('kodille_message', rawurlencode($message), $redirect_url);

    wp_safe_redirect($redirect_url);
    exit;
}
