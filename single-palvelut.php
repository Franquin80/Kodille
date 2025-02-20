<?php get_header(); ?>

<div class="container">
    <h1><?php the_title(); ?></h1>

    <div class="service-meta">
        <?php
        $category = get_field('acf_palvelukategoria'); 
        if ($category): ?>
            <p><strong>Kategoria:</strong> <?php echo esc_html($category->name); ?></p>
        <?php endif; ?>

        <?php $description = get_field('palvelun_nimi'); ?>
        <?php if ($description): ?>
            <p><strong>Kuvaus:</strong> <?php echo esc_html($description); ?></p>
        <?php endif; ?>
    </div>

    <h2>Palveluntarjoajat</h2>
    <ul>
        <?php
        $args = array(
            'post_type' => 'providers',
            'meta_query' => array(
                array(
                    'key' => 'tarjotut_palvelut',
                    'value' => '"' . get_the_ID() . '"',
                    'compare' => 'LIKE'
                )
            )
        );
        $query = new WP_Query($args);
        if ($query->have_posts()):
            while ($query->have_posts()): $query->the_post(); ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> - 
                    <?php echo get_field('puhelinnumero'); ?>
                </li>
            <?php endwhile;
        else: ?>
            <p>Ei palveluntarjoajia tälle palvelulle.</p>
        <?php endif; ?>
        <?php wp_reset_postdata(); ?>
    </ul>
</div>

<?php get_footer(); ?>
