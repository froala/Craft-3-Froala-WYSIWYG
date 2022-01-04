(function (FroalaEditor) {
    FroalaEditor.DEFAULTS = Object.assign(FroalaEditor.DEFAULTS, {
        // general
        craftElementSiteId: false,
        craftAssetElementType: false,
        craftAssetElementRefHandle: false,
        // links
        craftLinkCriteria: false,
        craftLinkSources: [],
        craftLinkStorageKey: false,
        craftLinkElementType: false,
        craftLinkElementRefHandle: false,
        // images
        craftImageCriteria: false,
        craftImageSources: [],
        craftImageStorageKey: false,
        craftImageTransforms: [],
        // files
        craftFileCriteria: false,
        craftFileSources: [],
        craftFileStorageKey: false,

        linkInsertButtons: ['craftLinkEntry', 'craftLinkAsset']
    });

    // Define the plugin.
    // The editor parameter is the current instance.
    FroalaEditor.PLUGINS.craft = function (editor) {
        function showEntrySelectModal() {
            var disabledElementIds = [],
                $popup = editor.popups.get('link.insert'),
                selectedText = (editor.selection.text() || false);

            // save selection before modal is shown
            var $currentImage = editor.image.get();
            if (!$currentImage) {
                editor.selection.save();
            }

            // check the src url containing '#asset:{id}[:{transform}]'
            var urlValue = $popup.find('input[name="href"]').val();
            if (urlValue && urlValue.indexOf('#') !== -1) {

                var hashValue = urlValue.substr(urlValue.indexOf('#'));
                hashValue = decodeURIComponent(hashValue);

                if (hashValue.indexOf(':') !== -1) {
                    disabledElementIds.push(hashValue.split(':')[1]);
                }
            }

            _elementModal(
                editor.opts.craftLinkElementType,
                editor.opts.craftLinkStorageKey,
                editor.opts.craftLinkSources,
                editor.opts.craftLinkCriteria, {
                    transforms: editor.opts.craftImageTransforms
                },
                function (elements) {
                    if ($currentImage) {
                        editor.image.edit($currentImage);
                    } else {
                        editor.selection.restore();
                    }

                    // re-focus the popup
                    if (!editor.popups.isVisible('link.insert')) {
                        editor.popups.show('link.insert');
                    }

                    // add-in element link details
                    if (elements.length) {
                        var element = elements[0],
                            url = element.url + '#' + editor.opts.craftLinkElementRefHandle + ':' + element.id,
                            title = selectedText.length > 0 ? selectedText : element.label;

                        $popup.find('input[name="href"]').val(url);
                        $popup.find('input[name="text"]').val(title);
                    }
                }
            );
        }

        function showImageInsertModal() {
            // save selection before modal is shown
            editor.selection.save();

            _elementModal(
                editor.opts.craftAssetElementType,
                editor.opts.craftImageStorageKey,
                editor.opts.craftImageSources,
                editor.opts.craftImageCriteria,
                null,
                function (assets, transform) {
                    if (assets.length) {
                        for (var i = 0; i < assets.length; i++) {
                            var asset = assets[i],
                                url = asset.url + '#' + editor.opts.craftAssetElementRefHandle + ':' + asset.id;

                            if (transform) {
                                url += ':' + transform;
                            }

                            editor.image.insert(url, false);
                        }

                        return true;
                    }
                }
            );
        }

        function showImageReplaceModal() {
            var disabledElementIds = [],
                $currentImage = editor.image.get();

            // check the src url containing '#asset:{id}[:{transform}]'
            if ($currentImage.attr('src').indexOf('#') !== -1) {

                var hashValue = $currentImage.attr('src').substr($currentImage.attr('src').indexOf('#'));
                hashValue = decodeURIComponent(hashValue);

                if (hashValue.indexOf(':') !== -1) {
                    disabledElementIds.push(hashValue.split(':')[1]);
                }
            }

            _elementModal(
                editor.opts.craftAssetElementType,
                editor.opts.craftImageStorageKey,
                editor.opts.craftImageSources,
                editor.opts.craftImageCriteria, {
                    disabledElementIds: disabledElementIds,
                    transforms: editor.opts.craftImageTransforms
                },
                function (assets, transform) {
                    if (assets.length) {
                        for (var i = 0; i < assets.length; i++) {
                            var asset = assets[i],
                                url = asset.url + '#asset:' + asset.id;

                            if (transform) {
                                url += ':' + transform;
                            }

                            editor.image.insert(url, false, [], $currentImage);
                        }

                        return true;
                    }
                }
            );
        }

        function showFileInsertModal(viaPopup) {
            var viaPopup = viaPopup || false,
                disabledElementIds = [],
                selectedText = (editor.selection.text() || false);

            if (viaPopup) {
                var $popup = editor.popups.get('link.insert');

                // check the src url containing '#asset:{id}[:{transform}]'
                var urlValue = $popup.find('input[name="href"]').val();
                if (urlValue && urlValue.indexOf('#') !== -1) {

                    var hashValue = urlValue.substr(urlValue.indexOf('#'));
                    hashValue = decodeURIComponent(hashValue);

                    if (hashValue.indexOf(':') !== -1) {
                        disabledElementIds.push(hashValue.split(':')[1]);
                    }
                }
            }

            // save selection before modal is shown
            editor.selection.save();

            _elementModal(
                editor.opts.craftAssetElementType,
                editor.opts.craftFileStorageKey,
                editor.opts.craftFileSources,
                editor.opts.craftFileCriteria, {
                    disabledElementIds: disabledElementIds
                },
                function (elements) {

                    // re-focus the popup
                    if (viaPopup && !editor.popups.isVisible('link.insert')) {
                        editor.popups.show('link.insert');
                    }

                    if (elements.length) {
                        var element = elements[0],
                            url = element.url + '#' + editor.opts.craftAssetElementRefHandle + ':' + element.id,
                            title = selectedText.length > 0 ? selectedText : element.label;

                        if (viaPopup) {
                            // no title replace at update
                            $popup.find('input[name="href"]').val(url);
                        } else {
                            editor.link.insert(url, title);
                        }

                        return true;
                    }
                }
            );
        }

        function _elementModal(type, storageKey, sources, criteria, addOpts, callback) {

            var modalOpts = {
                storageKey: (storageKey || 'Froala.Craft.Modal.' + type),
                sources: sources,
                criteria: criteria,
                onSelect: $.proxy(callback, editor),
                closeOtherModals: false
            };

            if (typeof addOpts !== 'undefined') {
                modalOpts = $.extend(modalOpts, addOpts);
            }

            var modal = Craft.createElementSelectorModal(type, modalOpts);
        }

        // The start point for your plugin.
        function _init() {
            /*
            LINK REPLACEMENTS & ADDITIONS
            */

            FroalaEditor.DefineIcon('craftLinkEntry', {
                NAME: 'newspaper-o',
                template: 'font_awesome'
            });
            FroalaEditor.RegisterCommand('craftLinkEntry', {
                title: 'Link to Craft Entry',
                undo: false,
                focus: true,
                refreshOnCallback: false,
                popup: true,
                callback: function () {
                    editor.craft.showEntrySelectModal();
                }
            });

            FroalaEditor.DefineIcon('craftLinkAsset', {
                NAME: 'file-o',
                template: 'font_awesome'
            });
            FroalaEditor.RegisterCommand('craftLinkAsset', {
                title: 'Link to Craft Asset',
                focus: true,
                refreshOnCallback: true,
                callback: function () {
                    editor.craft.showFileInsertModal(true);
                }
            });

            /*
                IMAGE REPLACEMENTS & ADDITIONS
            */

            FroalaEditor.RegisterCommand('insertImage', Object.assign(FroalaEditor.COMMANDS['insertImage'], {
                callback: function (cmd, val) {
                    editor.craft.showImageInsertModal();
                }
            }));

            FroalaEditor.RegisterCommand('imageReplace', Object.assign(FroalaEditor.COMMANDS['imageReplace'], {
                callback: function (cmd, val) {
                    editor.craft.showImageReplaceModal();
                }
            }));

            /*
                FILE REPLACEMENTS & ADDITIONS
            */

            FroalaEditor.RegisterCommand('insertFile', Object.assign(FroalaEditor.COMMANDS['insertFile'], {
                callback: function (cmd, val) {
                    editor.craft.showFileInsertModal();
                }
            }));

            /*
                SHORTCUT REPLACEMENT FOR CRAFT'S SAVE ACTION
            */

            FroalaEditor.RegisterShortcut(FroalaEditor.KEYCODE.S, null, null, null, false, false);
        }

        // Expose public methods. If _init is not public then the plugin won't be initialized.
        // Public method can be accessed through the editor API:
        // editor.myPlugin.publicMethod();
        return {
            _init,
            showEntrySelectModal: showEntrySelectModal,
            showImageInsertModal: showImageInsertModal,
            showImageReplaceModal: showImageReplaceModal,
            showFileInsertModal: showFileInsertModal
        }
    }
})(FroalaEditor);