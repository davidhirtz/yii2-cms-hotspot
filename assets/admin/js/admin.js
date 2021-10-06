/**
 * Registers image annotation tool.
 * This only works if the AnnotationAssetBundle is registered first.
 */
Skeleton.registerAnnotations = function (url) {
    var $image = $('#image'),
        $canvas = $image.parent().css('position', 'relative'),
        buttons = [],
        zIndex = 0;

    function initButton(url, x, y) {
        var $btn = $('<a href="' + url + '" class="btn btn-primary btn-annotation"  title="test"><i class="fas fa-wrench"></i></a>')
                .appendTo($canvas)
                .tooltip(),
            btnXOffset = $btn.outerWidth() / 2,
            btnYOffset = $btn.outerHeight() / 2;

        $btn.css({
            position: 'absolute',
            left: x - btnXOffset,
            top: y - btnYOffset,
            zIndex: zIndex++
        });

        $btn.draggable({
            containment: $canvas,
            start: function () {
                $btn.css('z-index', ++zIndex).addClass('btn-secondary').tooltip('hide');
            },
            stop: function () {
                $btn.removeClass('btn-secondary');

                console.log({
                    x: ($btn.position().left + btnXOffset) / $canvas.width() * 100,
                    y: ($btn.position().top + btnYOffset) / $canvas.height() * 100,
                    position: buttons.length
                })
            }
        });

        buttons.push($btn);
    }

    $image.on('dblclick', function (e) {
        var x = e.pageX - $canvas.offset().left,
            y = e.pageY - $canvas.offset().top;

        initButton('/admin', x, y, buttons.length);
    });
};