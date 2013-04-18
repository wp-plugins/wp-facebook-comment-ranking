<?php
/*
Plugin Name: WP Facebook Comment Ranking
Plugin URI: https://github.com/Mankin/wp-fb-comment-ranking
Description: facebookのコメント数に応じた、ブログ記事のランキングを生成します。
Author: Taishi Kato
Version: 1.0
Author URI: http://taishikato.com/blog/
*/

$wpcrank = new WpCommentRanking();

class WpCommentRanking {

  public function __construct () {
    if (function_exists('register_activation_hook')) {
      // When This Plugin Become Valid
      register_activation_hook(__FILE__, array(&$this, 'set_commentcount_meta'));
    }
    if (function_exists('register_deactivation_hook')) {
      // When This Plugin Become Invalid
      register_deactivation_hook(__FILE__, array(&$this, 'delete_commentcount_meta'));
    }
  
    add_action( 'transition_post_status', array(&$this, 'add_userblog_commentmeta'), 10, 3 );
    
    //set Hook
    if ( !wp_next_scheduled( 'wp_fb_comment_ranking_event' ) ) {
      $WpFbCommentRankingUpdateFrequency = get_option ('wp_fb_comment_ranking_frequency');
      wp_schedule_event(time(), $WpFbCommentRankingUpdateFrequency, 'wp_fb_comment_ranking_event');
    }
    // add action
    add_action('wp_fb_comment_ranking_event', array(&$this, 'update_fb_comment'));
  }

  function add_userblog_commentmeta ( $new_status, $old_status, $post ) {
    if ($new_status == 'publish' AND $old_status != 'publish') {
      global $post;
      add_post_meta($post->ID, 'wp_fb_comment_count', 0, true);
    }
  }

  function set_commentcount_meta () {  
    // プラグインを有効にしたときの処理を書く
    // Set the options
    update_option ('wp_fb_comment_ranking_frequency', 'hourly');
    update_option ('wp_fb_comment_ranking_updatePostNumber', 'all'); 
    // Search All Of The Posts
    $lastposts = get_posts('numberposts=-1');
    foreach($lastposts as $post) {
      setup_postdata($post);
      // get the ID
      $postId = $post->ID;
      // get the permalink
      $permalink = get_permalink($postId);
      $xml = 'http://api.facebook.com/method/fql.query?query=select%20commentsbox_count%20from%20link_stat%20where%20url=%22'.$permalink.'%22';
      $result = file_get_contents ($xml);
      $result = simplexml_load_string ($result);
      $commentNumber = $result->link_stat->commentsbox_count;
      $commentNumber = (int) $commentNumber;
      // Add Meta Data
      add_post_meta($postId, 'wp_fb_comment_count', $commentNumber, true);
    }
  }

  function delete_commentcount_meta () {  
    // プラグインを無効にしたときの処理を書く 
    // Search All Of The Posts
    $lastposts = get_posts('numberposts=-1');
    foreach($lastposts as $post) {
      setup_postdata($post);
      // get the ID
      $postId = $post->ID;

      // Delete Meta Data
      delete_post_meta($postId, 'wp_fb_comment_count');
    }
  }

  function update_fb_comment () {
    $WpFbCommentRankingUpdatePostNumber = get_option ('wp_fb_comment_ranking_updatePostNumber');
    if ($WpFbCommentRankingUpdatePostNumber == 'all') {
      $lastposts = get_posts('numberposts=0&post_type=post&post_status=');
    } else {
      $lastposts = get_posts('numberposts='.$WpFbCommentRankingUpdatePostNumber.'&orderby=post_date&order=DESC');
    }
    foreach($lastposts as $post) {
      setup_postdata($post);
      // get the ID
      $postId = $post->ID;
      // get the permalink
      $permalink = get_permalink($postId);
      // get the number of like
      $xml = 'http://api.facebook.com/method/fql.query?query=select%20commentsbox_count%20from%20link_stat%20where%20url=%22'.$permalink.'%22';
      $result = file_get_contents ($xml);
      $result = simplexml_load_string ($result);
      $likeNumber = $result->link_stat->commentsbox_count;
      $commentNumber = (int) $commentNumber;

      $preCommentNumber = get_post_meta($postId, 'wp_fb_comment_count', true);

      if( $preCommentNumber != $commentNumber ) {
        update_post_meta($postId, 'wp_fb_comment_count', $commentNumber, $preCommentNumber);
      }
    }
  }
}

add_action('admin_menu', 'wp_fb_like_ranking_admin_menu');
function wp_fb_comment_ranking_admin_menu () {
  add_options_page('WP Facebook Comment Ranking', 'WP Facebook Comment Ranking', 8, __FILE__, 'wp_fb_comment_ranking_edit_setting');
}

// 管理画面設定
function wp_fb_comment_ranking_edit_setting () {
  if (isset($_POST['wp_fb_comment_ranking_frequency'])) {
    update_option ('wp_fb_comment_ranking_frequency', $_POST['wp_fb_comment_ranking_frequency']);
  }
  if (isset($_POST['wp_fb_comment_ranking_updatePostNumber'])) {
    update_option ('wp_fb_comment_ranking_updatePostNumber', $_POST['wp_fb_comment_ranking_updatePostNumber']);
  }
  $WpFbLikeRankingFrequency = get_option ('wp_fb_comment_ranking_frequency');
  $WpFbCommentRankingUpdatePostNumber = get_option ('wp_fb_comment_ranking_updatePostNumber');
  include 'setting.html.php';
}

function get_comment_ranking ($number = 5, $comment_count = true, $thumbnail_size = null) {
  $number = esc_html($number);
  $rank = get_posts('meta_key=wp_fb_comment_count&numberposts='.$number.'&orderby=meta_value_num');
  echo '<ul class="wp-fb-comment-ranking">';
  $i = 0;
  foreach($rank as $post) {
    $commentNumberToPost = get_post_meta($post->ID, 'wp_fb_comment_count', true);
    if ($commentNumberToPost != 0) {
      $i++;
      if ($comment_count == true) {
        if ($thumbnail_size == null) {
          echo '<li><a href="'.$post->guid.'">'.esc_html($post->post_title).'</a> <span class="wp-fb-comment-ranking-count">'.$commentNumberToPost.'</span></li>';
        } else {
          echo '<li><a href="'.$post->guid.'">'.get_the_post_thumbnail( $post->ID, $thumbnail_size ).esc_html($post->post_title).'</a> <span class="wp-fb-comment-ranking-count">'.$commentNumberToPost.'</span></li>';
        }
      } else {
        if ($thumbnail_size == null) {
          echo '<li><a href="'.$post->guid.'">'.esc_html($post->post_title).'</a></li>';
        } else {
          echo '<li><a href="'.$post->guid.'">'.get_the_post_thumbnail( $post->ID, $thumbnail_size ).esc_html($post->post_title).'</a></li>';
        }
      }
    }
  }
  if ($i == 0) echo 'コメントがある記事はまだありません';
  echo '</ul>';
  wp_reset_query();
}
