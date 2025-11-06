<?php
/**
 * Template Name: Etusivu
 */
get_header(); ?>

<main>
    <!-- Hero-osa -->
    <section class="hero-section">
        <h1>Kaikki kodin ja kiinteistön palvelut helposti yhdestä paikasta</h1>
        <p>Löydä luotettava palveluntarjoaja alueellasi yhdellä haulla.</p>
        <form method="get" action="/palveluntarjoajat" class="hero-search">
            <select name="paikkakunta" class="hero-select">
                <option value="">Valitse paikkakunta</option>
                <?php
                $paikkakunnat = get_terms(array('taxonomy' => 'sijainnit', 'hide_empty' => false));
                foreach ($paikkakunnat as $paikkakunta) {
                    echo '<option value="' . esc_attr($paikkakunta->term_id) . '">' . esc_html($paikkakunta->name) . '</option>';
                }
                ?>
            </select>
            <button type="submit" class="cta-button">Hae</button>
        </form>
    </section>

    <!-- Keitä me olemme -->
    <section class="about-section">
        <h2>Keitä me olemme</h2>
        <p>Kodille.com yhdistää sinut monipuolisiin kodin palveluihin. Tarjoamme ratkaisuja siivouksesta ja remontoinnista turvallisuus- ja teknisiin palveluihin, kaikki helposti yhdestä paikasta.</p>
        <a href="/tietoa-meista" class="cta-button">Lue lisää meistä</a>
    </section>

    <!-- Palvelukategoriat -->
    <section class="categories-section">
        <h2>Palvelukategoriat</h2>
        <div class="category-grid">
            <?php
            $kategoriat = get_terms(array('taxonomy' => 'palvelukategoriat', 'hide_empty' => false));
            if (!empty($kategoriat) && !is_wp_error($kategoriat)) {
                foreach ($kategoriat as $kategoria) {
                    $link = get_term_link($kategoria);
                    echo '<div class="category-card">';
                    echo '<h3>' . esc_html($kategoria->name) . '</h3>';
                    echo '<p>' . esc_html($kategoria->description ?: 'Tutustu palveluihimme.') . '</p>';
                    echo '<a href="' . esc_url($link) . '">Tutustu</a>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </section>

    <!-- Ajankohtaista -->
    <section class="news-section">
        <h2>Ajankohtaista</h2>
        <div class="news-container">
            <?php
            $query = new WP_Query(array('post_type' => 'post', 'posts_per_page' => 3));
            if ($query->have_posts()) {
                echo '<ul class="news-list">';
                while ($query->have_posts()) {
                    $query->the_post();
                    echo '<li>';
                    echo '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
                    echo '<p>' . get_the_excerpt() . '</p>';
                    echo '</li>';
                }
                echo '</ul>';
                wp_reset_postdata();
            } else {
                echo '<p>Ei ajankohtaisia artikkeleita.</p>';
            }
            ?>
        </div>
    </section>
</main>

<footer class="site-footer">
    <p>© 2025 Kodille.com. Kaikki oikeudet pidätetään.</p>
    <div>
        <a href="/tietosuojaseloste">Tietosuojaseloste</a>
        <a href="/ota-yhteytta">Ota yhteyttä</a>
    </div>
</footer>

<?php get_footer(); ?>
