<?php
get_header(); // Lisää ylätunniste
?>

<div class="service-archive" style="padding: 40px; font-family: Arial, sans-serif;">
    <h1 style="text-align: center; font-size: 2.5rem; margin-bottom: 30px;">Palvelut</h1>

    <!-- Suodatuslomake -->
    <form method="get" action="" style="max-width: 800px; margin: 0 auto 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div>
            <label for="paikkakunta" style="font-weight: bold; display: block; margin-bottom: 10px;">Paikkakunta:</label>
            <input type="text" name="paikkakunta" id="paikkakunta" value="<?php echo isset($_GET['paikkakunta']) ? esc_attr($_GET['paikkakunta']) : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        </div>

        <div>
            <label for="kategoria" style="font-weight: bold; display: block; margin-bottom: 10px;">Kategoria:</label>
            <input type="text" name="kategoria" id="kategoria" value="<?php echo isset($_GET['kategoria']) ? esc_attr($_GET['kategoria']) : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        </div>

        <div style="grid-column: span 2; text-align: center;">
            <button type="submit" style="padding: 10px 20px; background-color: #0073aa; color: #fff; border: none; border-radius: 5px; cursor: pointer;">Hae</button>
        </div>
    </form>

    <div class="service-list" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <?php
        // Hae suodatusparametrit
        $paikkakunta = isset($_GET['paikkakunta']) ? sanitize_text_field($_GET['paikkakunta']) : '';
        $kategoria = isset($_GET['kategoria']) ? sanitize_text_field($_GET['kategoria']) : '';

        // WP_Query -asetukset
        $args = [
            'post_type' => 'palvelut',
            'meta_query' => []
        ];

        // Lisää suodatus paikkakunnalle
        if (!empty($paikkakunta)) {
            $args['meta_query'][] = [
                'key' => 'paikkakunta',
                'value' => $paikkakunta,
                'compare' => 'LIKE'
            ];
        }

        // Lisää suodatus kategorialle
        if (!empty($kategoria)) {
            $args['meta_query'][] = [
                'key' => 'palvelukategoria',
                'value' => $kategoria,
                'compare' => 'LIKE'
            ];
        }

        // Suorita kysely
        $query = new WP_Query($args);

        // Näytä tulokset
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                ?>
                <div class="service-card" style="background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 5px; text-align: center;">
                    <h2 style="font-size: 1.5rem; margin-bottom: 15px;">
                        <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: #0073aa;">
                            <?php the_title(); ?>
                        </a>
                    </h2>
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 20px;">
                        <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                    </p>
                    <a href="<?php the_permalink(); ?>" style="background: #0073aa; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Lue lisää</a>
                </div>
                <?php
            endwhile;

            echo '<div style="margin-top: 40px; text-align: center;">';
            the_posts_pagination([
                'mid_size' => 2,
                'prev_text' => __('&laquo; Edellinen'),
                'next_text' => __('Seuraava &raquo;'),
            ]);
            echo '</div>';
        else :
            echo '<p style="text-align: center; font-size: 1.2rem;">Ei palveluita löytynyt.</p>';
        endif;

        wp_reset_postdata();
        ?>
    </div>
</div>

<?php
get_footer(); // Lisää alatunniste
?>
