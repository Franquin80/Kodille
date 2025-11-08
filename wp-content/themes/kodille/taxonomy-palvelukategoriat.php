<?php
get_header();

$term = get_queried_object(); // Haetaan valittu kategoria
?>
<section class="container-section">
  <h1><?php echo esc_html($term->name); ?></h1>
  <?php if ($term->description) : ?>
    <p><?php echo esc_html($term->description); ?></p>
  <?php endif; ?>

  <div class="category-grid">
    <?php
    $query = new WP_Query([
      'post_type' => 'palvelut',
      'tax_query' => [
        [
          'taxonomy' => 'palvelukategoriat',
          'field' => 'slug',
          'terms' => $term->slug,
        ],
      ],
    ]);

    if ($query->have_posts()) :
      while ($query->have_posts()) : $query->the_post(); ?>
        <article class="category-card">
          <h3><?php the_title(); ?></h3>
          <p><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
          <a href="<?php the_permalink(); ?>" class="cta-button">Tutustu</a>
        </article>
      <?php endwhile;
      wp_reset_postdata();
    else :
      echo '<p>Ei vielä paikallisia palveluntarjoajia alueella ' . esc_html($term->name) . '.</p>';
    endif;
    ?>
  </div>
</section>

<?php get_footer(); ?>
