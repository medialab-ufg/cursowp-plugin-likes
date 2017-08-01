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
        
        add_action('admin_init', array(&$this, 'register_settings'));
        
    }
    
    function print_scripts() {
        if (is_admin())
            return;
            
        wp_enqueue_script('cursowp_like', plugin_dir_url( __FILE__ ) . '/like.js', array('jquery'));
        wp_localize_script('cursowp_like', 'cursowp_like', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'mensagem1' => __('Pages')
            )
        );
    
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
            
        $likes = get_post_meta($post_id, '_user_like');
        $totalLikes = is_array($likes) ? sizeof($likes) : 0;
        $jaCurtiu = is_array($likes) ? in_array($current_user->ID, $likes) : false;
        
        if (!$jaCurtiu) {
            $html = "<span class='cursowp_like' data-post_id='{$post_id}' >Curtir</span>";
        } else {
            $html = "<span  >Já curtiu</span>";
        }
        
        $s = $totalLikes != 1 ? 's' : '';
        
        $html .= " | <span class='cursowp_like_count' data-post_id='{$post_id}' >$totalLikes curtida$s</span>";
        
        $html = "<div class='cursowp_like_wrapper' id='cursowp_like_{$post_id}'>$html<hr/></div>";
        
        return $html;

    }
    
    function POST_vote() {

        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            if (is_numeric($post_id = $_POST['post_id'])) {

                $current_user = wp_get_current_user();
                $likes = get_post_meta($post_id, '_user_like');
                $jaCurtiu = is_array($likes) ? in_array($current_user->ID, $likes) : false;
                
                if($jaCurtiu){
                    delete_post_meta($_POST['post_id'], '_user_like', $current_user->ID);
                } else {
                    add_post_meta($_POST['post_id'], '_user_like', $current_user->ID);
                }
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


