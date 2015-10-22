<?php
/*
  Plugin Name: post-submission-system
  Plugin URI: 
  Description: Provide shortcode to create submission section and create admin management page
  Version: 1.0.0
  Author: Steve
  Author URI: 
  License: GPLv2+
  Text Domain: post-submission-system
*/

/*
 * 2015/9/1 submission post can be shown in admin page, only sort function works. 
 * consider add new submenu in edit.php using its functions.
 * 
 */
if(!class_exists('WP_LIST_TABLE')){
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class Post_Submission_List_Table extends WP_LIST_TABLE{
            
    function get_columns(){
        $columns = array(
            //'cb'        => '<input type="checkbox" />',//put checkbox for each row
            //'ID'            => 'ID',
            'post_title'    => '標題',
            'post_author'   => '作者',
            'post_content'  => '內容',
            'post_status'   => '文章狀態',
            'post_date'     => '投稿時間'
        );
        return $columns;
    }
    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        //$this->items = $this->example_data; 
        
        $posts = get_posts(array('posts_per_page' => -1, 'post_status' => 'all', 'category_name' => 'article-submission'));        
        //convert post object to array
        $post_array = array();
        for($i=0;$i<count($posts);$i++) $post_array[$i]=(array)$posts[$i];        

        //sort
        usort( $post_array, array( &$this, 'usort_reorder' ) );               
        
        //setup items
        $this->items = $post_array;
        
        /******** begin of pagination *******/
        /*
        $per_page = 5;
        $current_page = $this->get_pagenum();
        $total_items = count($this->example_data);

        // only ncessary because we have sample data
        $this->found_data = array_slice($this->example_data,(($current_page-1)*$per_page),$per_page);

        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page                     //WE have to determine how many items to show on a page
        ) );
        $this->items = $this->found_data;            
         */
        /******** begin of pagination *******/

    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) { 
        case 'ID':
        case 'post_title':
        case 'post_author':
        case 'post_content':
        case 'post_status':
        case 'post_date':
            return $item[ $column_name ];break;
        default:
            return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }
    

    function get_sortable_columns() {
      $sortable_columns = array(
        //'post_title'  => array('post_title',true),
        'post_status' => array('post_status',false),
        'post_date'   => array('post_date',false)
      );
      return $sortable_columns;
    } 

    function usort_reorder( $a, $b ) {
      // If no sort, default to title
      $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'post_date';
      // If no order, default to asc
      $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'dsc';
      // Determine sort order
      $result = strcmp( $a[$orderby], $b[$orderby] );
      // Send final sort direction to usort
      return ( $order === 'asc' ) ? $result : -$result;
    }

    //add action to 'Title' column for each row
    function column_post_title($item) {
        $actions = array(
            //'edit'      => sprintf('<a href="?page=%s&action=%s&post=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
            //'delete'    => sprintf('<a href="?page=%s&action=%s&post=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
            'edit'      => sprintf("<a href='%s'>編輯</a>", get_edit_post_link($item['ID']))
            //'delete'    => sprintf("<a href='%s'>刪除</a>", get_delete_post_link($item['ID']))
        );
        return sprintf('%1$s %2$s', $item['post_title'], $this->row_actions($actions) );
    }
    function column_post_author($item){
        $a_name = get_the_author_meta('display_name',$item['post_author']);
        //$a_role = get_the_author_meta('roles',$item['post_author'])[0];
        $a_role = wp_roles()->role_names[get_the_author_meta('roles',$item['post_author'])[0]];                    
        $display = $a_name.'<br/>('.$a_role.')';      
        $actions = array(
            'view'  => sprintf("<a href='%s'>作者資訊</a>",  get_edit_profile_url($item['post_author']))
        );
        return sprintf('%1$s %2$s', $display, $this->row_actions($actions) );        
    }
/*
    //add bulk actions
    function get_bulk_actions() {
        $actions = array(
        'delete'    => 'Delete'
        );
        return $actions;
    }

    //add checkbox for each row, remember to put something to get_column()
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="book[]" value="%s" />', $item['ID']
        );    
    }
*/
}


function my_add_menu_items(){
    add_menu_page( 'My Post Submission Table', 'Post Submission', 'activate_plugins', 'submission_post_list', 'my_render_list_page' );
}
add_action( 'admin_menu', 'my_add_menu_items' );

function my_render_list_page(){
    $myListTable = new My_Example_List_Table();
    echo '<div class="wrap"><h2>My List Table Test</h2>'; 
    $myListTable->prepare_items(); 
    $myListTable->display(); 
    echo '</div>'; 

    
    //test
    //echo get_edit_post_link(282);
    
    /*
    wp_reset_query();
    $posts = get_posts(array('posts_per_page' => 10,'post_status' => 'all'));
    echo '<div class="wrap"><h2>hello every body</h2></div>';
    $posts_array[] = array();
    
    $data=$myListTable->example_data;
    echo count($posts);
    for($i=0;$i<sizeof($posts);$i++){
        $posts_array[$i] = (array)$posts[$i];
        echo $posts[$i]->post_title.'<br/>';
    }
    
    
    for($i=0;$i<sizeof($data);$i++){
        echo $data[$i]['booktitle'].'<br/>';
    }
    
    echo "here: ".$posts_array[0]['ID'];
     * 
     */
    
}





/*
 * Template Name: 投稿頁面
 * Author：Steve
 * 
 * 更新记录
 *  2010年09月09日 ：
 *  首个版本发布
 *  
 *  2011年03月17日 ：
 *  修正时间戳函数，使用wp函数current_time('timestamp')替代time()
 *  
 *  2011年04月12日 ：
 *  修改了wp_die函数调用，使用合适的页面title
 *  
 *  2013年01月30日 ：
 *  错误提示，增加点此返回链接
 *  
 *  2013年07月24日 ：
 *  去除了post type的限制；已登录用户投稿不用填写昵称、email和博客地址
 *  
 *  2015年03月08日 ：
 *  使用date_i18n('U')代替current_time('timestamp')
 *
 *  2015/08/28:
 *  使用wp_media_upload()製作上傳pdf功能
 */

///pss system
function pss_shortcode(){
?>
<!------------------ begin of post action --------------------->
<?php
// Check that the nonce is valid, and the user can edit this post.
if( isset($_POST['tougao_form']) && $_POST['tougao_form'] == 'send') {
    global $wpdb;
    $current_url = get_permalink();
            //'http://localhost:8888/wp/119-2/';   // 修改此頁網址

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
    $category =  7; //isset( $_POST['cat'] ) ? (int)$_POST['cat'] : 0; //修改category ID
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
    $post_content = 'ID: '.$name.'<br />Email: '.$email.'<br />附件網址: '.$att_link;
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

<h1>hello</h1>
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
