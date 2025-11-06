<?php get_header(); ?>

<div class="container">
    <h1>Palvelut paikkakunnittain</h1>
    <ul>
        <?php
        $locations = new WP_Query(array(
            'post_type' => 'locations',
            'posts_per_page' => -1
        ));
        if ($locations->have_posts()):
            while ($locations->have_posts()): $locations->the_post(); ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </li>
            <?php endwhile;
        else:
            echo "<p>Ei paikkakuntia listattuna.</p>";
        endif;
        wp_reset_postdata();
        ?>
    </ul>
</div>

<?php get_footer(); ?>
