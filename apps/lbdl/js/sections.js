function selectSection(section) {
    console.log('selecting', section);
    $('#left_pane').find('div.selected').removeClass('selected');
    $('#section-selector_'+section).addClass('selected');
    $('#section_'+section).addClass('selected');

    if(!$('#section_'+section).find('.maps').children().length) {
        /**
         * load selected section
         */
        var url;
        switch(section) {
            case 'news':
                url = '/?get=model_collect&'+ (new URLSearchParams({
                    entity: 'lbdl\\Map',
                    domain: '[status,=,published]',
                    fields: '{name,board,created,author,count_likes,count_games,difficulty}',
                    limit:15, order: 'created', sort: 'desc'
                }))
                .toString();
                break;
            case 'easy':
                url = '/?get=model_collect&'+ (new URLSearchParams({
                    entity: 'lbdl\\Map',
                    fields: '{name,board,created,author,count_likes,count_games,difficulty}',
                    limit:15, order: 'difficulty', sort: 'asc'
                }))
                .toString();
                break;
            case 'difficult':
                url = '/?get=model_collect&'+ (new URLSearchParams({
                    entity: 'lbdl\\Map',
                    fields: '{name,board,created,author,count_likes,count_games,difficulty}',
                    limit:15, order: 'difficulty', sort: 'desc'
                }))
                .toString();
                break;
            case 'most-played':
                url = '/?get=model_collect&'+ (new URLSearchParams({
                    entity: 'lbdl\\Map',
                    fields: '{name,board,created,author,count_likes,count_games,difficulty}',
                    limit:15, order: 'count_games', sort: 'desc'
                }))
                .toString();
                break;
            case 'most-liked':
                url = '/?get=model_collect&'+ (new URLSearchParams({
                    entity: 'lbdl\\Map',
                    fields: '{name,board,created,author,count_likes,count_games,difficulty}',
                    limit:15, order: 'count_likes', sort: 'desc'
                }))
                .toString();
                break;
        }

        fetch(url)
        .then(async (response) => {
            var $template = $('#template-map');
            const maps = await response.json();
            maps.forEach( (map) => {
                const formatter = Intl.NumberFormat('en', { notation: 'compact' });
                var $map = (new Map(map)).miniMap();
                var $item = $template.clone().removeAttr('id');;
                // decorate template
                $item.find('.map-thumb').append($map);
                $item.find('.map-name').text(map.name);
                $item.find('.map-author').text(map.author);
                $item.find('.map-difficulty').append( $("<img>").prop('src', "assets/img/difficult_"+map.difficulty+".png") );
                $item.find('.map-date').text(new Date(map.created).toLocaleDateString('fr-BE'));
                $item.find('.map-likes').text(formatter.format(map.count_likes));
                $item.find('.map-games').text(formatter.format(map.count_games));
                $item.on('click', () => {
                    window.location.href="./play.html?level="+map.id;
                });
                // insert item
                $('#section_'+section).find('.maps').append($item);
            });
        })
        .catch( (response) => {
            // There was an error
            console.warn('Something went wrong while requesting section.', response);
        });
    }
}
