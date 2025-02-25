<?php get_header(); ?>

<div class="provider-archive" style="padding: 40px; font-family: Arial, sans-serif;">
    <h1 style="text-align: center; font-size: 2.5rem; margin-bottom: 30px;">Etsi palveluntarjoajia</h1>

    <!-- Hakulomake -->
    <form method="get" action="" id="provider-search-form">
        <label for="maakunta">Valitse maakunta:</label>
        <select name="maakunta" id="maakunta" style="width: 100%; padding: 10px; margin-bottom: 20px;">
            <option value="">Valitse maakunta</option>
            <?php
            $maakunnat = get_terms(array(
                'taxonomy'   => 'sijainnit',
                'parent'     => 0,
                'hide_empty' => false,
            ));
            foreach ($maakunnat as $maakunta) {
                echo '<option value="' . esc_attr($maakunta->term_id) . '">' . esc_html($maakunta->name) . '</option>';
            }
            ?>
        </select>

        <label for="paikkakunta">Valitse paikkakunta:</label>
        <select name="paikkakunta" id="paikkakunta" style="width: 100%; padding: 10px; margin-bottom: 20px;" disabled>
            <option value="">Valitse ensin maakunta</option>
        </select>

        <label for="palvelukategoria">Valitse palvelukategoria:</label>
        <select name="palvelukategoria" id="palvelukategoria" style="width: 100%; padding: 10px; margin-bottom: 20px;">
            <option value="">Valitse kategoria</option>
            <?php
            $palvelukategoriat = get_terms(array(
                'taxonomy'   => 'palvelukategoriat',
                'hide_empty' => false,
            ));
            foreach ($palvelukategoriat as $palvelukategoria) {
                echo '<option value="' . esc_attr($palvelukategoria->term_id) . '">' . esc_html($palvelukategoria->name) . '</option>';
            }
            ?>
        </select>

        <label for="service">Valitse palvelu:</label>
        <select name="service" id="service" style="width: 100%; padding: 10px; margin-bottom: 20px;" disabled>
            <option value="">Valitse ensin kategoria</option>
        </select>

        <button type="submit" style="padding: 10px 20px; background-color: #0073aa; color: white; border: none; border-radius: 5px;">Hae</button>
    </form>

    <!-- Tulosten näyttö -->
    <div id="provider-results" style="margin-top: 40px; display: none;">
        <?php
        if (!empty($_GET['maakunta']) && !empty($_GET['paikkakunta']) && !empty($_GET['palvelukategoria']) && !empty($_GET['service'])) {
            $query_args = array(
                'post_type'      => 'palveluntarjoajat',
                'posts_per_page' => -1,
                'tax_query'      => array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'sijainnit',
                        'field'    => 'term_id',
                        'terms'    => intval($_GET['paikkakunta']),
                    ),
                    array(
                        'taxonomy' => 'palvelukategoriat',
                        'field'    => 'term_id',
                        'terms'    => intval($_GET['palvelukategoria']),
                    ),
                ),
                'meta_query' => array(
                    array(
                        'key'     => 'tarjotut_palvelut',
                        'value'   => '"' . intval($_GET['service']) . '"',
                        'compare' => 'LIKE'
                    )
                )
            );

            $provider_query = new WP_Query($query_args);

            if ($provider_query->have_posts()) {
                echo '<ul style="list-style: none; padding: 0;">';
                while ($provider_query->have_posts()) {
                    $provider_query->the_post();
                    echo '<li style="padding: 10px; border-bottom: 1px solid #ddd;"><a href="' . get_permalink() . '" style="text-decoration: none; color: #0073aa; font-size: 1.2rem;">' . get_the_title() . '</a></li>';
                }
                echo '</ul>';
                wp_reset_postdata();
                echo '<script>document.getElementById("provider-results").style.display = "block";</script>';
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
    const form = document.getElementById('provider-search-form');
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

<?php get_footer(); ?>
