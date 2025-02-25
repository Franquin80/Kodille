<?php get_header(); ?>

<div class="provider-single" style="padding: 40px; font-family: Arial, sans-serif;">
    <h1 style="text-align: center; font-size: 2.5rem;">Palveluntarjoaja: <?php the_title(); ?></h1>

    <div style="max-width: 800px; margin: 0 auto; padding: 20px; background: #fff; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
        <p><strong>Palveluntarjoajan nimi:</strong> <?php the_title(); ?></p>

        <p><strong>Osoite:</strong> 
            <?php echo esc_html(get_field('katuosoite')); ?>, 
            <?php echo esc_html(get_field('postinumero')); ?>, 
            <?php echo esc_html(get_field('paikkakunta')); ?>
        </p>

        <p><strong>Puhelin:</strong> 
            <?php 
            $puhelin = get_field('puhelin');
            if ($puhelin) {
                echo '<a href="tel:' . esc_attr($puhelin) . '" style="color: #0073aa;">' . esc_html($puhelin) . '</a>';
            } else {
                echo 'Ei puhelinnumeroa';
            }
            ?>
        </p>

        <p><strong>Arvostelut:</strong> 
            <?php echo esc_html(get_field('arvostelut')) ?: 'Ei arvosteluja'; ?> / 5 ⭐
        </p>

        <p><strong>Kotisivu:</strong> 
            <?php 
            $kotisivu = get_field('kotisivu');
            if ($kotisivu) {
                echo '<a href="' . esc_url($kotisivu) . '" target="_blank" rel="noopener noreferrer" style="color: #0073aa;">' . esc_html($kotisivu) . '</a>';
            } else {
                echo 'Ei kotisivua';
            }
            ?>
        </p>

        <p><strong>Tarjotut palvelut:</strong>
            <?php
            $tarjotut_palvelut = get_field('tarjotut_palvelut');
            if ($tarjotut_palvelut) {
                echo '<ul>';
                foreach ($tarjotut_palvelut as $palvelu) {
                    echo '<li>' . esc_html(get_the_title($palvelu)) . '</li>';
                }
                echo '</ul>';
            } else {
                echo 'Ei tarjottuja palveluita';
            }
            ?>
        </p>
    </div>
</div>

<?php get_footer(); ?>
