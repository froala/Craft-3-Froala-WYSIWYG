$(function() {

    $.extend($.FE.DEFAULTS, {
        craftLinkCriteria: false,
        craftLinkSources: [],
        craftLinkStorageKey: false,
        craftLinkElementType: false,
        craftLinkElementRefHandle: false
    });

    $.FE.RegisterCommand('insertLink', $.extend($.FE.COMMANDS['insertLink'], {
        callback: function (cmd, val) {
            var _editor = this,
                _selectedText = (this.selection.text() || false);

            // save selection before modal is shown
            this.selection.save();

            var modal = Craft.createElementSelectorModal(this.opts.craftLinkElementType, {
                storageKey: (this.opts.craftLinkStorageKey || 'FroalaInput.LinkTo.' + this.opts.craftLinkElementType),
                sources: this.opts.craftLinkSources,
                criteria: $.extend({ siteId: this.opts.craftElementSiteId }, this.opts.craftLinkCriteria),
                onSelect: $.proxy(function(elements) {
                    if (elements.length) {
                        var element = elements[0],
                            url = element.url + '#' + this.opts.craftLinkElementRefHandle + ':' + element.id,
                            title = _selectedText.length > 0 ? _selectedText : element.label;

                        _editor.link.insert(url, title);

                        return true;
                    }
                }, this),
                closeOtherModals: false
            });
        }
    }));

    $.FE.RegisterCommand('linkEdit', $.extend($.FE.COMMANDS['linkEdit'], {
        callback: function (cmd, val) {
            var linkIsEntry = true,
                disabledElementIds = [],
                $currentLink = $(this.link.get());

            if ($currentLink.attr('href').indexOf('#') !== -1) {

                // otherwise check the src url containing '#asset:{id}[:{transform}]'
                var hashValue = $currentLink.attr('href').substr(($currentLink.attr('href').indexOf('#') + 1));
                    hashValue = decodeURIComponent(hashValue);

                if (hashValue.indexOf(':') !== -1) {
                    disabledElementIds.push(hashValue.split(':')[1]);

                    var linkKind = hashValue.split(':')[0];
                    switch (linkKind) {
                        case 'asset':
                            linkIsEntry = false;
                            break;
                    }
                }
            }

            var modalElementType = (linkIsEntry ? this.opts.craftLinkElementType : this.opts.craftAssetElementType),
                modalCriteria = (linkIsEntry ? this.opts.craftLinkCriteria : this.opts.craftFileCriteria),
                modalStorageKey = (linkIsEntry ? this.opts.craftLinkStorageKey : this.opts.craftFileStorageKey),
                modalRefHandle = (linkIsEntry ? this.opts.craftLinkElementRefHandle : this.opts.craftAssetElementRefHandle);

            var modal = Craft.createElementSelectorModal(modalElementType, {
                storageKey: (modalStorageKey || 'FroalaInput.LinkTo.' + modalElementType),
                sources: (linkIsEntry ? this.opts.craftLinkSources : this.opts.craftFileSources),
                criteria: $.extend({ siteId: this.opts.craftElementSiteId }, modalCriteria),
                disabledElementIds: disabledElementIds,
                onSelect: $.proxy(function(elements) {
                    if (elements.length) {
                        var element = elements[0],
                            url = element.url + '#' + modalRefHandle + ':' + element.id;

                        $currentLink.attr('href', url);

                        return true;
                    }
                }, this),
                closeOtherModals: false
            });
        }
    }));

});