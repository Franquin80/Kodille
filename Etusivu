<!-- Hero-osa -->

<section class="hero-section">
<h1>Kaikki kodin ja kiinteistön palvelut helposti yhdestä paikasta</h1>
Löydä luotettava palveluntarjoaja alueellasi yhdellä haulla.

<form class="hero-search" action="/palveluntarjoajat" method="get"><select class="hero-select" name="paikkakunta">
<option value="">Valitse paikkakunta</option><!--?php $paikkakunnat = get_terms(array('taxonomy' =&gt; 'sijainnit', 'hide_empty' =&gt; false));&lt;br ?--> foreach ($paikkakunnat as $paikkakunta) {
</select>
<select class="hero-select" name="paikkakunta">echo '
<option value="' . esc_attr($paikkakunta-&gt;term_id) . '">' . esc_html($paikkakunta-&gt;name) . '</option>';
</select>

<select class="hero-select" name="paikkakunta">}
</select>

<select class="hero-select" name="paikkakunta">?&gt;
</select>

<button class="cta-button" type="submit">Hae</button>

</form></section><!-- Keitä me olemme -->

<section class="about-section">
<h2>Keitä me olemme</h2>
Kodille.com yhdistää sinut monipuolisiin kodin palveluihin. Tarjoamme ratkaisuja siivouksesta ja remontoinnista turvallisuus- ja teknisiin palveluihin, kaikki helposti yhdestä paikasta.

<a class="cta-button" href="/tietoa-meista">Lue lisää meistä</a>

</section><!-- Palvelukategoriat -->

<section class="categories-section">
<h2>Palvelukategoriat</h2>
<div class="category-grid"><!--?php $kategoriat = get_terms(array('taxonomy' =&gt; 'palvelukategoriat', 'hide_empty' =&gt; false));&lt;br ?--> if (!empty($kategoriat) &amp;&amp; !is_wp_error($kategoriat)) {
foreach ($kategoriat as $kategoria) {
$link = get_term_link($kategoria);
echo '
<div class="category-card">';
echo '
<h3>' . esc_html($kategoria-&gt;name) . '</h3>
';
echo '

' . esc_html($kategoria-&gt;description ?: 'Tutustu palveluihimme.') . '

';
echo '<a href="' . esc_url($link) . '">Tutustu</a>';
echo '

</div>
';
}
}
?&gt;

</div>
</section><!-- Ajankohtaista -->

<section class="news-section">
<h2>Ajankohtaista</h2>
<div class="news-container"><!--?php $query = new WP_Query(array('post_type' =&gt; 'post', 'posts_per_page' =&gt; 3));&lt;br ?--> if ($query-&gt;have_posts()) {
echo '
<ul class="news-list">
 	<li style="list-style-type: none;">
<ul class="news-list">';</ul>
</li>
</ul>
<ul class="news-list">
 	<li style="list-style-type: none;">
<ul class="news-list">while ($query-&gt;have_posts()) {</ul>
</li>
</ul>
<ul class="news-list">
 	<li style="list-style-type: none;">
<ul class="news-list">$query-&gt;the_post();</ul>
</li>
</ul>
<ul class="news-list">
 	<li style="list-style-type: none;">
<ul class="news-list">echo '
 	<li>';
echo '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
echo '' . get_the_excerpt() . '';
echo '</li>
</ul>
</li>
</ul>
';
}
echo '

';
wp_reset_postdata();
} else {
echo '

Ei ajankohtaisia artikkeleita.

';
}
?&gt;

</div>
</section>&nbsp;

<footer class="site-footer">© 2025 Kodille.com. Kaikki oikeudet pidätetään.
<div><a href="/tietosuojaseloste">Tietosuojaseloste</a>
<a href="/ota-yhteytta">Ota yhteyttä</a></div>
</footer>
