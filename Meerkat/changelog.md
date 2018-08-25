1.5.0

Improvements
- Added the following translation strings:
    actions.configure
    actions.cancel
    actions.prompt_confirm
    actions.prompt_confirm_action
    comments.table_author
    comments.table_comment
    exports.name
    exports.comment
    exports.email
    exports.date
- Fixed a visual glitch with the "Exporter" button in the Control Panel
- The comment table headers can now be translated
- The CSV export headers can now be translated

Fix
- Items from the Comments API are now apply the correct language translation


1.4.0

Improvements
- Removed the requirement of Composer dependencies to improve the installation experience across Statamic 2.x versions
- Improved the internal settings management components

Fix
- Fixed an issue with the dashboard widget's JavaScript causing JavaScript errors on non-dashboard pages
- Improved Statamic version 2.x compatibility

1.3.26

Improvements
- The headings on the various views within the Meerkat Control Panel can now be localized
- Checking for spam will now longer flag previously cleared comments as spam
- Improved Control Panel compatibility for Statamic Versions newer than 2.1.0 (with fallback for older versions)
- The Meerkat Control Panel navigation item will automatically update in Statamic versions 2.1.0 and higher as changes take place within the Control Panel
- The Control Panel will now automatically refresh the view state after bulk actions have taken place
- The Control Panel will now automatically update the comment count whenever someone leaves a comment on your site without needing a refresh.

1.2.0 (January 2nd, 2018)

Security
- Patched a vulnerability that would allow arbitrary Antlers template execution

Improvements
- Export text in Control Panel can be localized

Fix
- Fixed the export links in the Control Panel

1.1.17 (October 22nd, 2017)

Improvements
- Improves compatibility with Statamic Control Panel

1.0.3 (September 15th, 2017)

Improvements
- Filters will no longer disappear when clicking a filter with no items.
- Clicking a filter on the dashboard will now update the history and URL
- Added a "Loading" spinner to indicate progress
- The dashboard widget now features a table to help make understanding the chart easier

Fix
- Actions now appear on mobile devices
- Pagination now appears on mobile devices
- Pagination links are now correctly centered
- Fixed a bug that prevent replies from being created from the Control Panel
- The `has_replies` comment flag will correctly indicate if a comment has replies, even if the comment collection is not returned as a flat list.
- The "Remove Comments" bulk actions now display the correct feedback messages.

Added
- Added Comment method 'getStreamName()'
- JSON Exporter
- CSV  Exporter
- Display comments on posts within the admin panel
- Designer Mode (to provide randomized demo content for theme developers)

Misc
- Added a `meerkat_path` helper to quickly resolve the addon path to Meerkat