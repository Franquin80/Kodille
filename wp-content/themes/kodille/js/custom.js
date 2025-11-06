jQuery(document).ready(function($) {
    var settings = window.kodilleAjax || {};
    var restBase = settings.rest_url || '';

    function buildRestUrl(path) {
        if (!restBase) {
            return '';
        }

        return restBase.replace(/\/?$/, '/') + path.replace(/^\//, '');
    }

    // Päivittää palvelukategoriat
    $('#paikkakunta').change(function() {
        var paikkakunta_id = $(this).val();
        if (paikkakunta_id) {
            $('#palvelukategoria').html('<option value="">Ladataan...</option>');
            var categoriesEndpoint = buildRestUrl('wp/v2/palvelukategoriat');
            if (!categoriesEndpoint) {
                $('#palvelukategoria').html('<option value="">Haku epäonnistui</option>');
                return;
            }
            $.get(categoriesEndpoint)
                .done(function(data) {
                    var options = '<option value="">Kaikki palvelukategoriat</option>';
                    if (data && data.length > 0) {
                        $.each(data, function(index, value) {
                            options += '<option value="' + value.id + '">' + value.name + '</option>';
                        });
                    } else {
                        options = '<option value="">Ei palvelukategorioita</option>';
                    }
                    $('#palvelukategoria').html(options).prop('disabled', false);
                })
                .fail(function() {
                    $('#palvelukategoria').html('<option value="">Haku epäonnistui</option>');
                });
        } else {
            $('#palvelukategoria').html('<option value="">Kaikki palvelukategoriat</option>').prop('disabled', true);
        }
        $('#service').html('<option value="">Kaikki palvelut</option>').prop('disabled', true);
    });

    // Päivittää palvelut
    $('#palvelukategoria').change(function() {
        var palvelukategoria_id = $(this).val();
        if (palvelukategoria_id) {
            $('#service').html('<option value="">Ladataan...</option>');
            var servicesEndpoint = buildRestUrl('wp/v2/palvelut?filter[palvelukategoriat]=' + palvelukategoria_id);
            if (!servicesEndpoint) {
                $('#service').html('<option value="">Haku epäonnistui</option>');
                return;
            }
            $.get(servicesEndpoint)
                .done(function(data) {
                    var options = '<option value="">Kaikki palvelut</option>';
                    if (data && data.length > 0) {
                        $.each(data, function(index, value) {
                            options += '<option value="' + value.id + '">' + value.title.rendered + '</option>';
                        });
                    } else {
                        options = '<option value="">Ei palveluita</option>';
                    }
                    $('#service').html(options).prop('disabled', false);
                })
                .fail(function() {
                    $('#service').html('<option value="">Haku epäonnistui</option>');
                });
        } else {
            $('#service').html('<option value="">Kaikki palvelut</option>').prop('disabled', true);
        }
    });

    // Google Maps API
    if (settings.google_maps_api_key) {
        fetch(`https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=60.1699,24.9384&radius=5000&keyword=house%20painting&key=${settings.google_maps_api_key}`)
            .then(response => response.json())
            .then(data => {
                const top5 = (data.results || []).slice(0, 5);
                top5.forEach(place => {
                    $('#palveluntarjoajat').append(`
                        <div>${place.name} - ${(place.rating || 'N/A')} ★</div>
                    `);
                });
            })
            .catch(error => console.error('Virhe API-haussa:', error));
    }
});
