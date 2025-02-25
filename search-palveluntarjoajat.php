<form action="/haku/" method="GET">
    <select name="location">
        <option value="">Valitse paikkakunta</option>
        <?php
        $paikkakunnat = get_terms(array(
            'taxonomy'   => 'sijainnit',
            'hide_empty' => false,
        ));
        foreach ($paikkakunnat as $paikkakunta) {
            echo '<option value="' . esc_attr($paikkakunta->slug) . '">' . esc_html($paikkakunta->name) . '</option>';
        }
        ?>
    </select>

    <select name="service">
        <option value="">Valitse palvelu</option>
        <?php
        $palvelut = get_terms(array(
            'taxonomy'   => 'palvelukategoriat',
            'hide_empty' => false,
        ));
        foreach ($palvelut as $palvelu) {
            echo '<option value="' . esc_attr($palvelu->slug) . '">' . esc_html($palvelu->name) . '</option>';
        }
        ?>
    </select>

    <button type="submit">Hae</button>
</form>

<?php
$location = isset($_GET['maakunta']) ? intval($_GET['maakunta']) : '';
$city = isset($_GET['paikkakunta']) ? intval($_GET['paikkakunta']) : '';
$service_category = isset($_GET['palvelukategoria']) ? intval($_GET['palvelukategoria']) : '';
$service = isset($_GET['service']) ? intval($_GET['service']) : '';

$args = array(
    'post_type'      => 'palveluntarjoajat',
    'post_status'    => 'publish',
    'posts_per_page' => 10,
    'tax_query'      => array('relation' => 'AND'),
);

// Maakunta (sijainti-taksonomia)
if (!empty($location)) {
    $args['tax_query'][] = array(
        'taxonomy' => 'sijainnit',
        'field'    => 'term_id',
        'terms'    => $location,
    );
}

// Paikkakunta (sijainti-taksonomia)
if (!empty($city)) {
    $args['tax_query'][] = array(
        'taxonomy' => 'sijainnit',
        'field'    => 'term_id',
        'terms'    => $city,
    );
}

// Palvelukategoria (palvelukategoriat-taksonomia)
if (!empty($service_category)) {
    $args['tax_query'][] = array(
        'taxonomy' => 'palvelukategoriat',
        'field'    => 'term_id',
        'terms'    => $service_category,
    );
}

// Tarjottu palvelu (ACF Relationship-kenttä)
if (!empty($service)) {
    $args['meta_query'][] = array(
        'key'     => 'tarjotut_palvelut',
        'value'   => '"' . $service . '"',
        'compare' => 'LIKE',
    );
}

// Suorita kysely
$query = new WP_Query($args);
?>

<?php if ($query->have_posts()) : ?>
    <ul>
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <li>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <p><strong>Osoite:</strong> <?php echo esc_html(get_field('katuosoite')); ?>, <?php echo esc_html(get_field('postinumero')); ?>, <?php echo esc_html(get_field('paikkakunta')); ?></p>
                <p><strong>Arvostelut:</strong> <?php echo esc_html(get_field('arvostelut')); ?> / 5 ⭐</p>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else : ?>
    <p>Ei hakutuloksia valituilla hakuehdoilla.</p>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
