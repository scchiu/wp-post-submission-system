<?php

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

