<?php

return [

    'publishing' => 'Publishing Settings',
    'publishing_instruct' => 'These settings allow you to configure how new comments are handled as well as some basic display settings.',

	'auto_publish' => 'Auto Publish Comments',
	'auto_publish_instruct' => 'Disable this to review your comments before they go live on your site.',

    'auto_publish_authenticated_users' => 'Auto Publish Authenticated User Comments',
    'auto_publish_authenticated_users_instruct' => 'When this option is enabled, comments created by users signed into the Control Panel will be automatically approved.',

    'cp_avatar_driver' => 'Comment Author Avatars',
    'cp_avatar_driver_instruct' => 'Which method would you like to use to display author\'s avatars in the Control Panel?',

    'security' => 'Security',
    'security_instruct' => 'These settings will help keep your site\'s reputation safe by allowing you to configure various security and spam settings.',

    'remove_spam_after' => 'Automatically Remove Comments After',
    'remove_spam_after_instruct' => 'The frequency at which spam comments should be automatically removed.',

    'auto_check_spam' => 'Automatically Check New Comments for Spam',
    'auto_check_spam_instruct' => 'When this option is enabled, new comments will automatically be checked for spam. If you are automatically publishing comments from authenticated users, those comments will not be checked.',

    'auto_delete_spam' => 'Automatically Delete Comments Flagged as Spam',
    'auto_delete_spam_instruct' => 'Automatically delete new comments that are flagged as spam. Turning this on will not give you the opportunity to review for comments incorrectly marked as spam!',

    'akismet_api_key' => 'Akismet API Key',
    'akismet_api_key_instruct' => 'If you would like to use the Akismet spam service, enter your Akismet API key here.',

    'akismet_front_page' => 'Akismet Front Page',
    'akismet_front_page_instruct' => 'To configure the front page (or blog URL) of your site enter a value here. If you enter nothing, the system will automatically use your Statamic site\'s URL.',

    'auto_submit_results' => 'Submit Spam/Ham Results',
    'auto_submit_results_instruct' => 'When enabled, Meerkat will submit comments marked as spam to your configured spam drivers; Meerkat will also submit comments you mark as "not spam" to help spam services to improve their service and detection methods. This means that some information from your site\'s comments will be submitted to third-party services.',

    'automatically_close_comments' => 'Automatically Close Comments',
    'automatically_close_comments_instruct' => 'After how many does should commenting be automatically disabled on posts? Enter "0" to always allow comments.',

    'license' => 'Meerkat License',
    'license_key' => 'Meerkat License',
    
    'license_instruct' => 'In order to use Meerkat on a public website, you must enter a valid license.',
    'license_key_instruct' => 'Enter the license key for this domain from your <a href="https://bag.stillat.com/licenses" target="_blank">Stillat Account</a>.',
    'license_submit' => 'Save License',
];