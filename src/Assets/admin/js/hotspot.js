/**
 * Registers image hotspot tool.
 * This only works if the HotspotAssetBundle is registered first.
 *
 * @param {String} config.formName
 * @param {String} config.url
 * @param {String} config.message
 * @param {Array} config.hotspots
 */
Skeleton.registerHotspots = function (config) {
    var $image = $('#image'),
        $canvas = $image.parent().addClass('hotspot-canvas'),
        hotspots = config.hotspots || [],
        zIndex = 0,
        i;

    if (config.message) {
        $canvas.closest('form').prepend('<div class="alert alert-success">' + config.message + '</div>');
    }

    for (i = 0; i < hotspots.length; i++) {
        setHotspot(hotspots[i]);
    }

    /**
     * @param {Object} values
     * @returns {{}}
     */
    function getFormFields(values) {
        var fields = {};
        fields[config.formName] = values;

        return fields;
    }

    /**
     * @param {String} data.id
     * @param {String} data.displayName
     * @param {Number} data.x
     * @param {Number} data.y
     * @param {String} data.url
     */
    function setHotspot(data) {
        var $btn = $('<a href="' + data.url + '" class="hotspot-btn" title="' + data.displayName + '"><i class="hotspot-icon fas fa-plus"></i></a>')
                .appendTo($canvas)
                .tooltip(),
            btnOffsetX = $btn.outerWidth() / 2,
            btnOffsetY = $btn.outerHeight() / 2;

        $btn
            .css({
                left: 'calc(' + data.x + '% - ' + btnOffsetX + 'px)',
                top: 'calc(' + data.y + '% - ' + btnOffsetY + 'px)',
                zIndex: zIndex++
            })
            .draggable({
                containment: $canvas,
                start: function () {
                    $btn.css('z-index', zIndex + 1).tooltip('disable').tooltip('hide').addClass('dragging');
                },
                stop: function () {
                    $.post(data.url, getFormFields({
                        x: ($btn.position().left + btnOffsetX) / $canvas.width() * 100,
                        y: ($btn.position().top + btnOffsetY) / $canvas.height() * 100,
                        position: zIndex + 1
                    }));

                    setTimeout(function () {
                        $btn.tooltip('enable').removeClass('dragging');
                    }, 1);
                }
            })
            .on('click', function (e) {
                if ($btn.hasClass('dragging')) {
                    e.preventDefault();
                }
            });
    }

    $image.on('dblclick', function (e) {
        var fields = getFormFields({
            x: (e.pageX - $canvas.offset().left) / $canvas.width() * 100,
            y: (e.pageY - $canvas.offset().top) / $canvas.height() * 100,
            position: hotspots.length + 1
        });

        $.post(config.url, fields).done(function (data) {
            setHotspot(data);
        });
    });
};