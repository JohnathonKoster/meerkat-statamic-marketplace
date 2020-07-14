1.5.84
- Fixes an issue when resolving comments with `all-comments` when more than one filter are applied

1.5.82
- Adds new helper data attributes for working with inline editing. See https://github.com/Stillat/meerkat-examples for an example of working with inline editing in your site's theme.

1.5.81
- Improves compatibility with latest Statamic versions

1.5.8
- Allows for greater control over user permissions in the Control Panel. See https://stillat.com/meerkat/docs/permissions for more details.
- Improves overall Meerkat security though improved access control
- IMPROVE: Removes internal use of Meerkat helpers.php file in favor of instance methods:
   - Reduces global function pollution
   - Issue(s) where helpers would not load on some installations
   - NOTE: The existing helper functions have not been removed for compatibility with existing integrations
- Renames /Http/Composers/JavascriptComposer.php to /Http/Composers/JavaScriptComposer.php
- Deprecates the use of Meerkat's Compass Server and Bag purchases
- `license_key` removed from the default Meerkat settings.yaml
- Replaced `{{ meerkat_trans() }}` in Blade resources with `{{ translate('addons.Meerkat::XXXXXXXX`) }}`

1.5.74
- Improves compatibility between Recaptcha addon and Meerkat Replies through `replies-to`

1.5.73
- FIX: Multiple issues preventing CP actions from completing, or correctly display messages or current state
- FIX: Corrects an overly-aggressive validation check that prevent replies from being detected correctly

1.5.72
- FIX: Corrects the behavior of recursive comments
  For best results, please wrap the recursive call in the parent element (`ul` in this case):
  
```html
{{ meerkat:responses as="replies" }}

    <ul>
        {{ replies }}
        <li>
            {{ id }} - {{ comment }}


            {{ if has_replies }}

            <ul>{{ *recursive replies* }}</ul>

            {{ /if }}
        </li>

        {{ /replies }}
    </ul>

{{ /meerkat:responses }}
```

1.5.71
- IMPROVE: Removes unused findWhere

1.5.70
- FIX: Corrects an issue preventing comments from being correctly saved
- FIX: Corrects a bug that prevented error messages from being displayed
- NEW: Adds a `errors.error_in` translation string that is used when reporting errors on the Control Panel
- NEW: Adds a `errors.comment_no_content` translation string that is supplied when a comment manifests without content
- Improvements:
    - If an error occurs when loading a comment from the data-source, it will not block all comments from loading
        - Exception output and comment IDs logged to the Error log.
    - Source data stored internally with the comment submission, to make mutation observation more reliable
    - Control Panel error messages provide more details
    - Comment Actions visible by default (increased opacity)
    - New UI that displays ongoing comment action
    - Expands Spam Guard CP error reporting (Guard contract expanded)
    - Third-party Guard service failures no longer block save operations
    - CP error messages will correctly reflect Guard operation results
    - Simplified comment save logic
    
- Translation Strings Added:
    - actions.approving
    - actions.unapproving
    - actions.replying
    - actions.spam_submitting
    - actions.delete_removing
    - errors.error_in
    - errors.comment_no_content
    - errors.guard_service_error
    - errors.guard_comment_saved_error
    - errors.guard_comment_ham_saved_error
    - errors.guard_multiple_spam_submit_errors
    - errors.guard_multiple_ham_submit_errors
    
1.5.64
- Adds the `getPublishedCommentCount` helper function the Meerkat API to easily get the total number of replies, including replies for a comment stream.
  - Usage (from within site/helpers/Tags.php) `$this->api('Meerkat')->getPublishedCommentCount($postId)`

1.5.63
- Emits the Statamic Form.submission.creating event to provide compatibility with other plugins
    - If a third-party, or configuration value, causes the 'creating' event to fail, a 'creating' error entry will be provided
    - Control Panel, or other authenticated users, will be by-pass "captcha" validation messages by default
      This behavior can be changed by adding a `captcha_auth_bypass` configuration entry to `settings/addons/meerkat.yaml` and setting it's value to `false`
1.5.62
- Patches the Meerkat Statamic Control Panel translations with fallback values when appropriate
- Improves locale detection in the API Comments response 

1.5.61
- Ensures that reply-to fields are an array to prevent errors

1.5.6
- Fixed a bug that prevented comments from displaying when viewing an individual post/page in the Control Panel
- Allows non-super admins to moderate contents
- Prevents Akismet API call failures from stopping requests
- Fixes a bug that would cause comment failure on non well-formed comment submissions

1.5.5
Fix
- Updates Meerkat to be compatible with Statamic's session and event handlers (#10 https://github.com/Stillat/meerkat-v1hub/issues/10)

1.5.4
Fix
- Corrects the Meerkat form configuration URL ([GH #6](https://github.com/Stillat/meerkat-v1hub/issues/6))
- Corrects the Comments API count ([GH #3](https://github.com/Stillat/meerkat-v1hub/issues/3))

Improvements
- Updates Meerkat for Statamic 2.11.12 compatbility ([GH #8](https://github.com/Stillat/meerkat-v1hub/issues/8))

1.5.3
Fix
- Resolves a JavaScript path issue

Improvements
- Improved and streamlined the Composer setup process

1.5.2
Fix
- Resolves an issue with missing files from the installation setup.
- Fixes an issue for the latest Statamic 2.x versions.


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