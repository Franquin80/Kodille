jQuery(document).ready(function($) {
    // Päivitä paikkakunnat, kun maakunta muuttuu
    $('#maakunta').change(function() {
        var maakunta_id = $(this).val();
        if (maakunta_id) {
            $.get('/wp-json/wp/v2/sijainnit?parent=' + maakunta_id, function(data) {
                var options = '<option value="">Kaikki paikkakunnat</option>';
                $.each(data, function(index, value) {
                    options += '<option value="' + value.id + '">' + value.name + '</option>';
                });
                $('#paikkakunta').html(options).prop('disabled', false);
            });
        } else {
            $('#paikkakunta').html('<option value="">Kaikki paikkakunnat</option>').prop('disabled', true);
        }
        $('#palvelukategoria, #service').html('<option value="">Kaikki</option>').prop('disabled', true);
    });

    // Päivitä palvelukategoriat, kun paikkakunta muuttuu
    $('#paikkakunta').change(function() {
        var paikkakunta_id = $(this).val();
        if (paikkakunta_id) {
            $.get('/wp-json/wp/v2/palvelukategoriat', function(data) {
                var options = '<option value="">Kaikki palvelukategoriat</option>';
                $.each(data, function(index, value) {
                    options += '<option value="' + value.id + '">' + value.name + '</option>';
                });
                $('#palvelukategoria').html(options).prop('disabled', false);
            });
        } else {
            $('#palvelukategoria').html('<option value="">Kaikki palvelukategoriat</option>').prop('disabled', true);
        }
        $('#service').html('<option value="">Kaikki palvelut</option>').prop('disabled', true);
    });

    // Päivitä palvelut, kun palvelukategoria muuttuu
    $('#palvelukategoria').change(function() {
        var palvelukategoria_id = $(this).val();
        if (palvelukategoria_id) {
            $.get('/wp-json/wp/v2/palvelut?palvelukategoriat=' + palvelukategoria_id, function(data) {
                var options = '<option value="">Kaikki palvelut</option>';
                $.each(data, function(index, value) {
                    options += '<option value="' + value.id + '">' + value.title.rendered + '</option>';
                });
                $('#service').html(options).prop('disabled', false);
            });
        } else {
            $('#service').html('<option value="">Kaikki palvelut</option>').prop('disabled', true);
        }
    });
});