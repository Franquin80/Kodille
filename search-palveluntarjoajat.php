<form action="/haku/" method="GET">
    <select name="location">
        <option value="">Valitse paikkakunta</option>
        <option value="helsinki">Helsinki</option>
        <option value="oulu">Oulu</option>
    </select>

    <select name="service">
        <option value="">Valitse palvelu</option>
        <option value="siivous">Siivous</option>
        <option value="remontti">Remontti</option>
    </select>

    <button type="submit">Hae</button>
</form>
<?php
$location = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';
$service = isset($_GET['service']) ? sanitize_text_field($_GET['service']) : '';

$args = array(
    'post_type' => 'providers',
    'tax_query' => array('relation' => 'AND')
);

if (!empty($location)) {
    $args['tax_query'][] = array(
        'taxonomy' => 'location-taxonomy',
        'field'    => 'slug',
        'terms'    => $location
    );
}

if (!empty($service)) {
    $args['tax_query'][] = array(
        'taxonomy' => 'service-categories',
        'field'    => 'slug',
        'terms'    => $service
    );
}

$query = new WP_Query($args);

if ($query->have_posts()):
    while ($query->have_posts()): $query->the_post(); ?>
        <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
    <?php endwhile;
else:
    echo '<p>Ei hakutuloksia.</p>';
endif;
wp_reset_postdata();
?>
