<?php
get_header();

function render_rating_stars($rating) {
    $output = '<span class="rating-stars">';
    $fullStars = floor($rating);
    for ($i = 1; $i <= 5; $i++) {
        $output .= $i <= $fullStars ? '★' : '☆';
    }
    $output .= '</span>';
    return $output;
}

$palvelu_post = get_queried_object();
$palvelu_slug = $palvelu_post->post_name;
$palvelu_nimi = get_field('palvelu_nimi', $palvelu_post->ID) ?: $palvelu_post->post_title;

$slug = get_query_var('paikkakunta');

$declensions = json_decode(file_get_contents(get_stylesheet_directory() . '/paikkakunnat.json'), true);
$availability = json_decode(file_get_contents(get_stylesheet_directory() . '/palvelu.paikkakunnat.json'), true);

if (!isset($availability[$palvelu_slug])) {
    $availability[$palvelu_slug] = array_keys($declensions);
}

if (!in_array($slug, $availability[$palvelu_slug] ?? []) || !isset($declensions[$slug])) {
    global $wp_query;
    $wp_query->set_404(); status_header(404); get_template_part('404'); exit;
}

$paikkakunta_meta = $declensions[$slug]['nominatiivi'];
$paikkakunta_inessiivi = $declensions[$slug]['inessiivi'];

// Tunnista oikea sijainti-taxonomia
$location_taxonomy = taxonomy_exists('toiminta-alueet') ? 'toiminta-alueet' : 'sijainnit';

$replace = [
    '%paikkakunta%' => $paikkakunta_meta,
    '%paikkakunta_inessiivi%' => $paikkakunta_inessiivi,
    '%palvelu_nimi%' => $palvelu_nimi
];

$seo_otsikko = str_replace(array_keys($replace), array_values($replace), get_field('seo_otsikko', $palvelu_post->ID) ?: "$palvelu_nimi $paikkakunta_inessiivi - Hinta, palvelut ja parhaat tekijät");
$seo_kuvaus = str_replace(array_keys($replace), array_values($replace), get_field('seo_kuvaus', $palvelu_post->ID) ?: "Tarvitsetko %palvelu_nimi% %paikkakunta_inessiivi%? Lue lisää hinnoista, palveluista ja löydä luotettava ammattilainen.");
$seo_intro = str_replace(array_keys($replace), array_values($replace), get_field('seo_intro', $palvelu_post->ID));
$seo_kysymys1 = str_replace(array_keys($replace), array_values($replace), get_field('seo_kysymys1', $palvelu_post->ID) ?: "Kuinka paljon %palvelu_nimi% maksaa %paikkakunta_inessiivi%?");
$seo_vastaus1 = str_replace(array_keys($replace), array_values($replace), get_field('seo_vastaus1', $palvelu_post->ID));
$seo_kysymys2 = str_replace(array_keys($replace), array_values($replace), get_field('seo_kysymys2', $palvelu_post->ID) ?: "Miksi palkata ammattilainen %palvelu_nimi% suorittamiseen %paikkakunta_inessiivi%?");
$seo_vastaus2 = str_replace(array_keys($replace), array_values($replace), get_field('seo_vastaus2', $palvelu_post->ID));
$seo_kysymys3 = str_replace(array_keys($replace), array_values($replace), get_field('seo_kysymys3', $palvelu_post->ID) ?: "Kuinka löydät parhaan %palvelu_nimi% tekijän %paikkakunta_inessiivi%?");
$seo_vastaus3 = str_replace(array_keys($replace), array_values($replace), get_field('seo_vastaus3', $palvelu_post->ID));
$seo_paatelma = str_replace(array_keys($replace), array_values($replace), get_field('seo_paatelma', $palvelu_post->ID));
$hinta = get_field('hinta', $palvelu_post->ID) ?: 'Hinta vaihtelee – pyydä tarjous alta!';

// 🔍 HAE PAIKKAKUNTA-TERMIN ID
$paikkakunta_term = get_term_by('name', $paikkakunta_meta, $location_taxonomy);
$paikkakunta_term_id = $paikkakunta_term ? $paikkakunta_term->term_id : null;

// 🔍 HAE PALVELU POST ID (jos on relationship-kenttä)
$palvelu_post_id = $palvelu_post->ID;

// 👉 KORJATTU: Haetaan sponsoroidut tarjoajat
$sponsor_args = [
    'post_type' => 'palveluntarjoajat',
    'posts_per_page' => 3,
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'sponsoroitu',
            'value' => '1',
            'compare' => '='
        ]
    ]
];

// Jos paikkakunta-termi löytyy, lisää tax_query
if ($paikkakunta_term_id) {
    $sponsor_args['tax_query'] = [[
        'taxonomy' => $location_taxonomy,
        'field' => 'term_id',
        'terms' => $paikkakunta_term_id
    ]];
}

$sponsored = get_posts($sponsor_args);

// 👉 KORJATTU: Haetaan paikalliset tarjoajat
$local_args = [
    'post_type' => 'palveluntarjoajat',
    'posts_per_page' => 6,
    'orderby' => 'meta_value_num',
    'meta_key' => 'arvostelut',
    'order' => 'DESC',
];

// Hae ne jotka tarjoavat TÄTÄ palvelua TÄLLÄ paikkakunnalla
$tax_query = ['relation' => 'AND'];

if ($paikkakunta_term_id) {
    $tax_query[] = [
        'taxonomy' => $location_taxonomy,
        'field' => 'term_id',
        'terms' => $paikkakunta_term_id
    ];
}

// Jos palvelu on tallennettu taxonomiana
$palvelu_term = get_term_by('slug', $palvelu_slug, 'palvelukategoriat');
if ($palvelu_term) {
    $tax_query[] = [
        'taxonomy' => 'palvelukategoriat',
        'field' => 'term_id',
        'terms' => $palvelu_term->term_id
    ];
}

if (count($tax_query) > 1) {
    $local_args['tax_query'] = $tax_query;
}

$local_providers = get_posts($local_args);

// Poista sponsoroidut paikallisista
$sponsored_ids = wp_list_pluck($sponsored, 'ID');
$local_providers = array_filter($local_providers, function($p) use ($sponsored_ids) {
    return !in_array($p->ID, $sponsored_ids);
});
?>

<meta name="description" content="<?php echo esc_attr($seo_kuvaus); ?>">

<main class="opas-container">
    <section class="hero">
        <h1><?php echo esc_html($seo_otsikko); ?></h1>
        <?php if ($seo_intro): ?><p class="hero-text"><?php echo wp_kses_post($seo_intro); ?></p><?php endif; ?>
    </section>

    <?php if (!empty($sponsored)): ?>
    <section class="sponsored-section" style="background:#f4f4f4; padding:20px; border-radius:8px; margin:30px 0;">
        <h2>⭐ Sponsoroidut palveluntarjoajat <?php echo esc_html($paikkakunta_inessiivi); ?></h2>
        <ul class="tarjoajat-lista">
            <?php foreach ($sponsored as $s): 
                $rating = get_field('arvostelut', $s->ID);
                $phone = get_field('puhelinnumero', $s->ID);
                $website = get_field('kotisivu', $s->ID);
            ?>
                <li>
                    <strong><?php echo esc_html(get_the_title($s)); ?></strong><br>
                    <?php if ($rating): echo render_rating_stars($rating) . '<br>'; endif; ?>
                    <?php if ($phone): echo '<strong>Puh:</strong> <a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a><br>'; endif; ?>
                    <?php if ($website): echo '<strong>Nettisivu:</strong> <a href="' . esc_url($website) . '" target="_blank" rel="noopener">Vieraile sivustolla</a><br>'; endif; ?>
                    <a href="<?php echo esc_url(get_permalink($s)); ?>" class="btn">Lue lisää</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>

    <?php if ($seo_vastaus1): ?><section class="service-description"><h2><?php echo esc_html($seo_kysymys1); ?></h2><?php echo wp_kses_post($seo_vastaus1); ?></section><?php endif; ?>
    
    <section class="price">
        <h2>Paljonko <?php echo esc_html(strtolower($palvelu_nimi)); ?> maksaa <?php echo esc_html($paikkakunta_inessiivi); ?>?</h2>
        <p><?php echo esc_html($hinta); ?></p>
    </section>
    
    <?php if ($seo_vastaus2): ?><section class="why-professional"><h2><?php echo esc_html($seo_kysymys2); ?></h2><?php echo wp_kses_post($seo_vastaus2); ?></section><?php endif; ?>
    <?php if ($seo_vastaus3): ?><section class="how-to-choose"><h2><?php echo esc_html($seo_kysymys3); ?></h2><?php echo wp_kses_post($seo_vastaus3); ?></section><?php endif; ?>

    <?php if (!empty($local_providers)): 
        // Rajoita max 5 tarjoajaan
        $local_providers = array_slice($local_providers, 0, 5);
    ?>
    <section class="providers-section">
        <h2>Löydä parhaat <?php echo esc_html($palvelu_nimi); ?> toimijat <?php echo esc_html($paikkakunta_inessiivi); ?></h2>
        <p class="providers-intro">Olemme koonneet yhteen paikkakuntasi arvostetuimmat ammattilaiset. Selaa luotettavia arvosteluihin perustuvia listoja ja tee paras valinta.</p>
        
        <ul class="tarjoajat-lista">
            <?php foreach ($local_providers as $p): 
                $rating = get_field('arvostelut', $p->ID);
                $phone = get_field('puhelinnumero', $p->ID);
                $address = get_field('katuosoite', $p->ID);
            ?>
                <li>
                    <strong><?php echo esc_html(get_the_title($p)); ?></strong><br>
                    <?php if ($rating): echo render_rating_stars($rating) . ' (' . number_format($rating, 1, ',', '') . '/5)<br>'; endif; ?>
                    <?php if ($address): echo '<small>' . esc_html($address) . '</small><br>'; endif; ?>
                    <?php if ($phone): echo '<strong>Puh:</strong> <a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a><br>'; endif; ?>
                    <a href="<?php echo esc_url(get_permalink($p)); ?>" class="btn">Lue lisää</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>

    <?php if ($seo_paatelma): ?><section class="paatelma"><h2>Yhteenveto</h2><?php echo wp_kses_post($seo_paatelma); ?></section><?php endif; ?>
</main>

<style>
.opas-container { max-width: 800px; margin: 20px auto; padding: 0 15px; }
.hero-text { font-size: 1.2em; margin: 15px 0; line-height: 1.6; }
h2 { margin-top: 40px; font-size: 24px; color: #333; }

/* Intro-teksti palveluntarjoajien yläpuolella */
.providers-intro {
    font-size: 1.05em;
    color: #555;
    margin: 15px 0 25px;
    line-height: 1.6;
}

/* Tarjoajat Grid-layoutissa */
.tarjoajat-lista { 
    list-style: none; 
    padding: 0;
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

/* Tietokoneella 2 vierekkäin */
@media (min-width: 768px) {
    .tarjoajat-lista {
        grid-template-columns: repeat(2, 1fr);
    }
}

.tarjoajat-lista li { 
    border: 1px solid #e0e0e0; 
    padding: 20px; 
    border-radius: 8px;
    background: #fff;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.3s ease;
}

.tarjoajat-lista li:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.rating-stars { 
    color: #f5a623; 
    font-size: 18px; 
    letter-spacing: 2px;
    display: inline-block;
    margin-right: 5px;
}

.btn { 
    display: inline-block; 
    background: #f77630; 
    color: white; 
    padding: 8px 16px; 
    border-radius: 6px; 
    margin-top: auto;
    text-decoration: none;
    font-weight: 600;
    text-align: center;
    align-self: flex-start;
}
.btn:hover { background: #e26520; }

.sponsored-section { 
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Sponsoroidut myös gridiin */
.sponsored-section .tarjoajat-lista {
    background: transparent;
}
</style>

<?php get_footer(); ?>
