function Lang() {

    /**
     * declare and init private vars
     */


    init();

    /**
     * return object interface
     */

    return {
        i18n:    (l) => load(l)
    };

    /**
     * private methods
     */

    function init() {
        /*
            use language from local storage
            fallback to current user
            fallback to 'en'
        */

        var lang = localStorage.getItem("lang");
        console.log(lang);
        if(!lang) {
            lang = 'en';
        }

        $('#lang-selector').on('change', function () {
            var new_lang = $(this).val();
            load(new_lang);
        });

        load(lang);
    }

    function load(lang) {
        localStorage.setItem("lang", lang);
        $('#lang-selector').val(lang);
        $.get('./assets/i18n/'+lang+'.json')
            .done(function( translations ) {
                $('[i18n]').each( function(index, elem) {
                    var $elem = $(elem);
                    var id = $elem.attr('data-id');
                    if(translations.hasOwnProperty(id)) {
                        $elem.text(translations[id]);
                    }
                });
            });
    }
}


$(document).ready( () => {
    new Lang();
});