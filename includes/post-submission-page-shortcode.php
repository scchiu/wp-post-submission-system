<?php
/*
 * Create a shortcode [pss_shortcode] to create the submission system
 * Author：Steve
 * 
 * 
 */

//pss system

//create category 'article-submission'
//$my_cat = array('cat_name' => 'My Category', 'category_description' => 'A Cool Category', 'category_nicename' => 'category-slug', 'category_parent' => '');
/*
if(category_exists('article-submission')){
    echo 'yes';
}
 * 
 */

// Create the category, if not exits
/*
if(!term_exists('article-submission')){
    wp_insert_term(
        '投稿文章',
        'category',
        array(
            'description'	=> 'This is a category for article submission',
            'slug' 		=> 'article-submission')
    );   
}
*/
// Register post type SPaper
//require(dirname(__FILE__) . '/post-submission-page-post-type-creation.php');
//include js and css    
function post_submission_page_scripts() {
    //bootstrap    
    wp_enqueue_style( 'style-name1', plugins_url( '/assets/bootstrap-3.3.5-dist/css/bootstrap.min.css', __FILE__ ) );
    wp_enqueue_style( 'style-name2', plugins_url( '/assets/bootstrap-3.3.5-dist/css/bootstrap-theme.min.css', __FILE__ ) );
    //wp_enqueue_style( 'css-bootstrap-select', plugins_url( '/assets/css/bootstrap-select.css', __FILE__ ) );
    //wp_enqueue_style( 'css-edit-profile', plugins_url( '/assets/css/css-edit-profile.css', __FILE__ ) );
    
    wp_enqueue_script('jQuery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js');
    //wp_enqueue_script( 'password-strength-meter' );
    //wp_enqueue_script( 'check-password-strength', plugins_url( '/assets/js/check-password-strength.js', __FILE__ ),array('jquery'), '1.0.0',true );        
    wp_enqueue_script( 'custom-script1', plugins_url( '/assets/bootstrap-3.3.5-dist/js/bootstrap.min.js', __FILE__ ),array('jquery'), '1.0.0',true );    
    //wp_enqueue_script( 'script-bootstrap-select', plugins_url( '/assets/js/bootstrap-select.js', __FILE__ ),array('jquery'), '1.0.0',true );        
}
//add_action( 'wp_enqueue_scripts', 'post_submission_page_scripts' );

function pss_page_shortcode(){
?>

<div class="main-container">
    <div class="container">
        <h1><?php _e('Article Submission','post-submission-system');//文章投稿;?></h1>        

<!------------------ begin of post action --------------------->
<?php

$error = array();
// Check that the nonce is valid, and the user can edit this post.
if( isset($_POST['tougao_form']) && $_POST['tougao_form'] == 'send') {
    global $wpdb;
    $current_url = get_permalink();
    $last_post = $wpdb->get_var("SELECT `post_date` FROM `$wpdb->posts` ORDER BY `post_date` DESC LIMIT 1");

    // use cookie to check that time the interval of last two submissions should
    // be at least 120 seconds, making the system safer
    if ( (date_i18n('U') - strtotime($last_post)) < 120 ) {//modify the time interval here
        $error[] = __('The time interval of last submissions is too close. ', 'post-submission-system');        
    }
        
    // initialize the variables from the form
    $name = isset( $_POST['tougao_authorname'] ) ? trim(htmlspecialchars($_POST['tougao_authorname'], ENT_QUOTES)) : '';
    $email =  isset( $_POST['tougao_authoremail'] ) ? trim(htmlspecialchars($_POST['tougao_authoremail'], ENT_QUOTES)) : '';
    //$blog =  isset( $_POST['tougao_authorblog'] ) ? trim(htmlspecialchars($_POST['tougao_authorblog'], ENT_QUOTES)) : '';
    //$title =  isset( $_POST['tougao_title'] ) ? trim(htmlspecialchars($_POST['tougao_title'], ENT_QUOTES)) : '';
    //$category =  get_category_by_slug('article-submission')->term_id; //isset( $_POST['cat'] ) ? (int)$_POST['cat'] : 0; 
    //$content =  isset( $_POST['tougao_content'] ) ? trim(htmlspecialchars($_POST['tougao_content'], ENT_QUOTES)) : '';
    
    // examine the variables
    if ( empty($name) || mb_strlen($name) > 20 ) {
        //die('ID必须填写，且长度不得超过20字。<a href="'.$current_url.'">返回前一頁</a>');
        $error[] = __('the length of username should be less or equal to 20. ', 'post-submission-system');        
    }
    
    if ( empty($email) || strlen($email) > 60 || !preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) {
        //die('Email必须填写，且长度不得超过60字，必须符合Email格式。<a href="'.$current_url.'">返回前一頁</a>');
        $error[] = __('Email should be filled and meets Email format. ', 'post-submission-system');        
    }
    /*
    if ( empty($title) || mb_strlen($title) > 100 ) {
        wp_die('标题必须填写，且长度不得超过100字。<a href="'.$current_url.'">点此返回</a>');
    }
    
    if ( empty($content) || mb_strlen($content) > 3000 || mb_strlen($content) < 100) {
        wp_die('内容必须填写，且长度不得超过3000字，不得少于100字。<a href="'.$current_url.'">点此返回</a>');
    }
    */
    
    //check upload file
    if($_FILES['tougao_pdf']['name']==""){
        //die('請附加文章檔案。<a href="'.$current_url.'">返回前一頁</a>');
        //die(__('Please attach the article file. ', 'post-submission-system').'<a href="'.$current_url.'">'.__('Go back to Previous page', 'post-submission-system').'</a>');
        $error[] = __('Please attach the article file. ', 'post-submission-system');
    }
    if($_FILES['tougao_pdf']['name']!="" && $_FILES['tougao_pdf']['type']!="application/pdf"){
        //die('請附加文章PDF檔。<a href="'.$current_url.'">返回前一頁</a>');
        $error[] = __('Please attach PDF file format. ', 'post-submission-system');
    }
    if($_FILES['tougao_pdf']['size'] > 2000000){
        //die('文章檔案需小於2MB。<a href="'.$current_url.'">返回前一頁</a>');
        $error[] = __('File size should be less than 2 MB. ', 'post-submission-system');
    }    

    // check the attachment, get ready to upload    
    if(isset($_POST['tougao_pdf_nonce']) 
            && wp_verify_nonce($_POST['tougao_pdf_nonce'], 'tougao_pdf') 
            && current_user_can('edit_posts')//, $status)
            && count($error) == 0){
        
    /* begin of inserting post into database */
    $query_condition = array(
        'post_type' => 'SPaper',
        'posts_per_page' => -1,
        //'category_name' => 'article-submission',
        'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash')    
    );    
    $query_post = get_posts($query_condition);
    
    //debug for obtaining the lastest submission ID
    /*
    foreach($query_post as $post):setup_postdata($post);
    echo the_title().'<br />';
    endforeach;
    wp_reset_postdata();
    */
    
    $lastest_submission_id = count($query_post)+1;
    $title = 'Submission_'.$lastest_submission_id;
    
    $tougao = array(
        'post_title' => $title, 
        //'post_content' => $post_content,
        //'post_category' => array($category),
        'post_type' => 'SPaper',
        'post_status' => 'pending'
    );

    $status = wp_insert_post( $tougao );        
    /*  end of insert post */
    
    /* being of uploading file */
    
        //debug for uploading process
        /*
            echo 'yes! we can put the attachment now! <br />';    
            echo '$_POST[tougao_pdf_nonce]: ' . $_POST['tougao_pdf_nonce'] . '<br />';
            echo '$_POST[tougao_pdf]: '.$_POST['tougao_pdf'] . '<br />';
        */
        // These files need to be included as dependencies when on the front end.  
    
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );    

        // Let WordPress handle the upload.
        // Remember, 'tougao_pdf' is the name of our file input in our form above.    
        $attachment_id = media_handle_upload('tougao_pdf', $status );
    
        if(!is_wp_error($attachment_id)){
            //echo '<p class="bg-success" style="padding: 10px;">'.$attachment_id.__(' Article file upload successful! ','post-submission-system').'</p>';
    /* end of uploading file */
            
            ///update post with meta content
            $att_link = '<a href="'.wp_get_attachment_url($attachment_id).'">'.wp_get_attachment_url($attachment_id).'</a>';
            $post_content = 'Attachment URL: '.$att_link.'<br />Username: '.$name.'<br />Email: '.$email;
            wp_update_post(array('ID' => $status,'post_content' => $post_content));

            echo '<p class="alert alert-success" role="alert" style="">'.__('Submission successfully! ','post-submission-system').'</p>';
            
    /* send email to author */
            //wp_mail("somebody#example.com","My subject","content");
            
        }
        else{
            //debuge
            //echo 'upload failed! <br />';
        }    
        
                
    }
    else{
        //debuge
        //echo 'nonce/verification/editable failed! <br />';
    }
        
}
?>
<!--------------------------- end of action ----------------------> 

<!-- my custom page -->

        <?php if ( !is_user_logged_in() ) : ?>
            <?php echo '<p class="alert alert-info" role="alert" style="">'.
                __('Please log in to submit your article.', 'post-submission-system').'</p>'//請先登入，才可更新個人資訊！'; ?>
        <?php else : ?>
            <?php if ( count($error) > 0 ) echo '<p class="alert alert-warning" role="alert" style="">' . implode("<br />", $error) . '</p>'; ?>
            <?php $current_user = wp_get_current_user();?>

        <!-- begin of form -->
        <form class="ludou-tougao" method="post" action="<?php echo $_SERVER["REQUEST_URI"];  ?>" enctype="multipart/form-data">
            <div class="form-group" style="/*text-align: left; padding-top: 10px;*/">
                <label for="tougao_authorname"><?php _e('Username');//ID ?></label>
                <input class="form-control" type="text" size="40" value="<?php if ( 0 != $current_user->ID ) echo $current_user->user_login; ?>" id="tougao_authorname" name="tougao_authorname" readonly="readonly"/>
            </div>

            <div class="form-group" style="/*text-align: left; padding-top: 10px;*/">
                <label for="tougao_authoremail"><?php _e('Email');//E-Mail ?> </label>
                <input class="form-control" type="text" size="40" value="<?php if ( 0 != $current_user->ID ) echo $current_user->user_email; ?>" id="tougao_authoremail" name="tougao_authoremail" readonly="readonly"/>
            </div>
            <!--                
            <div style="text-align: left; padding-top: 10px;">
                <label for="tougao_authorblog">您的博客:</label>
                <input type="text" size="40" value="<?php //if ( 0 != $current_user->ID ) echo $current_user->user_url; ?>" id="tougao_authorblog" name="tougao_authorblog" />
            </div>

            <div style="text-align: left; padding-top: 10px;">
                <label for="tougao_title">文章标题:*</label>
                <input type="text" size="40" value="" id="tougao_title" name="tougao_title" />
            </div>

            <div class="form-group" style="text-align: left; padding-top: 10px; display: none;">
                <label for="tougaocategorg">分類 </label>
                <?php //wp_dropdown_categories('hide_empty=0&id=tougaocategorg&show_count=1&hierarchical=1'); ?>
            </div>

            <div style="text-align: left; padding-top: 10px;">
                <label style="vertical-align:top" for="tougao_content">文章内容:*</label>
                <textarea rows="15" cols="55" id="tougao_content" name="tougao_content"></textarea>
            </div>
            -->

            <div class="form-group" style="/*text-align: left; padding-top: 10px;*/">
                <label for="tougaoupload"><?php _e('File Upload (PDF, <2MB)', 'post-submission-system');//上傳文章 (PDF, 小於2MB)?> </label>
                <input class="form-control" type="file" id="tougao_pdf" name="tougao_pdf" />
                <?php wp_nonce_field('tougao_pdf', 'tougao_pdf_nonce'); ?>
            </div>    

            <br clear="all">
            <div style="/*text-align: center; padding-top: 10px;*/">
                <input type="hidden" value="send" name="tougao_form" />
                <input class="btn btn-default" type="submit" value="<?php _e('Submit', 'post-submission-system');//提交?>" name="submit"/>
                <!--<input type="reset" value="<?php //_e('Reset', 'post-submission-system');//重填?>" />-->
            </div>
        </form> 
        <!-- end of form -->   

        <?php endif; ?>
    <!-- end of my cumstom page -->                                    
    </div><!-- .entry-content -->
</div>

<?php
}
add_shortcode('pss_page_shortcode', 'pss_page_shortcode');




