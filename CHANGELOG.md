# Craft CMS 3 - Froala WYSIWYG Editor Changelog

- Updated editor to version 4.0.2 along with the plugin version
## v4.0.2 - 2021-06-30

- Updated editor to version 4.0.1 along with the plugin version
## v4.0.1 - 2021-05-18

- Updated editor to version 3.2.6-1 along with the plugin version
## v3.2.6-1 - 2021-02-06

- Updated editor to version 3.2.6 along with the plugin version
## v3.2.6 - 2021-01-25

### Changed


- Updated editor to version 3.2.5-2 along with the plugin version
## v3.2.5-2 - 2021-01-06


### Changed

- Updated editor to version 3.2.5-1 along with the plugin version

## v3.2.1 - 2020-08-05

### Changed

- Updated editor to version 3.2.1 along with the plugin version

## v3.2.0 - 2020-07-23

### Changed

- Updated editor to version 3.2.0 along with the plugin version

## v3.1.0 - 2020-01-28

### Changed

- Updated editor to version 3.1.0 along with the plugin version

## v2.9.5 - 2019-06-08

### Changed

- Updated editor to version 2.9.5 along with the plugin version

## v2.9.4 - 2019-06-08

### Changed

- Updated editor to version 2.9.4 along with the plugin version
- Fix `Call to a member function getSettings() on null` by @reganlawton

## v2.9.3 - 2019-03-01

### Changed

- Updated editor to version 2.9.3 along with the plugin version

## v2.9.2.3 - 2019-02-14

### Changed

- Fixed checks in shorthand if, concatenating strings, in field service (@Zae [#9])

[#9]: https://github.com/froala/Craft-3-Froala-WYSIWYG/pull/9

## v2.9.2.2 - 2019-02-08

### Changed

- Fixed being compatible with Craft CMS 3.1 regaring using UID's instead of ID's ([#8])

[#8]: https://github.com/froala/Craft-3-Froala-WYSIWYG/pull/8

## v2.9.2.1 - 2019-02-07

### Changed

- Fixed checking volumes and displaying editor when configured well ([#7])

[#7]: https://github.com/froala/Craft-3-Froala-WYSIWYG/issues/7

## v2.9.2 - 2019-01-31

### Changed

- Improved checks on existence of image and file asset sources (volumes)
- Updated editor to version 2.9.2 along with the plugin version

## v2.9.1 - 2018-12-13

### Changed

- Updated editor to version 2.9.1 along with the plugin version

## v2.9.0 - 2018-12-13

### Changed

- Updated editor to version 2.9.0 along with the plugin version

## v2.8.5 - 2018-12-13

### Changed

- Made Craft CMS 3 as requirement for the package, since it's required for the plugin store
- Updated editor to version 2.8.5 along with the plugin version

## v2.8.4.3 - 2018-08-17

### Changed

- Fixed adding code-view plugin to enabled plugins when allowed to see code (permissions)
- Fixed comparing enabled plugins regarding core plugins

## v2.8.4.2 - 2018-07-19

### Changed

- Fixed bug loading Craft integrations when Enabled Plugins setting is set to something else than "All".

## v2.8.4.1 - 2018-07-18

### Changed

- Fixed loading custom CSS type via Asset bundle.
- Fixed issue with updating a file link and the Link update popup window.
- Fixed issue regarding inserting a file link with correct Craft's Asset referenced-tag `{asset:<id>}`.

### Removed

- Removed plugin support for custom CSS file loading. Since Craft 3 doesn't give the ability. 

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