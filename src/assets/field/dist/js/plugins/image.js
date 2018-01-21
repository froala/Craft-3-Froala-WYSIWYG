$(function() {

    $.extend($.FE.DEFAULTS, {
        craftImageStorageKey: false,
        craftImageSources: [],
        craftImageCriteria: false,
        craftImageTransforms: []
    });

    $.FE.DefineIcon('insertAssetImage', { NAME: 'image' });
    $.FE.RegisterCommand('insertAssetImage', {
        title: 'Insert Image',
        focus: true,
        refreshAfterCallback: true,
        callback: function (cmd, val) {
            var _editor = this;

            // save selection before modal is shown
            this.selection.save();

            var modal = Craft.createElementSelectorModal(this.opts.craftAssetElementType, {
                storageKey: (this.opts.craftImageStorageKey || 'FroalaInput.ChooseImage.' + this.opts.craftAssetElementType),
                multiSelect: true,
                sources: this.opts.craftImageSources,
                criteria: $.extend({ siteId: this.opts.craftElementSiteId, kind: 'image' }, this.opts.craftImageCriteria),
                onSelect: $.proxy(function(assets, transform) {
                    if (assets.length) {
                        for (var i = 0; i < assets.length; i++) {
                            var asset = assets[i],
                                url = asset.url + '#' + this.opts.craftAssetElementRefHandle + ':' + asset.id;

                            if (transform) {
                                url += ':' + transform;
                            }

                            _editor.image.insert(url);
                        }

                        return true;
                    }
                }, this),
                closeOtherModals: false,
                transforms: this.opts.craftImageTransforms
            });
        }
    });
});