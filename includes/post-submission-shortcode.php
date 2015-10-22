<?php
/*
 * Create a shortcode [pss_shortcode] to create the submission system
 * Author：Steve
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
if(!term_exists('article-submission')){
    wp_insert_term(
        '投稿文章',
        'category',
        array(
            'description'	=> 'This is a category for article submission',
            'slug' 		=> 'article-submission')
    );   
}

function pss_shortcode(){
?>
<!------------------ begin of post action --------------------->
<?php
// Check that the nonce is valid, and the user can edit this post.
if( isset($_POST['tougao_form']) && $_POST['tougao_form'] == 'send') {
    global $wpdb;
    $current_url = get_permalink();         //目前網址

    $last_post = $wpdb->get_var("SELECT `post_date` FROM `$wpdb->posts` ORDER BY `post_date` DESC LIMIT 1");

    // 博客当前最新文章发布时间与要投稿的文章至少间隔120秒。
    // 可自行修改时间间隔，修改下面代码中的120即可
    // 相比Cookie来验证两次投稿的时间差，读数据库的方式更加安全
    if ( (date_i18n('U') - strtotime($last_post)) < 1 ) {//修改時間間隔
        wp_die('與前次投稿相隔太近，請稍候再試！<a href="'.$current_url.'">返回前一頁</a>');
    }
        
    // 表单变量初始化
    $name = isset( $_POST['tougao_authorname'] ) ? trim(htmlspecialchars($_POST['tougao_authorname'], ENT_QUOTES)) : '';
    $email =  isset( $_POST['tougao_authoremail'] ) ? trim(htmlspecialchars($_POST['tougao_authoremail'], ENT_QUOTES)) : '';
    //$blog =  isset( $_POST['tougao_authorblog'] ) ? trim(htmlspecialchars($_POST['tougao_authorblog'], ENT_QUOTES)) : '';
    //$title =  isset( $_POST['tougao_title'] ) ? trim(htmlspecialchars($_POST['tougao_title'], ENT_QUOTES)) : '';
    $category =  get_category_by_slug('article-submission')->term_id; //isset( $_POST['cat'] ) ? (int)$_POST['cat'] : 0; 
    //$content =  isset( $_POST['tougao_content'] ) ? trim(htmlspecialchars($_POST['tougao_content'], ENT_QUOTES)) : '';
    
    // 表单项数据验证
    if ( empty($name) || mb_strlen($name) > 20 ) {
        die('ID必须填写，且长度不得超过20字。<a href="'.$current_url.'">返回前一頁</a>');
    }
    
    if ( empty($email) || strlen($email) > 60 || !preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) {
        die('Email必须填写，且长度不得超过60字，必须符合Email格式。<a href="'.$current_url.'">返回前一頁</a>');
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
        die('請附加文章檔案。<a href="'.$current_url.'">返回前一頁</a>');
    }
    if($_FILES['tougao_pdf']['type']!="application/pdf"){
        die('請附加文章PDF檔。<a href="'.$current_url.'">返回前一頁</a>');
    }
    if($_FILES['tougao_pdf']['size'] > 2000000){
        die('文章檔案需小於2MB。<a href="'.$current_url.'">返回前一頁</a>');
    }    

    $query_condition = array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'category_name' => 'article-submission',
        'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash')    
    );    
    $query_post = get_posts($query_condition);
    foreach($query_post as $post):setup_postdata($post);
    echo the_title().'<br />';
    endforeach;
    wp_reset_postdata();
    
    $lastest_submission_id = count($query_post)+1;
    $title = '投稿文章_'.$lastest_submission_id;
    
    $tougao = array(
        'post_title' => $title, 
        //'post_content' => $post_content,
        'post_category' => array($category),
        'post_status' => 'pending'
    );

    // 将文章插入数据库
    $status = wp_insert_post( $tougao );
    
    // check the attachment, get ready to upload    
    if(isset($_POST['tougao_pdf_nonce']) 
            && wp_verify_nonce($_POST['tougao_pdf_nonce'], 'tougao_pdf') 
            && current_user_can('edit_post', $status)){
        echo 'yes! we can put the attachment now! <br />';    
        echo '$_POST[tougao_pdf_nonce]: ' . $_POST['tougao_pdf_nonce'] . '<br />';
        echo '$_POST[tougao_pdf]: '.$_POST['tougao_pdf'] . '<br />';
    
    // These files need to be included as dependencies when on the front end.  
    
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );    

    // Let WordPress handle the upload.
    // Remember, 'tougao_pdf' is the name of our file input in our form above.    
        $attachment_id = media_handle_upload('tougao_pdf', $status );
    
        if(!is_wp_error($attachment_id)){
            echo $attachment_id.' upload successful! <br />';
        }
        else{
            echo 'upload failed! <br />';
        }    
    }
    else{
        echo 'nonce/verification/editable failed! <br />';
    }
    
    $att_link = '<a href="'.wp_get_attachment_url($attachment_id).'">'.wp_get_attachment_url($attachment_id).'</a>';
    $post_content = '附件網址: '.$att_link.'<br />ID: '.$name.'<br />Email: '.$email;
    wp_update_post(array('ID' => $status,'post_content' => $post_content));
    
    if ($status != 0) { 
        // 投稿成功给博主发送邮件
        // somebody#example.com替换博主邮箱
        // My subject替换为邮件标题，content替换为邮件内容
        wp_mail("somebody#example.com","My subject","content");
        
        //wp_die($status.'投稿成功！感谢投稿！<a href="'.$current_url.'">返回前一頁</a>', '投稿成功');
        die($status.'投稿成功！感谢投稿！<a href="'.$current_url.'">返回前一頁</a>');
    }
    else {
        die('投稿失败！<a href="'.$current_url.'">返回前一頁</a>');
    }
}
?>
<!--------------------------- end of action ----------------------> 

<!-- my custom page -->
<?php
if(!is_user_logged_in()){
    echo '<h1>Please loggin first! </h1>';
    die();
}
else{
    $current_user = wp_get_current_user();
?>

<h1>投稿</h1>
<form class="ludou-tougao" method="post" action="<?php echo $_SERVER["REQUEST_URI"];  ?>" enctype="multipart/form-data">
    <div class="form-group" style="text-align: left; padding-top: 10px;">
        <label for="tougao_authorname">ID </label>
        <input class="form-control" type="text" size="40" value="<?php if ( 0 != $current_user->ID ) echo $current_user->user_login; ?>" id="tougao_authorname" name="tougao_authorname" readonly="readonly"/>
    </div>

    <div class="form-group" style="text-align: left; padding-top: 10px;">
        <label for="tougao_authoremail">E-Mail </label>
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
    -->

    <div class="form-group" style="text-align: left; padding-top: 10px; display: none;">
        <label for="tougaocategorg">分類 </label>
        <?php wp_dropdown_categories('hide_empty=0&id=tougaocategorg&show_count=1&hierarchical=1'); ?>
    </div>
    
    <!--                
    <div style="text-align: left; padding-top: 10px;">
        <label style="vertical-align:top" for="tougao_content">文章内容:*</label>
        <textarea rows="15" cols="55" id="tougao_content" name="tougao_content"></textarea>
    </div>
    -->
    
    <div class="form-group" style="text-align: left; padding-top: 10px;">
        <label for="tougaoupload">上傳文章 (PDF, 小於2MB) </label>
        <input class="form-control" type="file" id="tougao_pdf" name="tougao_pdf" />
        <?php wp_nonce_field('tougao_pdf', 'tougao_pdf_nonce'); ?>
    </div>    
    
    <br clear="all">
    <div style="text-align: center; padding-top: 10px;">
        <input type="hidden" value="send" name="tougao_form" />
        <input type="submit" value="提交" name="submit"/>
        <input type="reset" value="重填" />
    </div>
</form>

<?php    
}
?>
<!-- end of my cumstom page -->                                    
<?php
}
add_shortcode('pss_shortcode', 'pss_shortcode');




