function Score() {

    /**
     * declare and init private vars
     */


    /**
     * return object interface
     */

    return {
        loadScores:    (s) => loadScores(s)
    };

    /**
     * private methods
     */

    function loadScores(scores) {
        $('#high-scores').find('.line').remove();
        scores.forEach( (score, index) => {
            $('#high-scores').append(
                $('<div>').addClass('line')
                .append($('<div>').addClass('name').text((index+1)+'. '+score.player))
                .append($('<div>').addClass('time').text(timeToString(score.time)))
                // .append($('<div>').addClass('tries').text(score.tries))
            );
        });
    }
}