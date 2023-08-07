/**
 * Those constants match the indexes of related images in the ImageArray.
 */

function Map(map) {

    this.map = map;

    this.miniMap = function () {
        var $map = $('<div>').css({width: '90px', height: '90px'});
        for (var y = 0; y < 18; y++) {
            for(var x = 0; x < 18; ++x) {
                var image_index = this.map.board[(y*18)+x];
                if(x == map.pos_x && y == map.pos_y) {
                    image_index = 9;
                }
                $map.append(
                    $('<img>')
                    .css({height: '5px', width: '5px', float: 'left'})
                    .prop('src', imagesArray[image_index].src)
                );
            }
        }
        return $map;
    }

    /**
     * declare and init private vars
     */

    var imagesArray = loadImages();


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

    function loadImages(skin) {
        return new loadImagesArray(
            'assets/img/mini_blank.png',  // 0
            'assets/img/mini_bush.png',   // 1
            'assets/img/mini_block.png',  // 2
            'assets/img/mini_water.png',  // 3
            'assets/img/mini_stairs.png', // 4
            'assets/img/mini_key.png',    // 5
            'assets/img/mini_door.png',   // 6
            'assets/img/mini_wall.png',   // 7
            'assets/img/mini_bush.png',   // 8
            'assets/img/mini_me.png'      // 9
        );
    }

}
