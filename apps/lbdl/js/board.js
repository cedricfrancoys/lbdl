/**
 * Those constants match the indexes of related images in the ImageArray.
 */

const EMPTY   	    = 0;
const BUSH    	    = 1;
const BLOCK   	    = 2;
const WATER   	    = 3;
const STAIRS  	    = 4;
const KEY     	    = 5;
const DOOR    	    = 6;
const GRID    	    = 7;
const FAKEBUSH	    = 8;
const START      	= 9;
const MOVE_UP      	= 10;
const MOVE_RIGHT   	= 11;
const MOVE_DOWN    	= 12;
const MOVE_LEFT    	= 13;
const STAY_STILL   	= 0;

const IDLE   	    = 0;
const RUNNING 	    = 1;
const TIMEOUT 	    = 2;
const SUNK    	    = 14;
const WON     	    = 15;

function PlayerInfo() {
    return {
        name: ''
    };
}

function GameState() {
    return {
        // map id
        id: 0,
        // max allowed time, in milliseconds
        time: 0,
        // current satus of the game
        status: IDLE,
        keys: 0,
        // current direction of the pawn
        direction: 0,
        // current pos of the pawn on the X-axis
        pos_x: 0,
        // current pos of the pawn on the Y-axis
        pos_y: 0,
        // initial state of the board
        board: [],
        trace: [],
        // current action of the user
        action: STAY_STILL
    }
}

function Timer() {
    return {
        // previous moment at which the time was measured
        time: 0,
        // remaining time before timeout
        left: 0,
        // id of the Timeout JS object
        timer_id: 0
    };
}

/**
 * Converts a time, in milliseconds, to a string of format mm:ss:cc
 *
 * @param {*} time
 * @returns
 */
function timeToString(time) {
    var t = Math.floor(time / 10);
    var cent = t%100;
    var sec = Math.floor(t/100)%60;
    var min = Math.floor(t/100/60);
    return min.toString().padStart(2, '0') + ":" + sec.toString().padStart(2, '0') + ":" + cent.toString().padStart(2, '0');
}

function getPos(x, y) {
    return (y*18)+x;
}

function Board() {

    /**
     * declare and init private vars
     */

    /** @var Object     global game state used in all methods: intended to be modified when game is running (set in `reset()`) */
    var gameState = null;

    /** @var Object     immutable state template, used for reset (set in `setMap()`) */
    var initialGameState = new GameState();

    var playerInfo = new PlayerInfo();

    var timer = new Timer();

    var imagesArray = loadImages();

    var tries = 0;

    showPopup("loading");

    /**
     * return object interface
     */

    return {
        listen:     (s) => listen(s),
        play:       ()  => play(),
        reload:     ()  => reset(),
        exportMap:  ()  => exportMap(),
        loadPlayer: (p) => loadPlayer(p),
        getMap:     ()  => getMap(),
        getPosX:    ()  => gameState.pos_x,
        getPosY:    ()  => gameState.pos_y,
        getChrono:  ()  => (gameState.time-timer.left),
        getTrace:   ()  => gameState.trace,
        getTries:   ()  => tries,
        setMap:     (m) => setMap(m),
        setSkin:    (s) => setSkin(s),
        setPosX:    (x) => (gameState.pos_x = x),
        setPosY:    (y) => (gameState.pos_y = y),
        setName:    (n) => (gameState.name = n),
        setTime:    (t) => setTime(t),
        setTries:   (t) => (tries = t),
        setKeys:    (k) => setKeys(k),
        setItem:    (x,y,i,s) => setItem(x,y,i,s),
        showPopup:  (p, d) => showPopup(p, d)
    };

    /**
     * private methods
     */

    function loadImagesArray() {
        this.length = loadImagesArray.arguments.length;
        for (var i = 0; i < this.length; i++) {
            this[i] = new Image(20,20);
            this[i].src = loadImagesArray.arguments[i];
        }
    }

    function listen(state) {
        if(state) {
            $(document).on('keydown', onkeydownHandler);
            $(document).on('keyup', onkeyupHandler);
        }
        else {
            $(document).off('keydown');
            $(document).off('keyup');
        }
    }

    function onkeyupHandler(event) {
        console.log('onkeyupHandler', event, gameState);
        if(gameState) {
            gameState.action = STAY_STILL;
        }
    }

    function onkeydownHandler(event) {
        console.log('onkeydownHandler', event, gameState);

        var key = (event)?event.keyCode:window.event.keyCode;


        if(gameState && gameState.status == RUNNING) {
            if([37, 38, 39, 40, 80].indexOf(key) == -1) {
                return;
            }

            switch(key){
                case 37:
                    gameState.action = MOVE_LEFT;
                break;
                case 38:
                    gameState.action = MOVE_UP;
                break;
                case 39:
                    gameState.action = MOVE_RIGHT;
                break;
                case 40:
                    gameState.action = MOVE_DOWN;
                break;
                // P
                case 80:
                    gameState.status = IDLE;
                    return;
                default:
            }
            move(gameState.action);
        }
        else {
            if([80, 82].indexOf(key) == -1) {
                return;
            }

            switch(key){
                // P
                case 80:
                    play();
                    break;
                // R
                case 82:
                    reset();
                    break;
                default:
            }
        }
    }

    function displayTime(time) {
        $("#board-time--value").val(timeToString(time));
    }

    function displayKeys(keys){
        $("#board-keys--value").val(keys + "");
    }

    function gameStateChanged() {
        if(gameState.status == TIMEOUT) {
            showPopup("timeout");
        }
        else if(gameState.status == WON) {
            // showMe();
            showPopup("won");
        }
        else if(gameState.status == SUNK) {
            showPopup("sunk");
        }
    }

    function showPopup(popup_name, data) {
        var $popup = $('#'+popup_name);
        switch(popup_name) {
            case 'won':
                $popup.find('#submit-score--player-name').show();
                $popup.find('#submit-score--button').show();
                $popup.find('#submit-score--confirm').hide();
                $popup.find('#player-name--value').val(playerInfo.name);
                $popup.find('#player-time--value').text(timeToString(gameState.time-timer.left));
                break;
        }
        // inject data and display
        $popup.data('data', data).show();
    }

    function hideAllPopup() {
        $('.popup').hide();
    }

    function hidePopup(popup_name) {
        $('#'+popup_name).hide();
    }

    function gameLoop() {
        if(gameState.status == RUNNING) {
            var time = (new Date).getTime();
            var diff = (time-timer.time);
            if (timer.left-diff > 0){
                timer.left -= diff;
                timer.time = time;
                displayTime(timer.left);
            }
            else {
                gameState.status = TIMEOUT;
                timer.left = 0;
                setTimeout(gameStateChanged, 1000);
            }
            timer.timer_id = setTimeout(gameLoop, 10);
        }
    }

    /**
     * Update the image displayed at the given (x,y) coordinate by setting the board cell with a given item.
     * @param integer   x       Cell coordinate on the X-axis.
     * @param integer   y       Cell coordinate on the Y-axis.
     * @param integer   item    An integer matching an entry from ImageArray.
     * @param boolean   shallow If set to true, only related image is affected (not the board itself). This is used to cover board without modifying its state.
     */
    function setItem(x, y, item, shallow = false) {
        if(x < 0 || x > 17 || y < 0 || y > 17) {
            return;
        }
        let index = getPos(x, y);
        if(!shallow) {
            gameState.board[index] = item;
        }
        $("img[data-index='"+index+"']").attr('src', imagesArray[item].src);
    }

    function getItem(x, y, initial = false) {
        if(x < 0 || x > 17 || y < 0 || y > 17) {
            return null;
        }
        return (initial)?initialGameState.board[getPos(x, y)]:gameState.board[getPos(x, y)];
    }

    function hideMe() {
        setItem(gameState.pos_x, gameState.pos_y, getItem(gameState.pos_x, gameState.pos_y));
    }

    function showMe() {
        setItem(gameState.pos_x, gameState.pos_y, gameState.direction, true);
    }

    function canMoveTo(item1, item2){
        if(item2 < 0) {
            item2 = BLOCK;
        }
        if(item1 < 0) {
            return 0;
        }
        if (item1 == EMPTY) {
            return 1;
        }
        if (item1 == FAKEBUSH) {
            return 1;
        }
        if (item1 == WATER) {
            return 1;
        }
        if (item1 == KEY) {
            return 1;
        }
        if (item1 == STAIRS) {
            return 1;
        }
        if (item1 == BLOCK && (item2 == EMPTY || item2 == WATER)) {
            return 1;
        }
        if (item1 == DOOR && gameState.keys > 0) {
            return 1;
        }
        return 0;
    }

    function move(dir) {

        var inc_x = 0, inc_y = 0;
        switch(dir) {
            case MOVE_LEFT:
                inc_x = -1;
                break;
            case MOVE_UP:
                inc_y = -1;
                break;
            case MOVE_RIGHT:
                inc_x = 1;
                break;
            case MOVE_DOWN:
                inc_y = 1;
                break;
        }

        // retrieve item at position targeted by the move...
        var item1 = getItem(gameState.pos_x+inc_x, gameState.pos_y+inc_y);
        // ...and retrieve item just behind it
        var item2 = getItem(gameState.pos_x+(2*inc_x), gameState.pos_y+(2*inc_y));
        // remember original direction for refreshing afterward in case of change
        var direction = gameState.direction;

        gameState.direction = dir;

        // record user move request
        gameState.trace.push({
            time: timer.time,
            direction: dir
        });

        if(canMoveTo(item1, item2)) {
            if(item1 == BLOCK) {
                if(item2 == WATER) {
                    setItem(gameState.pos_x+(2*inc_x), gameState.pos_y+(2*inc_y), EMPTY);
                }
                else {
                    setItem(gameState.pos_x+(2*inc_x), gameState.pos_y+(2*inc_y), BLOCK);
                }
                setItem(gameState.pos_x+inc_x, gameState.pos_y+inc_y, EMPTY);
            }
            if(item1 == KEY) {
                setItem(gameState.pos_x+inc_x, gameState.pos_y+inc_y, EMPTY);
                ++gameState.keys;
                displayKeys(gameState.keys);
            }
            if(item1 == DOOR) {
                setItem(gameState.pos_x+inc_x, gameState.pos_y+inc_y, EMPTY);
                --gameState.keys;
                displayKeys(gameState.keys);
            }
            if(item1 == WATER) {
                gameState.status = SUNK;
                gameState.direction = SUNK;
                setTimeout(gameStateChanged, 500);
            }
            if(item1 == STAIRS) {
                gameState.status = WON;
                gameState.direction = WON;
                setTimeout(gameStateChanged, 500);
            }

            hideMe();
            gameState.pos_x += inc_x;
            gameState.pos_y += inc_y;
            showMe();
        }
        else {
            if (direction != gameState.direction) {
                hideMe();
                showMe();
            }
        }
    }

    function loadImages(skin) {
        if(!arguments.length) {
            skin = 'default';
        }

        console.log('call loadImages()', skin);

        $('img#hg').prop('src', 'skins/'+skin+'/hg.gif');
        $('img#hh').prop('src', 'skins/'+skin+'/hh.gif');
        $('img#hd').prop('src', 'skins/'+skin+'/hd.gif');
        $('img#vg').prop('src', 'skins/'+skin+'/vg.gif');
        $('img#vd').prop('src', 'skins/'+skin+'/vd.gif');
        $('img#bg').prop('src', 'skins/'+skin+'/bg.gif');
        $('img#bh').prop('src', 'skins/'+skin+'/hb.gif');
        $('img#bd').prop('src', 'skins/'+skin+'/bd.gif');

        return new loadImagesArray(
                'skins/'+skin+'/blank.gif',     // 0
                'skins/'+skin+'/bush.gif',      // 1
                'skins/'+skin+'/block.gif',     // 2
                'skins/'+skin+'/water.gif',     // 3
                'skins/'+skin+'/steps.gif',     // 4
                'skins/'+skin+'/key.gif',       // 5
                'skins/'+skin+'/door.gif',      // 6
                'skins/'+skin+'/grid.gif',      // 7
                'skins/'+skin+'/fake.gif',      // 8
                'skins/'+skin+'/blank.gif',     // 9
                'skins/'+skin+'/up.gif',        // 10
                'skins/'+skin+'/right.gif',     // 11
                'skins/'+skin+'/down.gif',      // 12
                'skins/'+skin+'/left.gif',      // 13
                'skins/'+skin+'/sunk.gif',      // 14
                'skins/'+skin+'/won.gif'        // 15
            );
    }

    function displayBoard(board) {
        var $board = $('#game-board').empty();
        for (var y = 0; y < 18; y++) {
            $row = $('<tr>').appendTo($board);
            for(var x = 0; x < 18; ++x) {
                var i = (y*18)+x;
                $('<td>').addClass('board-cell').css({height: '20px', width: '20px'})
                    .append(
                        $('<img>')
                            .css({height: '20px', width: '20px'})
                            .prop('src', imagesArray[board[i]].src)
                            .attr('data-x', x)
                            .attr('data-y', y)
                            .attr('data-index', i)
                    )
                    .appendTo($row);
            }
        }
    }

    function loadPlayer(player) {
        playerInfo.name = player.name;
    }


    /**
     * this method is meant to be called only once at Board instanciation.
     */
    function setMap(map) {
        initialGameState.id = map.id;
        initialGameState.name = map.name;
        initialGameState.direction = map.direction;
        initialGameState.keys = map.keys;
        initialGameState.time = map.time*1000;
        initialGameState.pos_x = map.pos_x;
        initialGameState.pos_y = map.pos_y;
        initialGameState.trace = [];
        initialGameState.board = map.board.split('');

        $("#play_pane").css({ visibility: 'visible' });
        // reset tries
        tries = 0;
        // reset the board (set state initial game state)
        reset();
    }

    function getMap() {
        return {
            id: initialGameState.id,
            name: initialGameState.name,
            direction: initialGameState.direction,
            keys: initialGameState.keys,
            time: initialGameState.time / 1000,
            pos_x: initialGameState.pos_x,
            pos_y: initialGameState.pos_y,
            board: initialGameState.board.join('')
        };
    }

    /**
     * Export board map in its current state (used for map edition)
     */
    function exportMap() {
        return {
            id: gameState.id,
            name: gameState.name,
            direction: gameState.direction,
            keys: gameState.keys,
            time: gameState.time / 1000,
            pos_x: gameState.pos_x,
            pos_y: gameState.pos_y,
            board: gameState.board.join('')
        };
    }

    /**
     * Reset the board with the initial gamestate (from Map).
     * We assume the gameState have been set.
     */
    function reset() {
        ++tries;
        // reset game state by deep copy of the original state
        gameState = JSON.parse(JSON.stringify(initialGameState));

        displayBoard(gameState.board);
        displayKeys(gameState.keys);
        displayTime(gameState.time);

        hideAllPopup();
        showMe();
    }

    function play() {
        if(gameState.status == IDLE) {
            timer.time = (new Date()).getTime();
            timer.left = gameState.time;
            gameState.status = RUNNING;
            timer.timer_id = setTimeout(gameLoop, 10);
            showMe();
            // notify server of the new game
            fetch('/?do=lbdl_map_play&id='+gameState.id)
            .catch( (response) => {
                // There was an error
                console.warn('Something went wrong while notifying the server.', response);
            });
        }
    }

    function setSkin(skin) {
        imagesArray = loadImages(skin);
        displayBoard(gameState.board);
        showMe();
    }

    function setTime(time) {
        gameState.time = time;
        displayTime(time);
    }

    function setKeys(keys) {
        gameState.keys = keys;
        displayKeys(keys);
    }

}
