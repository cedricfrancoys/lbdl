/**
 * User Singleton.
 *
 */
var User = new (function() {

    /**
     * @type Promise promise
     */
    var promise = {};

    /**
     * return object interface
     */

    init();

    return {
        get:        () => promise,
        disconnect: () => disconnect()
    };

    /**
     * private methods
     */
    function init() {
        promise = new Promise( (resolve, reject) => {
            // retrieve user
            fetch('/?get=userinfo')
                .then( async (response) => {
                    if(response.status != 200) {
                        reject(response);
                    }
                    else {
                        var json = await response.json();
                        resolve(json);
                    }
                })
                .catch( (response) => {
                    reject(response);
                });
        });
    }

    function disconnect() {
        fetch('/?do=user_signout')
        .then( async (response) => {
            window.location.href = 'index.html';
        })
        .catch( (response) => {
            console.log('unexpected error');
        });
    }

});


$(document).ready( () => {
    // retrieve user
    User.get()
        .then( (user) => {
            // user.name = user.firstname + ' ' + user.lastname;
            if(window.hasOwnProperty('player')) {
                window.player = user;
            }
            $('#user-name--value').text(user.name);
            $('#menu-login').hide();
            $('#menu-logout').show();
            $('#action-clone--btn').show();
        })
        .catch( (response) => {
            // non identified user
            let name = 'guest'+ Math.floor(Math.random() * 999 + 1);
            if(window.hasOwnProperty('player')) {
                window.player.name = name;
            }
            $('#user-name--value').text(name);
            $('#menu-login').show();
            $('#menu-logout').hide();
            $('#action-clone--btn').hide();
        });
});
