/**
 * Registers image annotation tool.
 * This only works if the AnnotationAssetBundle is registered first.
 */
Skeleton.registerAnnotations = function (url) {
    var $image = $('#image'),
        $toolbar = $('<div id="image-toolbar"/>').insertBefore($image);

    var anno = Annotorious.init({
        image: 'image',
        locale: $('html').attr('lang'),
        disableEditor: true
    });

    anno.on('createSelection', async function (selection) {
        var selector = selection.target.selector,
            data = {
                width: $image.width(),
                height: $image.height()
            };

        if (selector.type === 'SvgSelector') {
            data.data = $(selector.value).html();
        }

        console.log(data);

        anno.updateSelected(selection, true);
    });

    // Init the selector plugin
    Annotorious.SelectorPack(anno);

    // Register toolbar and fix button type (still needed in version 1.0.2)
    Annotorious.Toolbar(anno, $toolbar[0]);
    $toolbar.find('button').attr('type', 'button');
};