=== Plugin Name ===
Contributors: kandrews
Donate link: http://www.geerservices.com/wordpress-plugins/resume-jobs/
Author URI: http://www.geerservices.com
Plugin URI: http://www.geerservices.com/wordpress-plugins/resume-jobs/
Tags: resume submission, job postings, job listing, resume, jobs 
Requires at least: 3.3
Tested up to: 3.3.1
Stable tag: 1.9.6

Allows the admin to create and show job postings. Users can submit their resume in response to a posting or for general purposes. 

== Description ==

The Resume Submissions & Job Postings plugin will allow an admin to post jobs for their business. 


If the job is no longer active, it can be set to archive, and will no longer be shown on the listings page and widget.


When there are open jobs, users can go in and look at each one. They may also submit their resume for any job.


Once the user submits his/her information, the admin may look at or edit the submitted resume.


The admin may sort resumes by job, or anything else they search for.

= Features: =
* Post Jobs
* Users Submit Resume
* Enable Captcha
* Send User 'Thank You' Email
* Widget to Show Job Postings
* Give User Ability to Use TinyMCE on the Cover Letter and the Resume fields
* Lets Admin choose what is shown and required
* Automatically fills in the first name, last name, and email fields if the user is logged in
* Allow Admin to create input fields (coming soon)
* Allow User attachment (coming soon)
* Save/Download Submitted Resume as PDF (coming soon)

 
== Features ==

1. Post Jobs
2. Users Submit Resume
3. Enable Captcha
4. Send User 'Thank You' Email
5. Widget to Show Job Postings
6. Give User Ability to Use TinyMCE on the Cover Letter and the Resume fields
7. Lets Admin choose what is shown and required
8. Automatically fills in the first name, last name, and email fields if the user is logged in


== Installation ==

1. Upload the folder `resume-submissions-job-postings` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place the shortcodes `[resumeForm]` and `[jobPostings]` in their respected pages

== Frequently Asked Questions ==

= I click on "Submit Resume for this Job", but nothing happens or the page is not found. =

Make sure to have the "Resume Form Page" field filled out to go to the page that has the Form shortcode in it.

= I cannot get the Captcha to work correctly. =

You must make sure that the Captcha Key fields are correctly filled out. Also, make sure that the url for those keys is the same url as this site.

= Where can I get the Captcha Keys? =
The Keys can be downloaded at [reCaptcha](https://www.google.com/recaptcha). Follow the steps and you will have your keys.

== Screenshots ==

1. The Resume Submissions lets you view or edit submitted user resumes. Can also sort by job or keyword.
2. The Job Posting screen where you can add, edit, delete, or archive jobs.
3. The Settings page where you can set Captcha or User Email.

== Changelog ==
= 1.9.6 =
* Fixed some coding errors that were in update 1.9.5
* Fixed permalink bug on form and widget

= 1.9.5 =
* Fixed bug where wp_editor() was showing an error if it is not present

= 1.9.4.3 =
* Added Spanish and Dutch .po files 

= 1.9.4 =
* Removed htmlentities function from the input
* Added I18n to the main text

= 1.9.3 =
* Added TinyMce to the Job Posting Description textarea

= 1.9.2 =
* Added the page "Input Fields" to the Admin
* Improved scripting for error reporting
* Admin can select which fields to show
* Admin can select whcih fields are required

= 1.8.8 =
* Fixed the link for the "View/Edit" Submission.

= 1.8.7 =
* Fixed the use of TinyMce.
* Allows the admin to enable/disable TinyMce on the Resume Form.

= 1.8.5 =
* Changed queries to comply with the Wordpress standards.
* Links grab the dynamic url instead of it being hard-coded.
* Cleaned up some bugs and typos.


== Upgrade Notice ==
= 1.9.2 =
The Admin can now select which input fields are shown and which ones are required

= 1.8.7 =
Admin is now able to enable/disable TinyMce on the Resume Form.

= 1.8.5 =
This upgrade meets the standards of the Wordpress plugin development.