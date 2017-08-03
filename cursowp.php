<?php
/*
Plugin Name: Curso WP Plugin Likes
Plugin URI: 
Description: Teste de plugin
Author: Jimmy Cliff
Version: 1.6
Tested up to: 
*/


class PostCurtir {
	const NAME = 'PostCurtirs';
    const MENU_NAME = 'PostCurtir';

    /**
     * alug do post type: deve conter somente minúscula 
     * @var string
     */
    protected static $post_type;

    static function init()
    {
        // o slug do post type
        self::$post_type = strtolower(__CLASS__);

        add_action('init', array(self::$post_type, 'register'), 0);

        //add_action( 'init', array(__CLASS__, 'register_taxonomies') ,10);
        //add_filter('menu_order', array(self::$post_type, 'change_menu_label'));
        //add_filter('custom_menu_order', array(self::$post_type, 'custom_menu_order'));
        //add_action('save_post',array(__CLASS__, 'on_save'));
    }

    static function register()
    {
        register_post_type(self::$post_type, array(
            'labels' => array(
                'name' => _x(self::NAME, 'post type general name', 'SLUG'),
                'singular_name' => _x('PostCurtir', 'post type singular name', 'SLUG'),
                'add_new' => _x('Adicionar Novo', 'image', 'SLUG'),
                'add_new_item' => __('Adicionar novo PostCurtir', 'SLUG'),
                'edit_item' => __('Editar PostCurtir', 'SLUG'),
                'new_item' => __('Novo PostCurtir', 'SLUG'),
                'view_item' => __('Ver PostCurtir', 'SLUG'),
                'search_items' => __('Search PostCurtirs', 'SLUG'),
                'not_found' => __('Nenhum PostCurtir Encontrado', 'SLUG'),
                'not_found_in_trash' => __('Nenhum PostCurtir na Lixeira', 'SLUG'),
                'parent_item_colon' => ''
            ),
            'public' => true,
            'rewrite' => array('slug' => 'PostCurtir'),
            'capability_type' => 'post',
            'hierarchical' => true,
            'map_meta_cap ' => true,
            'menu_position' => 6,
            'has_archive' => true, //se precisar de arquivo
            'supports' => array(
                'title',
                'editor',
                'page-attributes'
            ),
            //'taxonomies' => array('taxonomia')
            )
        );
    }

    static function register_taxonomies()
    {
        // se for usar, descomentar //'taxonomies' => array('taxonomia') do post type (logo acima)

        $labels = array(
            'name' => _x('Taxonomias', 'taxonomy general name', 'SLUG'),
            'singular_name' => _x('Taxonomia', 'taxonomy singular name', 'SLUG'),
            'search_items' => __('Search Taxonomias', 'SLUG'),
            'all_items' => __('All Taxonomias', 'SLUG'),
            'parent_item' => __('Parent Taxonomia', 'SLUG'),
            'parent_item_colon' => __('Parent Taxonomia:', 'SLUG'),
            'edit_item' => __('Edit Taxonomia', 'SLUG'),
            'update_item' => __('Update Taxonomia', 'SLUG'),
            'add_new_item' => __('Add New Taxonomia', 'SLUG'),
            'new_item_name' => __('New Taxonomia Name', 'SLUG'),
        );

        register_taxonomy('taxonomia', self::$post_type, array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => true,
            )
        );
    }

    static function change_menu_label($stuff)
    {
        global $menu, $submenu;
        foreach ($menu as $i => $mi) {
            if ($mi[0] == self::NAME) {
                $menu[$i][0] = self::MENU_NAME;
            }
        }
        return $stuff;
    }

    static function custom_menu_order()
    {
        return true;
    }

    /**
     * Chamado pelo hook save_post
     * @param int $post_id
     * @param object $post
     */
    static function on_save($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;

        global $post;

        if ($post->post_type == self::$post_type) {
            // faça algo com o post 
        }
    }
}

PostCurtir::init();

class CursoWPLikes {


    function __construct() {
		add_action('init', array(&$this,'replace_jquery'));
    
        add_action('wp_print_scripts', array(&$this, 'print_scripts'));
        add_filter('the_content', array(&$this, 'the_content'));
        add_action('wp_ajax_cursowp_like', array(&$this, 'POST_vote'));
        add_action('wp_ajax_cursowp_dislike', array(&$this, 'POST_unvote'));
        
        add_action('admin_init', array(&$this, 'register_settings'));
        
    }
    
    function replace_jquery() {
		if (!is_admin()) {
			// comment out the next two lines to load the local copy of jQuery
			wp_deregister_script('jquery');
			wp_register_script('jquery', 'http://code.jquery.com/jquery-3.2.1.min.js', false, '3.2.1');
			wp_enqueue_script('jquery');
		}
	}

    
    function print_scripts() {
        if (is_admin())
            return;
            
        wp_enqueue_script('cursowp_like', plugins_url( 'like.js'), array('jquery'));
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
            
        $permite_like = get_post_meta($post_id, '_permite_like')[0];
        
        if ( $permite_like == 'false' )
			return '';
        
        $current_user = wp_get_current_user();
        $idAutorPost = get_the_author_meta('ID');
            
        $likes = get_post_meta($post_id, '_user_like');
        $totalLikes = get_post_meta($post_id, '_total_likes')[0]; //is_array($likes) ? sizeof($likes) : 0;
        $jaCurtiu = is_array($likes) ? in_array($current_user->ID, $likes) : false;
        
        
        if ($current_user->ID != $idAutorPost) {
			if (!$jaCurtiu) {
				$html = "<span class='cursowp_like' data-post_id='{$post_id}' >Curtir</span> | ";
			} else {
				$html = "<span class='cursowp_dislike' data-post_id='{$post_id}' >Descurtir</span> | ";
			}
		}
        
        $s = $totalLikes != 1 ? 's' : '';
        
        $html .= "<span class='cursowp_like_count' data-post_id='{$post_id}' >$totalLikes curtida$s</span>";
        
        $html = "<div class='cursowp_like_wrapper' id='cursowp_like_{$post_id}'>$html<hr/></div>";
        
        return $html;

    }
    
    function POST_vote() {

        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $author_id = get_post_field( "post_author", $_POST['post_id']);
            if (is_numeric($_POST['post_id']) && $author_id != $current_user->ID) {
                add_post_meta($_POST['post_id'], '_user_like', $current_user->ID);
                $this->update_votes($_POST['post_id']);
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
                $this->update_votes($_POST['post_id']);
                echo $this->get_like_html($_POST['post_id']);
            } else {
                echo 'erro';
            }
        }
        
        die;

    }
    
    function update_votes($post_id) {
		$likes = get_post_meta($post_id, '_user_like');
		$totalLikes = is_array($likes) ? sizeof($likes) : 0;
		update_post_meta($post_id, '_total_likes', $totalLikes);
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



class _meta_likeMetabox {

    protected static $metabox_config = array(
        '_meta_like', // slug do metabox
        'Meta Like', // título do metabox
        array('post','page'), // array('post','page','etc'), // post types
        'side' // onde colocar o metabox: normal, side ou advanced
    );

    static function init() {
        add_action('add_meta_boxes', array(__CLASS__, 'addMetaBox'));
        add_action('save_post', array(__CLASS__, 'savePost'));
    }

    static function addMetaBox() {
        add_meta_box(
            self::$metabox_config[0],
            self::$metabox_config[1],
            array(__CLASS__, 'metabox'), 
            self::$metabox_config[2],
            self::$metabox_config[3]
            
        );
    }
    
    
    static function filterValue($meta_key, $value){
		global $post;
		return $value;
    }
    
    static function metabox(){
        global $post;
        
        wp_nonce_field( 'save_'.__CLASS__, __CLASS__.'_noncename' );
        
        $metadata = get_metadata('post', $post->ID);
        $value = $metadata['_permite_like'][0];
        ?>
        <label> Permite likes:
			<select name="<?php echo __CLASS__ ?>[_permite_like]">
				<option value="true" <?php selected($value, 'true') ?>>Permitir</option>
				<option value="false" <?php selected($value, 'false') ?>>Não Permitir</option>
			</select>
        </label>
        <?php
    }

    static function savePost($post_id) {
        // verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times

        if (!wp_verify_nonce($_POST[__CLASS__.'_noncename'], 'save_'.__CLASS__))
            return;


        // Check permissions
        if ('page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id))
                return;
        }
        else {
            if (!current_user_can('edit_post', $post_id))
                return;
        }

        // OK, we're authenticated: we need to find and save the data
        if(isset($_POST[__CLASS__])){
            foreach($_POST[__CLASS__] as $meta_key => $value)
                update_post_meta($post_id, $meta_key, self::filterValue($meta_key, $value));
        }
    }

    
}


_meta_likeMetabox::init();



class Lista_likes_widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'lista_likes_widget',
			'description' => 'Widget para listar posts ordenados por like',
		);
		parent::__construct( 'lista_likes_widget', 'Lista Likes Widget', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
	 
		//wp_reset_postdata();
		
		$nro_posts = $instance['nro_posts'];
		if ($nro_posts < 1) {
			$nro_posts = -1;
		}
		
		$query_args = array('posts_per_page' => $nro_posts, 'order' => 'DESC', 'orderby'   => 'meta_value_num',
		'meta_key'  => '_total_likes'
		);
		
		$query1 = new WP_Query( $query_args );

		if ( $query1->have_posts() ) {

			// The Loop
			echo '<ul>';
			while ( $query1->have_posts() ) {
				$query1->the_post();
				echo '<li><a href="' . get_permalink() .'">' . get_the_title() . ' | ' . get_post_meta(get_the_ID(), '_total_likes')[0] . ' curtidas</a></li>';
			}
			echo '</ul>';
			
			/* Restore original Post Data 
			 * NB: Because we are using new WP_Query we aren't stomping on the 
			 * original $wp_query and it does not need to be reset with 
			 * wp_reset_query(). We just need to set the post data back up with
			 * wp_reset_postdata().
			 */
			wp_reset_postdata();
		}
		
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$nro_posts = ! empty( $instance['nro_posts'] ) ? $instance['nro_posts'] : '0';
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'nro_posts' ) ); ?>">Número de posts a serem listados</label> 
		<input id="<?php echo esc_attr( $this->get_field_id( 'nro_posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'nro_posts' ) ); ?>" type="text" value="<?php echo esc_attr( $nro_posts ); ?>">
		</p>
		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['nro_posts'] = ( intval( $new_instance['nro_posts'] ) >= 0  ) ? $new_instance['nro_posts'] : intval( $old_instance['nro_posts'] );

		return $instance;
	}
}

add_action( 'widgets_init', function(){
	register_widget( 'Lista_likes_widget' );
});
