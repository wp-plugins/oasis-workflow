=== Oasis Workflow ===
Contributors: nuggetsol
Tags: workflow, work flow, review, assignment, publish, inbox, workflow history, audit
Requires at least: 3.6
Tested up to: 3.8
Stable tag: 1.0.8

Workflow process for WordPress made simple with Oasis Workflow.

== Description ==

Any online publishing organization has one or several Managing Editors responsible for keeping the arrangement of editorial content flowing in an organized fashion.

Oasis Workflow plugin is designed to automate any workflow process using a simple, intuitive graphical user interface (GUI).

The plugin provides three processes:

1. Assignment - represents task related to content generation.

2. Review - represents task related to content review.

3. Publish - represents the actual "publish" task.

**Visual Work flow Designer**
 - Configure your work flow using the easy drag and drop designer interface. See screen shots for more detail.

**Role-Based routing definitions allow you to assign tasks dynamically**
 - By using role-based routing, you can ensure that your process moves forward as quickly as possible without sacrificing accountability.

**Inbox**
 - Users can view their current assignments and sign off their tasks once it's completed.

**Process history lets users retrace their steps**
 - For auditing purposes a record is maintained of all posts that are routed through a workflow process. The process history also captures the comments added by the user when they signed off the particular task.

**Reassign - How to pass the buck?**
 - What if you have been assigned a workflow task, but you feel you are not the appropriate person to complete it? No worry, you can assign the task to another person. 

**Due Date and Email reminders** help you to publish your articles on time.

**Out of the box workflow**
To get you started, the plugin comes with an out of the box workflow. You can also modify the workflow to suit your needs. 

You can find the complete list of features on the [support](http://oasisworkflow.com) site. 
**If you are looking for additional functionality, check out our "Pro" version - Oasis Workflow Pro: http://www.oasisworkflow.com/pricing-purchase**

If you need help setting up the roles, we recommend the [User Role Editor plugin](http://wordpress.org/extend/plugins/user-role-editor/ "User Role Editor plugin").

Videos to help you get started with Oasis Workflow:

[youtube http://www.youtube.com/watch?v=PPBJns2p-zU]

[youtube http://www.youtube.com/watch?v=SuOCBf_mLpc]

== Installation ==

1. Download the plugin zip file to your desktop
2. Upload the plugin to WordPress
3. Activate Oasis Workflow by going to Workflow Admin --> Settings
4. You are now ready to use Oasis Workflow! Build Your Workflow and start managing your editorial content flow.

== Frequently Asked Questions ==

For [Frequently Asked Questions](http://oasisworkflow.com/faq) plus documentation, plugin help, go [here](http://oasisworkflow.com)

== Screenshots ==

1. Visual Work flow designer
2. Role-based routing
3. Inbox
4. Sign off
5. Process history


== Changelog ==

= Version 1.0.0 =

Initial version

= Version 1.0.1 =
* Added Multisite capability.
* Admin can now view another user's inbox and signoff on behave of other users.
* Bug fixes.

= Version 1.0.2 =
* Made WP 3.5 compatible

= Version 1.0.3 =
* Added an option for admin to detach the post from oasis workflow and go back to normal wordpress behavior.
* Added reminder email AFTER certain due date feature.
* Change the post title placeholder to be a link.
* Bug fixes.

= Version 1.0.4 =
* Made the assignment step a multi-user step, where multiple users can be assigned the work however only one can claim it.
* Configuration - Roles who are allowed to publish post without going through a workflow.
* Set "publish" as the success step for the publish step.
* Bug fixes.

= Version 1.0.5 =
* Multi site enhancements. Moved the Workflow Admin to Network Admin, so workflows can be shared between all the sites.
* No need to duplicate the workflows for new sites inside a multi site environment.
* Note: 
* 1. Workflows previously created in sub sites except the main site will NOT be available anymore. 
If these workflows are different, they need to be recreated with this upgrade.
* 2. Make sure to complete all the existing workflows for sub sites, to avoid any unexpected behavior. 

= Version 1.0.6 =
* Internationalization(I18N) and localization (L10N) support added.
* Bug fixes.
* minor enhancements

= Version 1.0.7 =
* Bug fixes.
* minor enhancements

= Version 1.0.8 =
* Updated the Inbox menu to display the number of inbox items.
* The plugin will now come with an out of the box workflow when installed for the first time. This will help getting started with the plugin with little or no effort. Simply activate the workflow process from Workflow Admin --&gt; Settings page and you are ready to use the workflow.
* Auto select of user during the sign off process, if there is one and only one user for that given role.
* Due dates are not required/shown unless "reminder emails" are set to be required on the settings page.
* Added French translation files.
* Added Sign off button on the Posts page. This will help to sign off the post/page even when you are not in your inbox.
* Fixed issues related to IE compatibility. The plugin should function well in IE 9 and IE 10.
* Fixed issue with sign off caused due to the addition of  "take over" functionality by core Wordpress.
* We have removed the connection type from the connection settings popup. The plugin defaults to one specific connection type. You might see the workflow visual representation to be a bit awkward. All you have to do is to save the workflow and it will auto-correct the connections.

