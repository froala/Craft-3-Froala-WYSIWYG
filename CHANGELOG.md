# Craft CMS 3 - Froala WYSIWYG Editor Changelog

## Unreleased

### Changed

- Fixed issue regarding inserting a file link with correct Craft's Asset referenced-tag `{asset:<id>}`. 

## v2.8.4 - 2018-07-15

### Changed

- Updated editor to version 2.8.4 along with the plugin version

## v2.8.3 - 2018-07-15

### Changed

- Updated editor to version 2.8.3 along with the plugin version

## v2.8.2 - 2018-07-15

### Changed

- Updated editor to version 2.8.2 along with the plugin version

## v2.8.1 - 2018-07-11

### Added

- Permission to grant users or groups to toggle HTML code view (not admins only).

### Removed

- Code view option via plugin settings (since it's a permission now).

## v2.8.1-rc.5 - 2018-07-08

### Changed

- Fixed composer dependency version constraint to use exact version of Froala Editor (v2.8.1)

## v2.8.1-rc.4 - 2018-07-08

### Changed

- [#2] Fixed hitting control/cmd+s when editor is focused and saving the element.
- [#3] Fixed issue reqarding disabling enabled plugins.

[#2]: https://github.com/froala/Craft-3-Froala-WYSIWYG/issues/2
[#3]: https://github.com/froala/Craft-3-Froala-WYSIWYG/issues/3

### Added

- Support for passing Craft's target language as editor language (if exists)
- [#4] Example JSON config added to the repository

[#4]: https://github.com/froala/Craft-3-Froala-WYSIWYG/issues/4

## v2.8.1-rc.3 - 2018-06-30

### Changed

- Loading translations from the Froala-editor category (instead of none).
- Formatted plugin init() method to easier read the event-listeners.

## v2.8.1-rc.2 - 2018-06-30

### Added

- [#1] Support for custom editor configuration per field instead of plugin-wide only

[#1]: https://github.com/froala/Craft-3-Froala-WYSIWYG/issues/1

### Changed

- Loading custom editor configurations, actually passing to the editor
- Styling toolbar separator causing button to appear on the right

## v2.8.1-rc.1 - 2018-06-28

- Initial release of the Craft CMS 3 plugin for Froala WYSIWYG