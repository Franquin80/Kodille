<?php
/* Template Name: Category Page */
get_header();

$category = get_queried_object();
$category_slug = $category->slug; // Hae nykyinen kategoria
?>

<div class="category-page">
    <h1><?php echo esc_html($category->name); ?></h1>
    <p><?php echo esc_html($category->description); ?></p>

    <div class="filter-form">
        <form method="get" action="<?php echo site_url('/palveluntarjoajat/'); ?>">
            <input type="hidden" name="category" value="<?php echo esc_attr($category_slug); ?>">
            <label for="location">Valitse paikkakunta:</label>
            <select name="location">
                <option value="">Kaikki paikkakunnat</option>
                <?php
                $locations = get_terms(['taxonomy' => 'sijainnit']);
                foreach ($locations as $location) {
                    echo '<option value="' . esc_attr($location->slug) . '">' . esc_html($location->name) . '</option>';
                }
                ?>
            </select>
            <button type="submit">Hae</button>
        </form>
    </div>
</div>

<?php get_footer(); ?>
