$(function() {

    $.extend($.FE.DEFAULTS, {
        craftImageStorageKey: false,
        craftImageSources: [],
        craftImageCriteria: false,
        craftImageTransforms: []
    });

    $.FE.RegisterCommand('insertImage', $.extend($.FE.COMMANDS['insertImage'], {
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

                            _editor.image.insert(url, false, { 'asset-id': asset.id });
                        }

                        return true;
                    }
                }, this),
                closeOtherModals: false,
                transforms: this.opts.craftImageTransforms
            });
        }
    }));

    $.FE.RegisterCommand('imageReplace', $.extend($.FE.COMMANDS['imageReplace'], {
        callback: function(cmd, val) {
            var _editor = this,
                disabledElementIds = [],
                $currentImage = this.image.get();

            // find out the current asset id based on data-attribute
            if ($currentImage.data('assetId')) {
                disabledElementIds.push($currentImage.data('assetId'));
            } else if ($currentImage.attr('src').indexOf('#') !== -1) {

                // otherwise check the src url containing '#asset:{id}[:{transform}]'
                var hashValue = $currentImage.attr('src').substr($currentImage.attr('src').indexOf('#'));
                    hashValue = decodeURIComponent(hashValue);

                if (hashValue.indexOf(':') !== -1) {
                    disabledElementIds.push(hashValue.split(':')[1]);
                }
            }

            var modal = Craft.createElementSelectorModal(this.opts.craftAssetElementType, {
                storageKey: (this.opts.craftImageStorageKey || 'FroalaInput.ChooseImage.' + this.opts.craftAssetElementType),
                multiSelect: true,
                sources: this.opts.craftImageSources,
                criteria: $.extend({ siteId: this.opts.craftElementSiteId, kind: 'image' }, this.opts.craftImageCriteria),
                disabledElementIds: disabledElementIds,
                onSelect: $.proxy(function(assets, transform) {
                    if (assets.length) {
                        for (var i = 0; i < assets.length; i++) {
                            var asset = assets[i],
                                url = asset.url + '#asset:' + asset.id;

                            if (transform) {
                                url += ':' + transform;
                            }

                            _editor.image.insert(url, false, { 'asset-id': asset.id }, $currentImage);
                        }

                        return true;
                    }
                }, this),
                closeOtherModals: false,
                transforms: this.opts.craftImageTransforms
            });
        }
    }));

});