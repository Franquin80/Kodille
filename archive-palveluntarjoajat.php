<div class="provider-archive" style="padding: 40px; font-family: Arial, sans-serif;">
    <h1 style="text-align: center; font-size: 2.5rem; margin-bottom: 30px;">Etsi palveluntarjoajia</h1>

    <!-- Hakulomake -->
    <form method="get" action="">
        <!-- Maakunta -->
        <label for="maakunta">Valitse maakunta:</label>
        <select name="maakunta" id="maakunta" style="width: 100%; padding: 10px; margin-bottom: 20px;">
            <option value="">Kaikki maakunnat</option>
            <?php
            $maakunnat = get_terms(array(
                'taxonomy' => 'sijainnit',
                'parent' => 0,
                'hide_empty' => false,
            ));
            foreach ($maakunnat as $maakunta) {
                $selected = (isset($_GET['maakunta']) && $_GET['maakunta'] == $maakunta->term_id) ? 'selected' : '';
                echo '<option value="' . esc_attr($maakunta->term_id) . '" ' . $selected . '>' . esc_html($maakunta->name) . '</option>';
            }
            ?>
        </select>

        <!-- Paikkakunta -->
        <label for="paikkakunta">Valitse paikkakunta:</label>
        <select name="paikkakunta" id="paikkakunta" style="width: 100%; padding: 10px; margin-bottom: 20px;" <?php echo empty($_GET['maakunta']) ? 'disabled' : ''; ?>>
            <option value="">Kaikki paikkakunnat</option>
            <?php
            if (!empty($_GET['maakunta'])) {
                $paikkakunnat = get_terms(array(
                    'taxonomy' => 'sijainnit',
                    'parent' => intval($_GET['maakunta']),
                    'hide_empty' => false,
                ));
                foreach ($paikkakunnat as $paikkakunta) {
                    $selected = (isset($_GET['paikkakunta']) && $_GET['paikkakunta'] == $paikkakunta->term_id) ? 'selected' : '';
                    echo '<option value="' . esc_attr($paikkakunta->term_id) . '" ' . $selected . '>' . esc_html($paikkakunta->name) . '</option>';
                }
            }
            ?>
        </select>

        <!-- Palvelukategoria -->
        <label for="palvelukategoria">Valitse palvelukategoria:</label>
        <select name="palvelukategoria" id="palvelukategoria" style="width: 100%; padding: 10px; margin-bottom: 20px;" <?php echo empty($_GET['paikkakunta']) ? 'disabled' : ''; ?>>
            <option value="">Kaikki palvelukategoriat</option>
            <?php
            if (!empty($_GET['paikkakunta'])) {
                $palvelukategoriat = get_terms(array(
                    'taxonomy' => 'palvelukategoriat',
                    'hide_empty' => false,
                ));
                foreach ($palvelukategoriat as $palvelukategoria) {
                    $selected = (isset($_GET['palvelukategoria']) && $_GET['palvelukategoria'] == $palvelukategoria->term_id) ? 'selected' : '';
                    echo '<option value="' . esc_attr($palvelukategoria->term_id) . '" ' . $selected . '>' . esc_html($palvelukategoria->name) . '</option>';
                }
            }
            ?>
        </select>

        <!-- Palvelu -->
        <label for="service">Valitse palvelu:</label>
        <select name="service" id="service" style="width: 100%; padding: 10px; margin-bottom: 20px;" <?php echo empty($_GET['palvelukategoria']) ? 'disabled' : ''; ?>>
            <option value="">Kaikki palvelut</option>
            <?php
            if (!empty($_GET['palvelukategoria'])) {
                $services = get_posts(array(
                    'post_type' => 'palvelut',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'palvelukategoriat',
                            'field'    => 'term_id',
                            'terms'    => intval($_GET['palvelukategoria']),
                        ),
                    ),
                ));
                foreach ($services as $service) {
                    $selected = (isset($_GET['service']) && $_GET['service'] == $service->ID) ? 'selected' : '';
                    echo '<option value="' . esc_attr($service->ID) . '" ' . $selected . '>' . esc_html($service->post_title) . '</option>';
                }
            }
            ?>
        </select>

        <button type="submit" style="padding: 10px 20px; background-color: #0073aa; color: white; border: none; border-radius: 5px;">Hae</button>
    </form>

    <!-- Tulokset -->
    <div class="provider-list" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <?php
        // Alustetaan kyselyn argumentit
        $query_args = array(
            'post_type' => 'palveluntarjoajat',
            'posts_per_page' => 5, // Näytä vain 5 parasta
            'meta_key' => 'arvostelut', // Oletetaan, että arvostelut on tallennettu ACF-kenttään 'arvostelut'
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
            'tax_query' => array('relation' => 'AND'),
            'meta_query' => array(),
        );

        // Suodata maakunnan perusteella
        if (!empty($_GET['maakunta'])) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'sijainnit',
                'field'    => 'term_id',
                'terms'    => intval($_GET['maakunta']),
            );
        }

        // Suodata paikkakunnan perusteella
        if (!empty($_GET['paikkakunta'])) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'sijainnit',
                'field'    => 'term_id',
                'terms'    => intval($_GET['paikkakunta']),
            );
        }

        // Suodata palvelukategorian perusteella
        if (!empty($_GET['palvelukategoria'])) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'palvelukategoriat',
                'field'    => 'term_id',
                'terms'    => intval($_GET['palvelukategoria']),
            );
        }

        // Suodata palvelun perusteella
        if (!empty($_GET['service'])) {
            $query_args['meta_query'][] = array(
                'key'     => 'tarjotut_palvelut',
                'value'   => intval($_GET['service']),
                'compare' => 'LIKE',
            );
        }

        // Suorita kysely
        $query = new WP_Query($query_args);

        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                ?>
                <div style="background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 5px;">
                    <h2><?php the_title(); ?></h2>
                    <p><strong>Osoite:</strong> <?php echo esc_html(get_field('katuosoite')) . ', ' . esc_html(get_field('paikkakunta')); ?></p>
                    <p><strong>Arvostelut:</strong> <?php echo esc_html(get_field('arvostelut')); ?></p>
                    <p><strong>Tarjotut palvelut:</strong> <?php
                        $related_services = get_field('tarjotut_palvelut');
                        if ($related_services) {
                            foreach ($related_services as $service) {
                                echo esc_html($service->post_title) . ', ';
                            }
                        }
                    ?></p>
                </div>
                <?php
            endwhile;
        else :
            echo '<p>Ei tuloksia. Kokeile toisia hakuehtoja.</p>';
        endif;

        wp_reset_postdata();
        ?>
    </div>
</div>