=== WordPress Facebook Comment Ranking ===
Contributors: MankinJp
Donate link: 
Tags: facebook, comment, ranking, popular, plugin
Requires at least: 2.8
Tested up to: 3.5.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

With this plugin, you can use a your posts' ranking rated by the number of Facebook comment.

== Description ==

= Features =

* **Making a ranking rated by the number of Facebook comment**. You can make and use a ranking rated by the number of Facebook comment width this plugin. It's good for visitors.

* **Setting in detail**. You can decide how often it will update the ranking information and how many posts it will check when it updates the information.

== Installation ==

1. Download the plugin and extract its contents. / プラグインをダウンロード、解凍します。
2. Upload `wp-facebook-comment-ranking` to the `/wp-content/plugins/` directory. / `/wp-content/plugins/`ディレクトリにプラグインをアップロードします。
3. Activate the plugin through the 'Plugins' menu in WordPress. / プラグインを有効化します。
4. Configure items through the 'Settings->WP Facebook Comment Ranking' menu in WordPres. / 'Settings->WP Facebook Comment Ranking'で細かい設定を行います。
5. Place `<?php if (function_exists('get_comment_ranking')) get_comment_ranking (); ?>` in your templates. / `<?php if (function_exists('get_comment_ranking')) get_comment_ranking (); ?>`を、ランキングを出したいところにコピペします。
That's it!

・more about function

`get_comment_ranking (int $post_number = 5, bool $post_count = true, array $thumbnail_size = null)`

ex)

`get_comment_ranking (10, false, array(20, 20))`

It shows 10 posts and 20px × 20px thumbnail picture without expressing like count.


== Frequently asked questions ==

= I need help with your plugin! What should I do? =

 If you're having problems with the plugin, my suggestion would be try disabling all other plugins.

== Screenshots ==

1. WordPress Facebook Comment Ranking on theme's sidebar.
2. WordPress Facebook Comment Ranking Stats panel.


== Changelog ==

= 1.0 =
* Public release

== Upgrade notice ==

You better use Wordpress 2.8 at least.
