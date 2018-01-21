$(function() {

    $.extend($.FE.DEFAULTS, {
        craftFileStorageKey: false,
        craftFileSources: [],
        craftFileCriteria: false
    });

    $.FE.RegisterCommand('insertFile', $.extend($.FE.COMMANDS['insertFile'], {
        callback: function (cmd, val) {
            var _editor = this;

            // save selection before modal is shown
            this.selection.save();

            var modal = Craft.createElementSelectorModal(this.opts.craftAssetElementType, {
                storageKey: (this.opts.craftFileStorageKey || 'FroalaInput.ChooseImage.' + this.opts.craftAssetElementType),
                multiSelect: true,
                sources: this.opts.craftFileSources,
                criteria: $.extend({ siteId: this.opts.craftElementSiteId, kind: ['excel', 'pdf', 'powerpoint', 'text', 'word'] }, this.opts.craftFileCriteria),
                onSelect: $.proxy(function(assets, transform) {
                    if (assets.length) {
                        for (var i = 0; i < assets.length; i++) {
                            var asset = assets[i];

                            var url = asset.url + '#asset:' + asset.id;
                            if (transform) {
                                url += ':' + transform;
                            }

                            _editor.file.insert(url, asset.label);
                        }

                        return true;
                    }
                }, this),
                closeOtherModals: false
            });
        }
    }));

});