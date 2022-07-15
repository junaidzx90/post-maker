<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.fiverr.com/junaidzx90
 * @since      1.0.0
 *
 * @package    Post_Maker
 * @subpackage Post_Maker/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Post_Maker
 * @subpackage Post_Maker/includes
 * @author     Developer Junayed <admin@easeare.com>
 */
class Post_Maker {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Post_Maker_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'POST_MAKER_VERSION' ) ) {
			$this->version = POST_MAKER_VERSION;
		} else {
			$this->version = '1.0.7';
		}
		$this->plugin_name = 'post-maker';

		add_shortcode( 'insert-box', [$this, 'pm_list_view'] );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		add_action( "admin_menu", [$this, "admin_menu_page"] );
        add_action( "admin_enqueue_scripts", [$this, "__admin_enqueue_scripts"] );

        add_action( "wp_ajax_pm_create_post", [$this, "pm_create_post"] );
        add_action( "wp_ajax_nopriv_pm_create_post", [$this, "pm_create_post"] );
	}

	function __admin_enqueue_scripts(){
        if(isset($_GET['page']) && $_GET['page'] === 'post-maker'){
			wp_enqueue_style( 'pm_selectize', plugin_dir_url( __FILE__ )."css/selectize.min.css",array(), POST_MAKER_VERSION, 'all' );
			wp_enqueue_style( 'post-maker-admin', plugin_dir_url( __FILE__ )."css/post-maker-admin.css",array(), POST_MAKER_VERSION, 'all' );
			
            wp_enqueue_media();
			wp_enqueue_editor();
			wp_enqueue_script( 'jquery.form', plugin_dir_url( __FILE__ ).'js/jquery.form.min.js', array(), POST_MAKER_VERSION, false );
			wp_enqueue_script( 'pm_selectize', plugin_dir_url( __FILE__ ).'js/selectize.min.js', array(), POST_MAKER_VERSION, false );
            wp_enqueue_script( 'post-maker', plugin_dir_url( __FILE__ ).'js/post-maker-admin.js', array('jquery', 'pm_selectize', 'jquery.form'), POST_MAKER_VERSION, true );
			wp_localize_script( 'post-maker', 'pm_ajax', array(
				'ajaxurl' => admin_url("admin-ajax.php")
			) );
        }
    }

	function admin_menu_page(){
		add_submenu_page( "edit.php", "Post maker", "Post maker", "manage_options", "post-maker", [$this, "setting_page"], null );
        add_settings_section( 'pm_general_opt_section', '', '', 'pm_general_opt_page' );
            
        // Post title
        add_settings_field( 'pm_post_title', 'Default title', [$this, 'pm_post_title_cb'], 'pm_general_opt_page','pm_general_opt_section' );
        register_setting( 'pm_general_opt_section', 'pm_post_title' );
        // Post contents
        add_settings_field( 'pm_post_contents', 'Default contents', [$this, 'pm_post_contents_cb'], 'pm_general_opt_page','pm_general_opt_section' );
        register_setting( 'pm_general_opt_section', 'pm_post_contents' );
        // Shortcodes
        add_settings_field( 'pm_post_shortcodes', 'Shortcodes', [$this, 'pm_post_shortcodes_cb'], 'pm_general_opt_page','pm_general_opt_section' );
        // register_setting( 'pm_general_opt_section', 'pm_post_shortcodes' );
        // Default thumbnail
        add_settings_field( 'pm_default_thumbnail', 'Default thumbnail', [$this, 'pm_default_thumbnail_cb'], 'pm_general_opt_page','pm_general_opt_section' );
        register_setting( 'pm_general_opt_section', 'pm_default_thumbnail' );
        // Default tags
        add_settings_field( 'pm_default_tags', 'Default tags', [$this, 'pm_default_tags_cb'], 'pm_general_opt_page','pm_general_opt_section' );
        register_setting( 'pm_general_opt_section', 'pm_default_tags' );
        // Default category
        add_settings_field( 'pm_default_category', 'Default category', [$this, 'pm_default_category_cb'], 'pm_general_opt_page','pm_general_opt_section' );
        register_setting( 'pm_general_opt_section', 'pm_default_category' );
        // Default author
        add_settings_field( 'pm_default_author', 'Default author', [$this, 'pm_default_author_cb'], 'pm_general_opt_page','pm_general_opt_section' );
        register_setting( 'pm_general_opt_section', 'pm_default_author' );
       
	}

	function pm_post_title_cb(){
        echo '<input type="text" name="pm_post_title" class="widefat" value="'.get_option('pm_post_title').'">';
    }
    function pm_post_contents_cb(){
		echo '<div class="pm__default_templates">';
		$contents = get_option('pm_post_contents');
		if(is_array($contents) && sizeof($contents) > 0){
			foreach($contents as $ind => $content){
				echo '<div class="pm__template_content">';
				wp_editor( wpautop( $content, true ), 'pm_post_contents_'.$ind, [
					'media_buttons' => true,
					'editor_height' => 100,
					'textarea_name' => 'pm_post_contents[]',
					'tinymce' => [
						'toolbar1' => 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
						'toolbar2' => 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help'
					]
				] );
				if($ind > 0){
					echo '<span class="pm_remove_template">+</span>';
				}
				echo '</div>';	
			}
		}else{
			echo '<div class="pm__template_content">';
			wp_editor( '', 'pm_post_contents_1', [
				'media_buttons' => true,
				'editor_height' => 100,
				'textarea_name' => 'pm_post_contents[]'
			] );
			echo '</div>';	
		}
		
		echo '</div>';
		echo '<button class="button-secondary add_new_template">Add template</button>';
    }

	function pm_post_shortcodes_cb(){
		echo '<div id="pm_shortcodes">';

		echo '<div class="shortcode_contents">';
		echo '<input data-id="1" class="pmshortcode" name="shortcodeFile[1]" type="file">';
		echo '<code>[pm-keyword-1]</code>';
		echo '</div>';
		
		echo '</div>';
		echo '<button class="button-secondary" id="pm_add_shortcode">Add shortcode</button>';
	}

    function pm_default_thumbnail_cb(){
        $attachment_url = '';
        if(get_option('pm_default_thumbnail')){
            $attachment_url = wp_get_attachment_url( get_option('pm_default_thumbnail') );
        }
        
        echo '<div class="pm_thumbnail_preview">';
        if($attachment_url){
            echo '<img style="width: 190px; margin: 10px 0; border: 2px solid #ddd; border-radius: 5px;" src="'.$attachment_url.'">';
        }
        echo '</div>';
        echo '<button id="pm_upload_thumbnail" class="button-secondary">Upload a thumbnail</button>';
        if($attachment_url){
            echo '&nbsp;&nbsp;<button style="border-color: red; color: red; box-shadow:none;" id="pm_remove_thumbnail" class="button-secondary">Remove</button>'; 
        }
        echo '<input type="hidden" name="pm_default_thumbnail" id="pm_default_thumbnail" class="widefat" value="'.get_option('pm_default_thumbnail').'">';
    }

	function pm_default_tags_cb(){
		$pm_tags = get_option('pm_default_tags');
		echo '<select id="pm_default_tags" multiple name="pm_default_tags[]" class="pm_default_tags">';
        echo '<option value="">Select</option>';
        
        $tags = get_tags(array(
            'hide_empty' => false
        ));

        foreach ($tags as $tag) {
            echo '<option '.( (is_array($pm_tags) && in_array($tag->name, $pm_tags)) ? 'selected': '').' value="'.$tag->name.'">'.$tag->name.'</option>';
        }
        
        echo '</select>';
	}

	function pm_default_category_cb(){
		$pm_categories = get_option('pm_default_category');
		echo '<select name="pm_default_category[]" id="pm_default_category" multiple class="pm_default_category">';
        echo '<option value="">Select</option>';
        
        $cats = get_categories(array(
            'hide_empty' => false
        ));

        foreach ($cats as $cat) {
            echo '<option '.((is_array($pm_categories) && in_array($cat->term_id, $pm_categories)) ? 'selected': '').' value="'.$cat->term_id.'">'.$cat->name.'</option>';
        }
        
        echo '</select>';
	}

	function pm_default_author_cb(){
		$pm_author = get_option('pm_default_author');
		echo '<select id="pm_default_author" name="pm_default_author" class="pm_default_author">';
        echo '<option value="">Select</option>';
        
        $users = get_users();
        if(is_array($users)){
            foreach($users as $user){
                echo '<option '.((intval($pm_author) === $user->ID) ? 'selected' : '').' value="'.$user->ID.'">'.$user->display_name.'</option>';
            }
        }
        
        echo '</select>';
	}
    
    function pm_create_post(){
		global $wpdb;
		
		$files = [];
		if(isset($_FILES['shortcodeFile'])){
			if(is_array($_FILES['shortcodeFile'])){
				$file = $_FILES['shortcodeFile'];

				if(array_key_exists("name", $file)){
					foreach($file['name'] as $key => $name){
						$files[$key]['name'] = $name;
					}
				}
				if(array_key_exists("type", $file)){
					foreach($file['type'] as $key => $type){
						$files[$key]['type'] = $type;
					}
				}
				if(array_key_exists("tmp_name", $file)){
					foreach($file['tmp_name'] as $key => $tmp_name){
						$files[$key]['tmp_name'] = $tmp_name;
					}
				}
			}
		}

		$dataArr = [];
		if(sizeof($files)>0){
			foreach($files as $key => $file){
				$keywords = [];

				$fileName = $file['name'];
				$file_data = $file['tmp_name'];
				
				$open = fopen($file_data, "r");
				while (($data = fgetcsv($open, 1000, ",")) !== FALSE){
					$keywords[] = [
						'shortcode' => $key,
						'keyword' => iconv("CP1251", "UTF-8", $data[0])
					];
				}

				$dataArr[] = $keywords;
				fclose($open);
			}
		}

		$finalData = [];
		foreach($dataArr as $darr){
			foreach($darr as $k => $rr){
				$finalData[$k][] = $rr;
			}
		}

		$contents = [];
		if(isset($_POST['pm_post_contents'])){
			$contents = $_POST['pm_post_contents'];
		}

		$title = '';
		if(isset($_POST['pm_post_title'])){
			$title = $_POST['pm_post_title'];
		}

		$thumbnail = null;
		if(isset($_POST['pm_default_thumbnail'])){
			$thumbnail = intval( $_POST['pm_default_thumbnail'] );
		}

		$tags = [];
		if(isset($_POST['pm_default_tags'])){
			$tags = $_POST['pm_default_tags'];
		}

		$categories = [];
		if(isset($_POST['pm_default_category'])){
			$categories = $_POST['pm_default_category'];
		}

		$author = null;
		if(isset($_POST['pm_default_author'])){
			$author = intval($_POST['pm_default_author']);
		}

		$created_urls = [];
		foreach($finalData as $key => $post){
			// Content ready with shortcode
			$final_contents = '';
			if(is_array($contents) && sizeof($contents) > 0){ // Random select template
				shuffle($contents);
				$contentIndex = array_rand($contents, 1);
				$final_contents = $contents[$contentIndex];
			}

			$shortcodecontents = [];
			$checktitle = false;
			$fcontents = $final_contents;
			$contentTitle = $title;

			foreach($post as $p){
				$shc = $p['shortcode'];
				$keyword = $p['keyword'];

				if(preg_match_all("[pm-keyword-".$shc."]", $fcontents, $matched)){
					if(sizeof($matched) > 0){
						$shortcodecontents[] = [
							'shortcode_id' => $shc,
							'text' => stripslashes( $keyword )
						];
					}else{
						$checktitle = true;
					}
				}

				if($checktitle){
					if(preg_match_all("[pm-keyword-".$shc."]", $title, $matched)){
						if(sizeof($matched) > 0){
							$shortcodecontents[] = [
								'shortcode_id' => $shc,
								'text' => stripslashes( $keyword )
							];
						}
					}
				}
				
				$fcontents = str_replace("[pm-keyword-".$shc."]", stripslashes( $keyword ), $fcontents);
				$fcontents = stripslashes( $fcontents );
				$contentTitle = str_replace("[pm-keyword-".$shc."]", stripslashes( $keyword ), $contentTitle);
			}

			$posttitle = wp_strip_all_tags( $contentTitle );
			$lowerTitle = strtolower($posttitle);
			$pstatus = 'publish';
			if($wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE LOWER(post_title) LIKE '%$lowerTitle%'")){
				$pstatus = 'draft';
			}

			// Post will create from here
            $args = array(
                'post_title'    => $posttitle,
                'post_content'  => $fcontents,
                'post_status'   => $pstatus,
                'post_author'   => (($author) ? $author : 1)
            );

            if(is_array($categories) && sizeof($categories) > 0){
                $args['post_category'] = $categories;
            }

            // Insert the post into the database
            $post_id = wp_insert_post( $args );

			if(!is_wp_error( $post_id )){
				if(is_array($shortcodecontents)){
					foreach($shortcodecontents as $shortcodecontent){
						$wpdb->insert($wpdb->prefix.'postmaker_keywords', array(
							'shortcode' => $shortcodecontent['shortcode_id'],
							'post_id' => $post_id,
							'keyword' => $shortcodecontent['text']
						));
					}
				}
	
				if($thumbnail){
					set_post_thumbnail( $post_id, $thumbnail );
				}
	
				if(is_array($tags)){
					wp_set_post_tags( $post_id, $tags );
				}

				$created_urls[] = get_the_permalink( $post_id );
			}
		}

		echo json_encode(array("success" => $created_urls));
		die;
    }

	// List view
	function pm_list_view(){
		ob_start();
		global $wpdb;
		$keywordsArr = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmaker_keywords");

		if(sizeof($keywordsArr) > 0){
			echo '<ul>';
			foreach($keywordsArr as $keywords){
				$post_id = $keywords->post_id;
				echo '<li><a target="_blank" href="'.get_the_permalink( $post_id ).'">'.$keywords->keyword.'</a></li>';
			}
			echo '</ul>';
		}

		return ob_get_clean();
	}
	
	function setting_page(){
		require_once plugin_dir_path( __FILE__ )."partials/tab-contents.php";
	}

}
