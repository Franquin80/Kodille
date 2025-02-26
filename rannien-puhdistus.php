<?php
/*
Template Name: Rännien puhdistus
*/

// Hae paikkakunta URL:sta tai ACF:stä
$paikkakunta = get_query_var('paikkakunta') ?: get_field('paikkakunta') ?: 'Helsinki';

// Funktio tarkistaa, onko syöte maakunta
function is_maakunta($paikkakunta) {
    $json_file = get_template_directory() . '/paikkakunnat.json';
    if (!file_exists($json_file)) return false;

    $data = json_decode(file_get_contents($json_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) return false;

    foreach ($data as $item) {
        if (strtolower($item['nominatiivi']) === strtolower($paikkakunta) && !empty($item['kunnat'])) {
            return true;
        }
    }
    return false;
}

// Funktio tarkistaa, onko syöte kunta
function is_kunta($paikkakunta) {
    $json_file = get_template_directory() . '/paikkakunnat.json';
    if (!file_exists($json_file)) return false;

    $data = json_decode(file_get_contents($json_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) return false;

    foreach ($data as $item) {
        if (strtolower($item['nominatiivi']) === strtolower($paikkakunta) && empty($item['kunnat'])) {
            return true;
        }
        foreach ($item['kunnat'] as $kunta) {
            if (strtolower($kunta['nominatiivi']) === strtolower($paikkakunta)) {
                return true;
            }
        }
    }
    return false;
}

// Taivuta paikkakunta
function taivuta_paikkakunta($paikkakunta, $muoto) {
    $json_file = get_template_directory() . '/paikkakunnat.json';
    if (!file_exists($json_file)) return $paikkakunta;

    $data = json_decode(file_get_contents($json_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) return $paikkakunta;

    foreach ($data as $item) {
        if (strtolower($item['nominatiivi']) === strtolower($paikkakunta)) {
            return $item[$muoto] ?? $paikkakunta;
        }
        foreach ($item['kunnat'] as $kunta) {
            if (strtolower($kunta['nominatiivi']) === strtolower($paikkakunta)) {
                return $kunta[$muoto] ?? $paikkakunta;
            }
        }
    }
    return $paikkakunta;
}

// Hae maakunta
function hae_maakunta($paikkakunta, $muoto) {
    $json_file = get_template_directory() . '/paikkakunnat.json';
    if (!file_exists($json_file)) return $paikkakunta;

    $data = json_decode(file_get_contents($json_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) return $paikkakunta;

    foreach ($data as $item) {
        foreach ($item['kunnat'] as $kunta) {
            if (strtolower($kunta['nominatiivi']) === strtolower($paikkakunta)) {
                return $item[$muoto] ?? $paikkakunta;
            }
        }
    }
    return $paikkakunta;
}

// Hae koordinaatit
function hae_koordinaatit($paikkakunta) {
    $json_file = get_template_directory() . '/paikkakunnat.json';
    if (!file_exists($json_file)) return '60.1699,24.9384'; // Oletus Helsinki

    $data = json_decode(file_get_contents($json_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) return '60.1699,24.9384';

    foreach ($data as $item) {
        if (strtolower($item['nominatiivi']) === strtolower($paikkakunta)) {
            return $item['coordinates'] ?? '60.1699,24.9384';
        }
    }
    return '60.1699,24.9384';
}

get_header();
$taivutettu = taivuta_paikkakunta($paikkakunta, 'inessiivi');
?>

<main>
    <header class="hero-section" role="banner" style="background-image: url('<?php echo esc_url(get_field('hero_background_image')); ?>');">
        <div class="container">
            <h1>Rännien puhdistus <?php echo esc_html($taivutettu); ?> – Kaikki mitä tarvitset</h1>
            <p>
                <?php if (is_maakunta($paikkakunta)) : ?>
                    Tarvitsetko apua rännien puhdistukseen <?php echo esc_html($taivutettu); ?>? Löydät täältä ohjeet ja 5 parasta ammattilaista alueella.
                <?php elseif (is_kunta($paikkakunta)) : ?>
                    Rännien puhdistus <?php echo esc_html($taivutettu); ?> pitää kotisi kunnossa. Tutustu menetelmiin ja löydä 5 parasta palveluntarjoajaa <?php echo esc_html($taivutettu); ?> sekä <?php echo esc_html(hae_maakunta($paikkakunta, 'genetiivi')); ?> alueella.
                <?php else : ?>
                    Paikkakuntaa ei tunnistettu – katso yleiset ohjeet ja etsi palveluntarjoajia alta!
                <?php endif; ?>
            </p>
        </div>
    </header>

    <section>
        <h2>Miksi rännit puhdistetaan?</h2>
        <p>Rännit ohjaavat veden pois rakennuksesta. Tukkeutuneet rännit voivat aiheuttaa:</p>
        <ul>
            <li>Kosteusvaurioita seiniin ja perustuksiin.</li>
            <li>Jään muodostumista ja rännien hajoamista.</li>
        </ul>
    </section>

    <section>
        <h2>Miten rännit puhdistetaan <?php echo esc_html($taivutettu); ?>?</h2>
        <h3>Käsipelillä</h3><p>Poista roskat käsin – tarkkaa mutta hidasta.</p>
        <h3>Painepesurilla</h3><p>Tehokas pinttyneeseen likaan.</p>
        <h3>Lehtipuhaltimella</h3><p>Nopea kevyeen roskaan.</p>
        <h3>Imurilla</h3><p>Hyvä korkeisiin ränneihin.</p>
    </section>

    <section>
        <h2>5 parasta rännien puhdistajaa <?php echo esc_html($taivutettu); ?></h2>
        <div id="palveluntarjoajat"></div>
    </section>

    <section>
        <h2>Löydä apua <?php echo esc_html($taivutettu); ?></h2>
        <p>Ota yhteyttä yllä listattuihin ammattilaisiin ja pyydä tarjous suoraan heiltä!</p>
    </section>
</main>

<script>
    const GOOGLE_MAPS_API_KEY = '<?php echo esc_js("sinun-api-avaimesi"); ?>';
    const location = '<?php echo esc_js(hae_koordinaatit($paikkakunta)); ?>';
    fetch(`https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=${location}&radius=5000&keyword=rannien%20puhdistus&key=${GOOGLE_MAPS_API_KEY}`)
        .then(response => response.json())
        .then(data => {
            const top5 = data.results.slice(0, 5);
            document.getElementById('palveluntarjoajat').innerHTML = top5.map(place => `
                <div>
                    <strong>${place.name}</strong> - ${place.rating || 'Ei arvostelua'} ★ 
                    <a href="https://www.google.com/maps/place/?q=place_id:${place.place_id}" target="_blank">Ota yhteyttä</a>
                </div>
            `).join('');
        });
</script>

<?php get_footer(); ?>
