<h3>Post maker settings</h3>
<hr>
<div class="pm_settings__tab">
<?php
    $current_tab = 'general';

    if(isset($_GET['page']) && $_GET['page'] === 'post-maker' && isset($_GET['tab'])){
        $tab = sanitize_text_field( $_GET['tab'] );

        switch ($tab) {
            case 'general':
                $current_tab = 'general';
                break;
            case 'keywords':
                $current_tab = 'keywords';
                break;
            default:
                $current_tab = 'general';
                break;
        }
    }
    ?>

    <a href="?page=post-maker&tab=general" class="pm_tablinks <?php echo (($current_tab === 'general') ? 'active': '') ?>">Post maker</a>
    <a href="?page=post-maker&tab=keywords" class="pm_tablinks <?php echo (($current_tab === 'keywords') ? 'active': '') ?>">Keywords</a>
</div>

<!-- Genral contents -->
<div id="general" class="pm_tabcontent <?php echo (($current_tab === 'general') ? 'active': '') ?>">
    <form method="post" style="width: 75%" action="options.php">
        <?php
        settings_fields( 'pm_general_opt_section' );
        do_settings_sections( 'pm_general_opt_page' );
        
        echo '<p>';
        echo '<input type="submit" name="save_changes" class="button-primary" value="Save changes">&nbsp;&nbsp;';
        echo '<input type="submit" name="create_new_post" id="create_new_post" class="button-primary" value="Create a post">';
        echo '</p>';
        ?>
    </form>	
</div>

<!-- Keywords contents -->
<div id="keywords" class="pm_tabcontent <?php echo (($current_tab === 'keywords') ? 'active': '') ?>">
    <?php
    global $wpdb;
    if(isset($_GET['delete']) && !empty($_GET['delete'])){
        if($_GET['delete'] !== "all"){
            $shortcode = intval($_GET['delete']);
            $wpdb->query("DELETE FROM {$wpdb->prefix}postmaker_keywords WHERE shortcode = $shortcode");
        }else{
            $wpdb->query("DELETE FROM {$wpdb->prefix}postmaker_keywords");
        }
        ob_start();
        wp_safe_redirect( admin_url( 'edit.php?page=post-maker&tab=keywords' ) );
        exit;
    }

    $keywordsArr = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmaker_keywords");
    if(sizeof($keywordsArr) > 0){
        $shData = [];
        foreach($keywordsArr as $record){
            $sh = $record->shortcode;
            $shData[$sh][] = [
                'post_id' => $record->post_id,
                'keyword' => $record->keyword
            ];
        }

        echo '<p>Use inside post or page to show this list: <code>[insert-box]</code></p>';
        echo '<ul>';
        foreach($shData as $sh => $keywords){
            ?>
            <fieldset>
            <legend>
                <div class="actionbx">
                    <code>[pm-keyword-<?php echo $sh ?>]</code>
                    <a class="pm_del" href="?page=post-maker&tab=keywords&delete=<?php echo $sh ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 1000 1000" enable-background="new 0 0 1000 1000" xml:space="preserve" width="15px" height="15px" fill="#fff"> <metadata> Svg Vector Icons : http://www.onlinewebfonts.com/icon </metadata> <g><path d="M321.8,277.3v534.6h89.1V277.3H321.8z M945.4,99.1H682.6l-49.2-49.2C631,27.4,612.1,10,589.1,10H410.9c-23,0-42,17.4-44.3,39.9l-49.2,49.2H54.6C29.9,99.1,10,119,10,143.7c0,24.6,19.9,44.6,44.6,44.6h265c13.3,7.3,26.4,8.3,33.5,1.3l90.4-90.4h113.2l90.4,90.4c7.1,7.1,20.2,6.1,33.5-1.3h265c24.6,0,44.6-20,44.6-44.6C990,119,970.1,99.1,945.4,99.1z M811.8,900.9H188.2V277.3H99.1v623.7c0,49.2,39.9,89.1,89.1,89.1h623.6c49.2,0,89.1-39.9,89.1-89.1V277.3h-89.1L811.8,900.9L811.8,900.9z M589.1,277.3v534.6h89.1V277.3H589.1z"/></g> </svg>
                        &nbsp;DELETE ALL
                    </a>
                </div>
            </legend>
            
            <?php
            if(is_array($keywords)){
                foreach($keywords as $key){
                    $post_id = $key['post_id'];
                    ?>
                    <li>
                        <a target="_blank" href="<?php echo get_the_permalink( $post_id ) ?>"><?php echo $key['keyword'] ?></a>
                    </li>
                    <?php
                }
            }
            echo '</fieldset>';
        }
        echo '</ul>';

        echo ' <a class="pm_del" href="?page=post-maker&tab=keywords&delete=all">
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 1000 1000" enable-background="new 0 0 1000 1000" xml:space="preserve" width="15px" height="15px" fill="#fff"> <metadata> Svg Vector Icons : http://www.onlinewebfonts.com/icon </metadata> <g><path d="M321.8,277.3v534.6h89.1V277.3H321.8z M945.4,99.1H682.6l-49.2-49.2C631,27.4,612.1,10,589.1,10H410.9c-23,0-42,17.4-44.3,39.9l-49.2,49.2H54.6C29.9,99.1,10,119,10,143.7c0,24.6,19.9,44.6,44.6,44.6h265c13.3,7.3,26.4,8.3,33.5,1.3l90.4-90.4h113.2l90.4,90.4c7.1,7.1,20.2,6.1,33.5-1.3h265c24.6,0,44.6-20,44.6-44.6C990,119,970.1,99.1,945.4,99.1z M811.8,900.9H188.2V277.3H99.1v623.7c0,49.2,39.9,89.1,89.1,89.1h623.6c49.2,0,89.1-39.9,89.1-89.1V277.3h-89.1L811.8,900.9L811.8,900.9z M589.1,277.3v534.6h89.1V277.3H589.1z"/></g> </svg>
        &nbsp;CLEAR ALL
        </a>';
    }else{
        echo "<p>No keywords found!</p>";
    }
    ?>
</div>