// custom.js
jQuery(document).ready(function($) {
    // Päivitä palvelukategoriat (jos haluat REST API:ta muualla)
    $('#paikkakunta').change(function() {
        var paikkakunta_id = $(this).val();
        if (paikkakunta_id) {
            $('#palvelukategoria').html('<option value="">Ladataan...</option>');
            $.get(ajax_params.rest_url + 'wp/v2/palvelukategoriat')
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

    // Päivitä palvelut
    $('#palvelukategoria').change(function() {
        var palvelukategoria_id = $(this).val();
        if (palvelukategoria_id) {
            $('#service').html('<option value="">Ladataan...</option>');
            $.get(ajax_params.rest_url + 'wp/v2/palvelut?palvelukategoriat=' + palvelukategoria_id)
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

    // Hae palveluntarjoajat (jos käytät REST API:ta muualla)
    $('#provider-search-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.get(ajax_params.ajax_url + '?action=hae_palveluntarjoajat&' + formData)
            .done(function(data) {
                $('#provider-results').html(data).show();
            })
            .fail(function() {
                $('#provider-results').html('<p>Haku epäonnistui, yritä uudelleen.</p>').show();
            });
    });
});document.addEventListener("DOMContentLoaded", function () {
    fetch(`https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=60.1699,24.9384&radius=5000&keyword=house%20painting&key=${GOOGLE_MAPS_API_KEY}`)
        .then(response => response.json())
        .then(data => {
            const top5 = data.results.slice(0, 5);
            top5.forEach(place => {
                document.getElementById('palveluntarjoajat').innerHTML += `
                    <div>${place.name} - ${place.rating} ★</div>
                `;
            });
        })
        .catch(error => console.error('Virhe API-haussa:', error));
});

