Plugin: Keywords 1.5
URL: http://stuchl4n3k.net/keywords-plugin
Plugin Author: Petr Stuchlik - http://stuchl4n3k.net
Licenses:  Keywords : Apache Software License 2.0

DESCRIPTION
-----------

Keywords is a simple, yet effective Habari plugin which allows you to add HTML Meta tags Keywords and Desctription for each post you create.

The plugin is inspired by MetaSEO plugin by Habari Community, which was not compatible with the 0.7 Habari version when I was building my first Habari site.


INSTALLATION
------------

 1. Download the archive to your server
 2. Extract the contents to a temporary location (not strictly necessary, but just being safe)
 3. Move the keywords directory to /path/to/your/habari/user/plugins/
 4. Add following tags in your theme's header.php template inside the <head>...</head> tag:

    <meta name="keywords" content="your, default, keywords">
    <meta name="description" content="Your default description.">

 5. Refresh your plugins page and activate the plugin
 6. In each post you create, fill in the 'Meta' section


UPGRADE
-------

The upgrade procedure is as per the installation procedure.


HOW IT WORKS
------------

 * Keywords plugin checks, if there are any keywords and description filled in for currently displayed post.
 * It replaces your default meta keywords and meta description tag contents with the values you filled in.
 * If no keywords or description is filled or if you're not currently viewing any post, no replacing is preformed and default meta tags in your template are displayed.



REVISION HISTORY
----------------

1.5     - Compatibility with Habari 0.9.1.
1.4     - Compatibility with Habari 0.8, code cleanup.
1.3     - Fix for post preview warning, when no keywords were set, but some tags were.
1.2     - Bug fixes
1.1     - Some code cleanup, GUID support was added.
1.0     - Initial release


If you encounter any problems, please feel free to leave a comment on the post that relates to the release.