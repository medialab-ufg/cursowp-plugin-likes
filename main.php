<?php
/*
Plugin Name: Curso WP Plugin Likes
Plugin URI:
Description: Teste de plugin
Author: Jimmy Cliff
Version: 1.6
Tested up to:
*/

class CursoWPLikes {


  function __construct() {
    add_action('wp_print_scripts', array(&$this, 'print_scripts'));
    add_filter('the_content', array(&$this, 'the_content'));
    add_action('wp_ajax_cursowp_like', array(&$this, 'POST_vote'));
    add_action('wp_ajax_cursowp_unlike', array(&$this, 'POST_unvote'));
    add_action('admin_init', array(&$this, 'register_settings'));
    add_action('wp_enqueue_scripts', array(&$this, 'print_styles'));
  }

  function print_scripts() {
    if (is_admin())
      return;
    wp_enqueue_script('cursowp_like', plugin_dir_url( __FILE__ ) . 'assets/js/like.js', array('jquery'));
    wp_localize_script('cursowp_like', 'cursowp_like', array(
                                                             'ajaxurl' => admin_url('admin-ajax.php'),
                                                             'mensagem1' => __('Pages')
                                                             )
    );
  }

  function print_styles() {
    wp_register_style( 'cursowp_like', plugin_dir_url( __FILE__ ) . 'assets/css/style.css' );
    wp_enqueue_style( 'cursowp_like' );
  }

  function the_content($content) {
    global $post;
    $html = $this->get_like_html($post->ID);
    return $html . $content;
  }

  function get_like_html($post_id) {
    if (!is_user_logged_in())
      return '';

    $whereToDisplay = get_option('cursowp_like_enabled');

    if ( is_page() && $whereToDisplay != 'postspages' )
      return '';

    $current_user = wp_get_current_user();

    $author = get_post_field('post_author', get_the_ID());

    if ($current_user->ID == $author) {
      $isPostAuthor = 1;
    } else {
      $isPostAuthor = 0;
    }

    $likes = get_post_meta($post_id, '_user_like');
    $likeOptions = get_post_meta($post_id, '_can_like_checkbox');
    $podeCurtir = is_array($likeOptions) ? in_array('true', $likeOptions) : false;
    $totalLikes = is_array($likes) ? sizeof($likes) : 0;
    $jaCurtiu = is_array($likes) ? in_array($current_user->ID, $likes) : false;

    if ($isPostAuthor == 0 && $podeCurtir == true) {
      if (!$jaCurtiu) {
        $html = "<span class='cursowp_like' data-post_id='{$post_id}'>Curtir</span> | ";
      } else {
        $html = "<span class='cursowp_unlike' data-post_id='{$post_id}'>Descurtir</span> | ";
      }
    } elseif($isPostAuthor == 1 && $podeCurtir != true) {
      $html = "<div class='alert'>Opção de curtidas desabilitada</div>";
    } else {
      $html = "";
    }

    $s = $totalLikes != 1 ? 's' : '';

    if($podeCurtir == true) {
      $html .= "<span class='cursowp_like_count' data-post_id='{$post_id}' >$totalLikes curtida$s</span>";
    } else {
      $html .= "";
    }

    $html = "<div class='cursowp_like_wrapper' id='cursowp_like_{$post_id}'>$html<hr/></div>";

    return $html;

  }

  function POST_vote() {
    if (is_user_logged_in()) {
      $current_user = wp_get_current_user();
      if (is_numeric($_POST['post_id'])) {
        add_post_meta($_POST['post_id'], '_user_like', $current_user->ID);
        echo $this->get_like_html($_POST['post_id']);
      } else {
        echo 'erro';
      }
    }
    die;
  }

  function POST_unvote() {
    if (is_user_logged_in()) {
      $current_user = wp_get_current_user();
      if (is_numeric($_POST['post_id'])) {
        delete_post_meta($_POST['post_id'], '_user_like', $current_user->ID);
        echo $this->get_like_html($_POST['post_id']);
      } else {
        echo 'erro';
      }
    }
    die;
  }



  function register_settings() {

    register_setting('general', 'cursowp_like_enabled');

    add_settings_field(
                       'cursowp_like_enabled',
                       'Habilitar votação em:',
                       array(&$this, 'setting_field'),
                       'general'
                       );

  }

  function setting_field() {

    $value = get_option('cursowp_like_enabled');

    echo '<select name="cursowp_like_enabled">';

    echo '<option value="onlyposts" ' . selected($value, 'onlyposts', false) . '>Somente posts</option>';
    echo '<option value="postspages" ' . selected($value, 'postspages', false ) . '>Posts e páginas</option>';

    echo '</select>';

  }

}

add_action('init', function() {
  $cursoWP = new CursoWPLikes();
});


// EXIBIR OPÇÃO PARA DESATIVAR/ATIVAS CURTIDAS
function likes_meta_box_markup($object){
  wp_nonce_field(basename(__FILE__), "meta-box-likes");

  ?>
  <div>
    <label for="_can_like_checkbox">Permitir?</label>
    <?php
    $checkbox_value = get_post_meta($object->ID, "_can_like_checkbox", true);

    if($checkbox_value == "") {
      ?>
      <input name="_can_like_checkbox" type="checkbox" value="true">
      <?php
    }
    else if($checkbox_value == "true") {
      ?>
      <input name="_can_like_checkbox" type="checkbox" value="true" checked>
      <?php
    }
    ?>
  </div>
  <?php

}

function add_likes_meta_box() {
  add_meta_box("likes-meta-box", "Permissão de Curtidas", "likes_meta_box_markup", "post", "side", "high", null);
}

add_action("add_meta_boxes", "add_likes_meta_box");

// SALVANDO OPÇÃO NO ADMIN
function save_likes_meta_box($post_id, $post, $update) {
  if (!isset($_POST["meta-box-likes"]) || !wp_verify_nonce($_POST["meta-box-likes"], basename(__FILE__)))
    return $post_id;

  if(!current_user_can("edit_post", $post_id))
    return $post_id;

  if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
    return $post_id;

  $slug = "post";
  if($slug != $post->post_type)
    return $post_id;

  $meta_box_checkbox_value = "";


  if(isset($_POST["_can_like_checkbox"]))
  {
    $meta_box_checkbox_value = $_POST["_can_like_checkbox"];
  }
  update_post_meta($post_id, "_can_like_checkbox", $meta_box_checkbox_value);
}

add_action("save_post", "save_likes_meta_box", 10, 3);
