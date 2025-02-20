<?php get_header(); ?>

<div class="container">
    <h1><?php the_title(); ?></h1>

    <p><strong>Osoite:</strong> <?php echo esc_html(get_field('katuosoite')); ?>, <?php echo esc_html(get_field('postinumero')); ?></p>
    <p><strong>Puhelin:</strong> <?php echo esc_html(get_field('puhelinnumero')); ?></p>
    <p><strong>Y-tunnus:</strong> <?php echo esc_html(get_field('y_tunnus')); ?></p>
    <p><strong>Arvostelut:</strong> <?php echo esc_html(get_field('arvostelut')); ?> / 5 ⭐</p>
    
    <h2>Tarjotut palvelut</h2>
    <ul>
        <?php
        $services = get_field('tarjotut_palvelut');
        if ($services):
            foreach ($services as $service): ?>
                <li><a href="<?php echo get_permalink($service->ID); ?>"><?php echo $service->post_title; ?></a></li>
            <?php endforeach;
        else:
            echo '<p>Ei palveluita listattuna.</p>';
        endif;
        ?>
    </ul>

    <h2>Toimialueet</h2>
    <ul>
        <?php
        $locations = get_field('toimialue');
        if ($locations):
            foreach ($locations as $loc): ?>
                <li><a href="<?php echo get_permalink($loc->ID); ?>"><?php echo $loc->post_title; ?></a></li>
            <?php endforeach;
        else:
            echo '<p>Ei toimialueita listattuna.</p>';
        endif;
        ?>
    </ul>
</div>

<?php get_footer(); ?>
