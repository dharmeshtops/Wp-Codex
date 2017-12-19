<?php 
/*
Name : Dharmesh
Date : 13/02/2017
Desc : WP Rest API  Register USER
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/register/', array(
    'methods' => 'POST',
    'callback' => 'createuser_func',
  ) );
} );

function createuser_func(){
      /*=======Add Deviceinfo===============*/

      $dtoken = $_REQUEST['device_token'];
      $dtype  = $_REQUEST['device_type'];
      $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
      insert_deviceinfo( $dtoken,$dtype,$du_id);

      /*====================================*/
      $lang = $_REQUEST['lg'];
      if($lang == 'ar'){
        $exterror = 'يسمح بتنسيق ملفات جبغ و ينغ و جيف فقط.';
        $emptyupass = 'اسم المستخدم الفارغ أو كلمة المرور.';
        $emptyemail = 'عنوان البريد الإلكتروني الفارغ';
        $emailhai = 'هذا البريد الالكتروني مسجل سابقا.';
        $reg_done = 'سجلت بنجاح.';
      }else{
        $exterror = 'Only JPG, PNG and GIF files are allowed.';
        $emptyupass = 'empty username or password.';
        $emptyemail = 'empty email address';
        $emailhai = 'This email address is already registered.';
        $reg_done = 'Successfully registered.';
      }
      $info = array();
      $info['user_nicename'] = $info['nickname'] = sanitize_text_field($_REQUEST['lname']);
      $info['user_login'] = sanitize_text_field( $_REQUEST['email'] );
      $info['first_name']  = sanitize_text_field($_REQUEST['fname']);
      $info['last_name']  = sanitize_text_field($_REQUEST['lname']);
      $info['display_name'] = $info['first_name'].' '. $info['last_name'];
      $info['user_pass']     = sanitize_text_field( $_REQUEST['password'] );
      $info['user_email']    = sanitize_email( $_REQUEST['email'] );
      $info['role']          = get_option( 'default_role', 'contributor' );
      $profile_image = '';
      $phoneNumber  = $_REQUEST['phoneNumber'];
      /*......Profile Image Upload Code.....*/

      if ( ! empty( $_FILES['image']['name'] ) ) {

      // Allowed file extensions/types
      $mimes = array(
        'jpg|jpeg|jpe' => 'image/jpeg',
        'gif'          => 'image/gif',
        'png'          => 'image/png',
      );
      // Front end support - shortcode, bbPress, etc
      if ( ! function_exists( 'wp_handle_upload' ) )
        require_once ABSPATH . 'wp-admin/includes/file.php';         

      $filename = $_FILES['image']['name'];
      $allowed = array('jpg','png','gif','jpeg','jpe');
      $ext = pathinfo($filename, PATHINFO_EXTENSION);
      if(!in_array($ext,$allowed) ) {
         echo json_encode(array(
          'flag'=>false,
          'message'=> $exterror
          )); 
        exit;
      }
      $upload_dir       = wp_upload_dir(); 
      $unique_file_name = wp_unique_filename( $upload_dir['path'], $_FILES['image']['name']);     
      $avatar = wp_handle_upload( $_FILES['image'], array( 'mimes' => $mimes, 'test_form' => false, 'unique_filename_callback' => array($unique_file_name) ) );

      if (!$avatar['error']) {
          $filename = $unique_file_name;
          $wp_filetype = wp_check_filetype($filename, null );
          $attachment = array(
            'guid' => $upload_dir['url'] . '/' . $filename,
            'post_mime_type' => $wp_filetype['type'],            
            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content' => '',
            'post_status' => 'inherit'
          );
          $attachment_id = wp_insert_attachment( $attachment, $avatar['file']);
          if (!is_wp_error($attachment_id)) {
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata( $attachment_id, $avatar['file'] );
            wp_update_attachment_metadata( $attachment_id,  $attachment_data );
            $profile_image = wp_get_attachment_url( $attachment_id );
          }
        }
    } 
        /*......end Profile Image Upload Code.....*/
      if(empty($_REQUEST['email']) || empty($_REQUEST['password'])){
             echo json_encode( array(
              'flag' => false,
              'message'  => $emptyupass
            ) );
            
      }
      elseif(empty($_REQUEST['email'])){
            echo json_encode( array(
              'flag' => false,
              'message'  => $emptyemail
            ) );
           
      }
      else{
            $user_register = wp_insert_user( $info );
            $user = get_user_by( 'ID', $user_register );
            $user_email = $user->user_email;
            $firstname = $user->first_name;
            $lastname = $user->last_name; 
            /*.....Update usermeta for profile...*/
            if(!empty($attachment_id)){
              update_user_meta($user_register,'wp_user_avatar',$attachment_id);
              update_user_meta($user_register,'phoneNumber',$phoneNumber);
            }
             if ( is_wp_error( $user_register ) ) {
          $error = $user_register->get_error_codes();

          if ( in_array( 'empty_user_login', $error ) ) {
            echo json_encode( array(
              'flag' => false,
              'message'  => esc_html( $user_register->get_error_message( 'empty_user_login' ) )
            ) );
            
          } elseif ( in_array( 'existing_user_email', $error ) || in_array( 'existing_user_login', $error ) ) {
            echo json_encode( array(
              'flag' => false,
              'message'  => $emailhai
            ) );
             
          }
        } else {
           echo json_encode( array(
              'flag' => true,
              'message'  => $reg_done,
              'data'=>array(
                'userid'     => $user_register,
                'first_name' => $firstname,
                'last_name'  => $lastname,
                'phoneNumber'=>$phoneNumber,
                'profile_image'=>$profile_image
              )  
            ) );
           
        } 
      }
      exit();
}

/*
Name : Dharmesh
Date : 13/02/2017
Desc : WP Rest API  Login USER
*/

add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/login/', array(
    'methods' => 'POST',
    'callback' => 'loginuser_func',
  ) );
} );

function loginuser_func(){
      /*=======Add Deviceinfo===============*/

      $dtoken = $_REQUEST['device_token'];
      $dtype  = $_REQUEST['device_type'];
      $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
      insert_deviceinfo( $dtoken,$dtype,$du_id);

      /*====================================*/
      $lang = $_REQUEST['lg'];
      if($lang == 'ar'){
         $errorupass = 'الرجاء إدخال اسم المستخدم وكلمة المرور';
         $errorpnding = 'لا يزال حسابك في انتظار المراجعة';
         $errordenied = 'تم رفض دخول حسابك إلى هذا الموقع';
         $wrnurpass = 'اسم المستخدم أو كلمة المرور خاطئة';
         $succssmsg = 'تم تسجيل الدخول بنجاح، وإعادة التوجيه ...';
         $notexist = 'عنوان البريد الإلكتروني هذا غير مسجل حتى الآن';
         
      }else{
         $errorupass = 'Please enter Username and Password';
         $errorpnding = 'Your account is still pending for approval';
         $errordenied = 'Your account has been denied access to this site';
         $wrnurpass = 'Wrong username or password';
         $succssmsg = 'Login successful, redirecting...';
         $notexist = 'This email address is not registered yet';
      }
      $Eexists = email_exists($_REQUEST['username']);
      if($Eexists)
      {
        $info = array();
        $info['user_login'] = $_REQUEST['username'];
        $info['user_password'] = $_REQUEST['password'];
        $user_signon = wp_signon($info, false);
        $role_name = $user_signon->roles[0];
        $user_id =  $user_signon->ID;
        $user_email = $user_signon->user_email;
        $firstname = $user_signon->first_name;
        $lastname = $user_signon->last_name;
        $phoneNumber = get_user_meta($user_id, 'phoneNumber', true);
        $user_status = get_user_meta($user_id,'pw_user_status',true);
        if($info['user_login'] == '' || $info['user_password'] == '')
        {
          echo json_encode(array(
                'flag' => false,
                'message' => $errorupass
            ));
        }
          else if (is_wp_error($user_signon)) {          	
			$errors = $user_signon->errors;
			if(isset($errors['pending_approval']) && !empty($errors['pending_approval'])){
				echo json_encode(array(
                'flag' => false,
                'message' => $errorpnding
            ));
			}else if(isset($errors['denied_access']) && !empty($errors['denied_access'])){
				echo json_encode(array(
                'flag' => false,
                'message' => $errordenied
            ));

			}else{
              echo json_encode(array(
                'flag' => false,
                'message' => $wrnurpass
            ));
          }
        } 
        else 
        {
              echo json_encode(array(
                'flag' => true,
                'message' => $succssmsg,
                'data'=>array(
                  'userid'     => $user_id,
                  'first_name' => $firstname,
                  'last_name'  => $lastname,
                  'phoneNumber' => $phoneNumber,
                  'profile_image'=> $profile_image
                )              
            ));
        }
      }
      else
      {
          echo json_encode(array(
                'flag' => false,
                'message' => $notexist
            ));
      }
       exit();
} 


/*
Name : Dharmesh
Date : 13/02/2017
Desc : WP Rest API  Change Password USER
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/change_password/', array(
    'methods' => 'GET',
    'callback' => 'change_password_func',
  ) );
} );

function change_password_func(){
  /*=======Add Deviceinfo===============*/

  $dtoken = $_REQUEST['device_token'];
  $dtype  = $_REQUEST['device_type'];
  $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
  insert_deviceinfo( $dtoken,$dtype,$du_id);

  /*====================================*/
 /* $lang = $_REQUEST['lg'];
  if($lang == 'ar'){
     $chng_currpass = '';
     $chng_newpass = '';
     $chng_confpass = '';
     $chng_samepass = '';
     $chng_passmatch = '';
     $chng_wrnoldpass = '';
     $chng_done = '';
  }else{*/
     $chng_currpass = 'enter current Password';
     $chng_newpass = 'enter new Password';
     $chng_confpass = 'enter confirm Password';
     $chng_samepass = 'New password same as old one.please try again.';
     $chng_passmatch = 'Password does not match.please try again.';
     $chng_wrnoldpass = 'please enter correct your old password!';
     $chng_done = 'You have Successfully changed your Password!';
     $chng_emailnotmtch = "That E-mail doesn't belong to any registered users on this site";
     $chng_emptyuid = 'please enter user_id';
  /*}*/
  $uid = $du_id;
  $old_password = $_REQUEST['current_password'];
  $newpassword = $_REQUEST['new_password'];
  $cpassword = $_REQUEST['confirm_password'];
  $user = get_user_by( 'ID', $uid );
  $email = $user->user_email;
  if(!empty($uid)){
    $exists = email_exists($email);
    if ( $exists ) {
  if ( $user && wp_check_password( $old_password, $user->data->user_pass, $user->ID) ){
  if(empty($old_password)){ 
     echo json_encode(array(
      'flag'=>false,
      'message'=>$chng_currpass
      ));
   }  
  if(empty($newpassword)){ 
     echo json_encode(array(
      'flag'=>false,
      'message'=>$chng_newpass
      ));
   }
   if(empty($cpassword)){ 
     echo json_encode(array(
      'flag'=>false,
      'message'=>$chng_confpass
      ));
   }  
  if($old_password == $newpassword){
     echo json_encode(array(
      'flag'=>false,
      'message'=>$chng_samepass
      ));
  }
  elseif($newpassword != $cpassword){
     echo json_encode(array(
      'flag'=>false,
      'message'=>$chng_passmatch
      ));
  }
  else{   
    wp_set_password( $newpassword, $user->ID);
    $creds = array();
    $creds['user_login'] = $user->user_login;
    $creds['user_password'] = $newpassword;
    wp_signon( $creds, false );
    echo json_encode(array(
      'flag'=>true,
      'message'=>$chng_done
      ));
    } 
  }else{
    echo json_encode(array(
      'flag'=>false,
      'message'=>$chng_wrnoldpass
      ));   
  }
  }
  else{
       echo json_encode(array(
      'flag'=>false,
      'message'=> $chng_emailnotmtch
      )); 
    }
 }
  else{
     echo json_encode(array(
      'flag'=>false,
      'message'=> $chng_emptyuid
      )); 
  }
  exit();
}


/*
Name : Dharmesh
Date : 13/02/2017
Desc : WP Rest API  Forgot Password USER
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/forgot_password/', array(
    'methods' => 'GET',
    'callback' => 'forgot_password_func',
  ) );
} );

function forgot_password_func(){
  /*=======Add Deviceinfo===============*/

  $dtoken = $_REQUEST['device_token'];
  $dtype  = $_REQUEST['device_type'];
  $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
  insert_deviceinfo( $dtoken,$dtype,$du_id);

  /*====================================*/

  $email = $_REQUEST['email'];
  $lang = $_REQUEST['lg'];
  if($lang == 'ar'){
     $donemsg = 'تحقق من بريدك الإلكتروني للحصول على رابط التأكيد.';
     $notsnt = 'تعذر إرسال البريد الإلكتروني.';
     $notreg = 'هذا البريد الإلكتروني لا ينتمي إلى أي مستخدمين مسجلين على هذا الموقع';
     $notemail = 'الرجاء إدخال عنوان البريد الإلكتروني';
  }else{
     $donemsg = 'Check your email for the confirmation link.';
     $notsnt = 'The email could not be sent.';
     $notreg = "That E-mail doesn't belong to any registered users on this site";
     $notemail = 'Please enter email address';
  }
  if ( !empty( $email ) ) {
    $exists = email_exists($email);
    if ( $exists ){
    $user_data = get_user_by('email', $email);     
    $user_login = $user_data->user_login;
    $user_email = $user_data->user_email;
    $firstname = $user_data->first_name;
    $lastname = $user_data->last_name;
    $key = get_password_reset_key( $user_data );
    if ( is_wp_error( $key ) ) {
      return $key;
    }
    $to = $user_email;
    $subject = 'Password Reset';
    $message ='<p><span style="color: #565656; font-size: 14px;">Dear '.$user_login.',</span></p><p style="color: #565656; font-size: 14px;">You asked us to reset your password for your account using the email address '.$user_email.'</p><p style="color: #565656; font-size: 14px;">If this was a mistake, or you didnt ask for a password reset, just ignore this email and nothing will happen.</p><p style="color: #565656; font-size: 14px;">';
    $message .= 'Click to reset your password <a style="color: #d1021f; text-decoration: none;" href='.network_site_url("/wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') .'>Click Here</a></p>';
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
$headers = array('Content-Type: text/html; charset=UTF-8');
 
$email  = wp_mail( $to, wp_specialchars_decode($subject), $message, $headers );
if($email === TRUE){
  echo json_encode(array(
        'flag'=>true,
        'message'=>$donemsg
        ));
} else {
  echo json_encode(array(
      'flag'=>false,
      'message'=>$notsnt
      )); 
} 
  }else{
    echo json_encode(array(
      'flag'=>false,
      'message'=>$notreg
      )); 

  }
} 
 else{
  echo json_encode(array(
    'flag'=> false,
    'message'=> $notemail
    ));
 }
 exit();
}

/*
Name : Vinay
Date : 13/02/2017
Desc : WP Rest API comment Post
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/comments/add/', array(
    'methods' => 'POST',
    'callback' => 'commentpost_func',
  ) );
} );

function commentpost_func(){
  if($_REQUEST['user_id']){
    $user = get_user_by('id',$_REQUEST['user_id']);
    $role = $user->roles[0];  
    $author=$user->user_login;
    $email =$user->user_email;
    $userId = $_REQUEST['user_id'];
  }else{
    $author = $_REQUEST['author_name'];
    $email = $_REQUEST['author_email'];
    $userId = 0;
  }
  $comment_parent_id = (!$_REQUEST['parent_id'] == '') ? $_REQUEST['parent_id'] : 0;
  $comment_approved = ($role == 'administrator') ? 1 : 0;

  $data = array(
    'comment_post_ID' => $_REQUEST['pid'],
    'comment_author' => $author,
    'comment_author_email' => $email,
    'comment_content' => $_REQUEST['comment'],
    'comment_parent' => $comment_parent_id,
    'user_id' => $userId,
    'comment_author_IP' => $_SERVER['SERVER_ADDR'],
    'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
    'comment_approved' => $comment_approved,
  );

  $comment = wp_insert_comment($data);
  if($comment){
    echo json_encode(array(
      'flag'=>true,
      'message'=>'Your comment is awaiting moderation.'
      ));
  }
  else{
    echo json_encode(array(
      'flag'=>false,
      'message'=>'something went wrong.'
      ));
  }
  exit();
}

/*
Name : Vinay
Date : 14/02/2017
Desc : WP Rest API All Post By User
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/posts/user/', array(
    'methods' => 'POST',
    'callback' => 'userpost_func',
  ) );
} );

function userpost_func(){
  $args = array(
    'author'        =>  $_REQUEST['user_id'],
    'order'         =>  'DESC',
    'posts_per_page' => -1
  );
  $post = get_posts($args);
  echo json_encode(array(
    'flag'=>true,
    'data'=>$post
  ));
  exit();
}

/*
Name : Dharmesh
Date : 28/02/2017
Desc : WP Rest API Trending Post Display
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/posts/trending/', array(
    'methods' => 'GET',
    'callback' => 'trendingpost_func',
  ) );
} );

function trendingpost_func()
{
  
  /*=======Add Deviceinfo===============*/

  $dtoken = $_REQUEST['device_token'];
  $dtype  = $_REQUEST['device_type'];
  $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
  insert_deviceinfo( $dtoken,$dtype,$du_id);

  /*====================================*/
  $login_id = $du_id;
  $cat = $_REQUEST['cat']; 
  $paged = ( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1;
  $posts_per_page = 10;
   if(!empty($paged)){
    $offset = ($paged - 1) * $posts_per_page;
  }
  $query_args = array(
    'post_type' => 'post',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'cat'=>$cat,
    //'orderby' => 'meta_value_num',
     'orderby' => array('meta_value_num' => 'DESC', 'title' => 'ASC'),
    'order' => 'DESC',
    'offset'=>$offset,
    'meta_query' => array(
        'relation' => 'OR',
        array( 
            'key'=>'total_view_counts',
            'compare' => 'EXISTS'           
        ),
        array( 
            'key'=>'total_view_counts',
            'compare' => 'NOT EXISTS'           
        )
    )
  );
  $post = get_posts($query_args);
  $count_posts = wp_count_posts();
  if(isset($cat) && !empty($cat)){
  	$cposts = get_posts('post_type=post&cat=$cat'); 
    $pub_count = count($cposts);
  }
  else{
    $pub_count = $count_posts->publish;
  }
  $maxnos = ceil($pub_count / $posts_per_page);
  if(!empty($post)){
  foreach ($post as $key => $articles) {
    $id = $articles->ID;
    $link = site_url()."/?p=".$id;
    $title = $articles->post_title;
    $content = $articles->post_content;
    $user_id = $articles->post_author;
    $user = get_user_by('id',$user_id);
    $user_arr = array('id'=>$user->ID,'name'=>$user->first_name);
    $fb = get_post_meta( $id, 'essb_pc_facebook', true );
    $wp = get_post_meta( $id, 'essb_pc_whatsapp', true );
    $tw = get_post_meta( $id, 'essb_pc_twitter', true );
    $mail = get_post_meta( $id, 'essb_pc_mail', true );
    $sms = get_post_meta( $id, 'essb_pc_sms', true );
    $tele = get_post_meta( $id, 'essb_pc_telegram', true );
    $views = get_article_view_count($id);
    $total = $fb + $wp + $tw + $mail + $sms + $tele;
    $share_count = get_sharing_count($id);
    $att_id = get_post_thumbnail_id( $id );
    if(!empty($att_id))
    {
      $thumb = get_the_post_thumbnail_url( $id , 'feature-image');
      $full = get_the_post_thumbnail_url( $id , 'full');
    }
    else
    {
     $thumb = "";
     $full = "";
    }
    $post_meta = get_post_meta( $id, 'post_content', true );
    $post_meta = (empty($post_meta))? array() : $post_meta;
    foreach ($post_meta as $key=>$value) {
      if($value['type'] == 'desc'){ 
    $post_meta[$key]['value'] = trim($value['value']);
    } 
    }
    $post_categories = wp_get_post_categories( $id );
    $cats = array();    
    foreach($post_categories as $c){
    $cat = get_category( $c );
    $cats[] = array('id' => $cat->term_id,'name' => $cat->name,'slug' => $cat->slug);
    }
    $media_arr = array('id'=>$att_id,'full_url'=>$full,'thumb_url'=>$thumb);
    $views = ($login_id == $user_id) ? $views: ''; 
    //$date = human_time_diff( get_the_modified_time( 'U',$id), current_time( 'timestamp' ) ) . " " . esc_html__( '[:en]ago[:ar]منذ[:]', 'boombox' );
    $date = get_the_date('d/m/Y',$id);
    $response[] = array(
          'id'=> $id,
          'link'=> $link,
          'perlink'=> get_permalink($id),
          'title'=>$title,
          'content'=>$content,
          'post_meta_content'=>$post_meta,
          'social_counts'=>$share_count,
          'post_views'=> $views,
          'featured_media'=> $media_arr,
          'categories'=>$cats,
          'author'=> $user_arr,
          'date'=>$date,
          'isThisQuizData'=> false
          );
      }
  echo json_encode(array('flag'=>true,'max_num_page'=>$maxnos,'data'=>$response));
  }
  else{
    echo json_encode(array(
      'flag'=>false,
      'message'=>'there is no  trending articles found'
    ));
  }
  exit();
}

/*
Name : Dharmesh
Date : 25/02/2017
Desc : WP REST API Articles
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/articles/', array(
    'methods' => 'GET',
    'callback' => 'callback_articles_func',
  ) );
} );

function callback_articles_func()
{
  /*=======Add Deviceinfo===============*/

  $dtoken = $_REQUEST['device_token'];
  $dtype  = $_REQUEST['device_type'];
  $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
  insert_deviceinfo( $dtoken,$dtype,$du_id);

  /*====================================*/
  $lang = $_REQUEST['lang'];
  $login_id = $du_id;
  $cat = $_REQUEST['cat']; 
  $paged = ( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1;
  $posts_per_page = 10;
  $query_args = array(
    'post_type' => 'post',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'cat'=> array($cat)
  );
  $post = get_posts($query_args);
  $count_posts = wp_count_posts();
  if(isset($cat) && !empty($cat)){
  	$cposts = get_posts('post_type=post&cat=$cat'); 
    $pub_count = count($cposts);
  }
  else{
    $pub_count = $count_posts->publish;
  }
  $maxnos = ceil($pub_count / $posts_per_page);
  if(!empty($post)){
  foreach ($post as $key => $articles) {
    $id = $articles->ID;
    $link = site_url()."/?p=".$id;
    $title = $articles->post_title;
    $content = $articles->post_content;
    $user_id = $articles->post_author;
    $user = get_user_by('id',$user_id);
    $user_arr = array('id'=>$user->ID,'name'=>$user->first_name);
    $fb = get_post_meta( $id, 'essb_pc_facebook', true );
    $wp = get_post_meta( $id, 'essb_pc_whatsapp', true );
    $tw = get_post_meta( $id, 'essb_pc_twitter', true );
    $mail = get_post_meta( $id, 'essb_pc_mail', true );
    $sms = get_post_meta( $id, 'essb_pc_sms', true );
    $tele = get_post_meta( $id, 'essb_pc_telegram', true );
    $views = get_article_view_count($id);
    $total = $fb + $wp + $tw + $mail + $sms + $tele;
    $share_count = get_sharing_count($id);
    $att_id = get_post_thumbnail_id( $id );
    if(!empty($att_id))
    {
      $thumb = get_the_post_thumbnail_url( $id , 'feature-image');
      $full = get_the_post_thumbnail_url( $id , 'full');
    }
    else
    {
     $thumb = "";
     $full = "";
    }
    $post_meta = get_post_meta( $id, 'post_content', true );
    foreach ($post_meta as $key=>$value) {
      if($value['type'] == 'desc'){ 
    $post_meta[$key]['value'] = trim($value['value']);
    } 
    }
    $post_categories = wp_get_post_categories( $id );
    $cats = array();    
    foreach($post_categories as $c){
    $cat = get_category( $c );
    $cats[] = array('id' => $cat->term_id,'name' => $cat->name,'slug' => $cat->slug);
    }
    $media_arr = array('id'=>$att_id,'full_url'=>$full,'thumb_url'=>$thumb);
    $views = ($login_id == $user_id) ? $views: ''; 
    //$date = human_time_diff( get_the_modified_time( 'U',$id), current_time( 'timestamp' ) ) . " " . esc_html__( '[:en]ago[:ar]منذ[:]', 'boombox' );
    $date = get_the_date('d/m/Y',$id);
    $response[] = array(
          'id'=> $id,
          'link'=> $link,
          'perlink'=> get_permalink($id),
          'title'=>$title,
          'content'=>$content,
          'post_meta_content'=>$post_meta,
          'social_counts'=>$share_count,
          'post_views'=> $views,
          'featured_media'=> $media_arr,
          'categories'=>$cats,
          'author'=> $user_arr,
          'date'=>$date,
          'isThisQuizData'=> false
          );
      }
  echo json_encode(array('flag'=>true,'max_num_page'=>$maxnos,'data'=>$response));
  }
  else{
    echo json_encode(array(
      'flag'=>false,
      'message'=>'there is no  articles found'
    ));
  }
  exit();
}

/*
Name : Dharmesh
Date : 26/02/2017
Desc : WP REST API Article by ID
*/

add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/articles/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'callback_article_byid_func',
  ) );
} );

function callback_article_byid_func($request)
{
  /*=======Add Deviceinfo===============*/

  $dtoken = $_REQUEST['device_token'];
  $dtype  = $_REQUEST['device_type'];
  $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
  insert_deviceinfo( $dtoken,$dtype,$du_id);

  /*====================================*/
  $lang = $_REQUEST['lang'];
  $id = $request['id'];
  $login_id = $du_id;
  $args = array('p'=> $id, 'post_type' => 'post','post_status'=>array('draft','pending','auto-draft','publish','reject','resubmission'));
  $my_posts = new WP_Query($args);
  if ( $my_posts->have_posts() ) {
    while ( $my_posts->have_posts() ) {
    $my_posts->the_post();
    $pid = get_the_ID();
    
    if(!empty($pid && $_REQUEST['device_token'])){
      if(get_post_status() == 'publish'){
      setPostViews($pid,$_REQUEST['device_token']);
      }
    }

    $isreviewd = isReviewSend_func($pid,$dtoken);
    $terms = get_terms( array(
    'taxonomy' => 'reaction',
    'hide_empty' => false,
     'orderby'=>'term_id',
     'order'=>'ASC'
	) );    
    foreach ($terms as $reaction_key => $reaction_row) {    	
    	$reactionsarr = get_reaction_count($pid,$reaction_row->term_id);
    	if(count($reactionsarr) > 0){
    		$total = $reactionsarr[0]->total;
    	} else {
    		$total = 0;
    	}    	
       $reactionsarray[] = array(
         'reaction_name' => $reaction_row->slug,
         'total'=> $total,
        'reaction_id'=>$reaction_row->term_id,
        ); 
    }
    $title = html_entity_decode(get_the_title());
    //$title = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $title);
    $content = get_the_content();
    $link = site_url()."/?p=".$pid;
    $user_id = get_the_author_id();
    $user = get_user_by('id',$user_id);
    $user_arr = array('id'=>$user->ID,'name'=>$user->first_name);
    $views = get_article_view_count($pid);
    $share_count = get_sharing_count($pid);
    $att_id = get_post_thumbnail_id( $pid );
    if(!empty($att_id))
    {
      $thumb = get_the_post_thumbnail_url( $pid , 'feature-image');
      $full = get_the_post_thumbnail_url( $pid , 'full');
    }
    else
    {
     $thumb = "";
     $full = "";
    }
    $post_meta = get_post_meta( $pid, 'post_content', true );
    foreach ($post_meta as $key=>$value) {
      if($value['type'] == 'desc'){ 
    $post_meta[$key]['value'] = trim($value['value']);
    } 
    }
    $post_categories = wp_get_post_categories( $pid );
    $cats = array();    
    foreach($post_categories as $c){
    $cat = get_category( $c );
    $cats[] = array('id' => $cat->term_id,'name' => $cat->name,'slug' => $cat->slug);
    }
    $media_arr = array('id'=>$att_id,'full_url'=>$full,'thumb_url'=>$thumb);
    $views = ($login_id == $user_id) ? $views: ''; 
    //$date = human_time_diff( get_the_modified_time( 'U',$pid), current_time( 'timestamp' ) ) . " " . esc_html__( '[:en]ago[:ar]منذ[:]', 'boombox' );
    $date = get_the_date('d/m/Y');
    $response1[] = array(
          'id'=> $pid,
          'link'=> $link,
          'perlink'=> get_permalink($pid),
          'title'=>$title,
          'content'=>$content,
          'post_meta_content'=>$post_meta,
          'social_counts'=>$share_count,
          'post_views'=> $views,
          'reactions'=>$reactionsarray,
          'isReviewSend' => $isreviewd,
          'featured_media'=> $media_arr,
          'categories'=>$cats,
          'author'=> $user_arr,
          'date'=>$date
          );
    }
    echo json_encode(array('flag'=>true,'data'=>$response1));
  } else {
    echo json_encode(array(
      'flag'=>false,
      'message'=>'invalid article ID'
    ));
  }
  exit();
}

/*
Name : Dharmesh
Date : 26/02/2017
Desc : WP REST API Mylisting by User ID
*/

add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/mylisting/(?P<uid>\d+)', array(
    'methods' => 'GET',
    'callback' => 'callback_mylisting_func',
  ) );
} );

function callback_mylisting_func($request)
{
  $lang     = $_REQUEST['lang'];
  $id = $request['uid'];
  $login_id = $id;
  $user = get_user_by( 'ID', $id );
  $email = $user->user_email;
  $exists = email_exists($email);
  if($exists)
  {
    $args = array('author'=> $id, 'post_type' => 'post','post_status'=>array('draft','pending','auto-draft','publish','reject','resubmission'),'posts_per_page'=>-1);
    $mylistings = new WP_Query($args);
    if ( $mylistings->have_posts() ) 
    {
      while ( $mylistings->have_posts() ) 
      {
        $mylistings->the_post();
        $pid = get_the_ID();
        $link = site_url()."/?p=".$pid;
        $post_status = get_post_status();
        $title = html_entity_decode(get_the_title());
        //$title = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $title);
        $content = get_the_content();
        $user_id = get_the_author_id();
        $user = get_user_by('id',$user_id);
        $user_arr = array('id'=>$user->ID,'name'=>$user->first_name);
        $views = get_article_view_count($pid);
        $views = ($login_id == $user_id) ? $views: '';
        $share_count = get_sharing_count($pid);
        $att_id = get_post_thumbnail_id( $pid );
        if(!empty($att_id))
        {
          $thumb = get_the_post_thumbnail_url( $pid , 'feature-image');
          $full = get_the_post_thumbnail_url( $pid , 'full');
        }
        else
        {
          $thumb = "";
          $full = "";
        }
        $post_meta = get_post_meta( $pid, 'post_content', true );
        foreach ($post_meta as $key=>$value) {
        if($value['type'] == 'desc'){ 
        $post_meta[$key]['value'] = trim($value['value']);
        } 
        }
        $post_categories = wp_get_post_categories( $pid );
        $cats = array();    
        foreach($post_categories as $c){
        $cat = get_category( $c );
        $cats[] = array('id' => $cat->term_id,'name' => $cat->slug,'title' => $cat->name);
        }
        $media_arr = array('id'=>$att_id,'full_url'=>$full,'thumb_url'=>$thumb);
        //$date = human_time_diff( get_the_modified_time( 'U',$pid), current_time( 'timestamp' ) ) . " " . esc_html__( '[:en]ago[:ar]منذ[:]', 'boombox' );
        $date = get_the_date('d/m/Y');
        $mylisting[] = array(
              'id'=> $pid,
              'link'=> $link,
              'perlink'=> get_permalink($pid),
              'title'=>$title,
              'content'=>$content,
              'post_status'=>$post_status,
              'post_meta_content'=>$post_meta,
              'social_counts'=>$share_count,
              'post_views'=> $views,
              'featured_media'=> $media_arr,
              'categories'=>$cats,
              'author'=> $user_arr,
              'date'=>$date
              );
      }
        echo json_encode(array('flag'=>true,'data'=>$mylisting));
    }
    else
    {
        echo json_encode(array(
          'flag'=>false,
          'message'=>'no articles found for this user'
        ));
    }
  }
  else
  {
     echo json_encode(array(
        'flag'=>false,
        'message'=>'invalid User ID'
      ));
  }  
  exit();
}


/*
Name : Dharmesh
Date : 26/02/2017
Desc : WP REST API My Listing Article by ID
*/

add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/mylisting/articles/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'callback_mylisting_article_byid_func',
  ) );
} );

function callback_mylisting_article_byid_func($request)
{
   /*=======Add Deviceinfo===============*/

   $dtoken = $_REQUEST['device_token'];
   $dtype  = $_REQUEST['device_type'];
   $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
   insert_deviceinfo( $dtoken,$dtype,$du_id);

  /*====================================*/
  $lang = $_REQUEST['lang'];
  $id = $request['id'];
  $login_id = $du_id;
  $args = array('p'=> $id, 'post_type' => 'post','post_status'=>array('draft','pending','auto-draft','publish','reject','resubmission'),'author'=>$login_id);
  $my_posts = new WP_Query($args);
  if($login_id != 'null')
  {
     $user = get_user_by( 'ID', $login_id );
     $email = $user->user_email;
     $exists = email_exists($email);
    if($exists){
      if ( $my_posts->have_posts() ) {
        while ( $my_posts->have_posts() ) {
        $my_posts->the_post();
        $pid = get_the_ID();
        $post_status = get_post_status();
        $link = site_url()."/?p=".$pid;
        if(!empty($pid && $_REQUEST['device_token'])){
          if($post_status == 'publish'){
          setPostViews($pid,$_REQUEST['device_token']);
         }
        }

        $isreviewd = isReviewSend_func($pid,$dtoken);
        $terms = get_terms( array(
        'taxonomy' => 'reaction',
        'hide_empty' => false,
         'orderby'=>'term_id',
         'order'=>'ASC'
      ) );    
        foreach ($terms as $reaction_key => $reaction_row) {      
          $reactionsarr = get_reaction_count($pid,$reaction_row->term_id);
          if(count($reactionsarr) > 0){
            $total = $reactionsarr[0]->total;
          } else {
            $total = 0;
          }     
           $reactionsarray[] = array(
             'reaction_name' => $reaction_row->slug,
             'total'=> $total,
            'reaction_id'=>$reaction_row->term_id,
            ); 
        }
        $title = html_entity_decode(get_the_title());
        //$title = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $title);
        $content = get_the_content();
        $user_id = get_the_author_id();
        $user = get_user_by('id',$user_id);
        $user_arr = array('id'=>$user->ID,'name'=>$user->first_name);
        $views = get_article_view_count($pid);
        $share_count = get_sharing_count($pid);
        $att_id = get_post_thumbnail_id( $pid );
        if(!empty($att_id))
        {
          $thumb = get_the_post_thumbnail_url( $pid , 'feature-image');
          $full = get_the_post_thumbnail_url( $pid , 'full');
        }
        else
        {
         $thumb = "";
         $full = "";
        }
        $post_meta = get_post_meta( $pid, 'post_content', true );
        foreach ($post_meta as $key=>$value) {
          if($value['type'] == 'desc'){ 
        $post_meta[$key]['value'] = trim($value['value']);
        } 
        }
        $post_categories = wp_get_post_categories( $pid );
        $cats = array();    
        foreach($post_categories as $c){
        $cat = get_category( $c );
        $cats[] = array('id' => $cat->term_id,'name' => $cat->slug,'title' => $cat->name);
        }
        $media_arr = array('id'=>$att_id,'full_url'=>$full,'thumb_url'=>$thumb);
        $views = ($login_id == $user_id) ? $views: ''; 
        //$date = human_time_diff( get_the_modified_time( 'U',$pid), current_time( 'timestamp' ) ) . " " . esc_html__( '[:en]ago[:ar]منذ[:]', 'boombox' );
        $date = get_the_date('d/m/Y');
        $response1[] = array(
              'id'=> $pid,
              'link'=>$link,
              'perlink'=> get_permalink($pid),
              'title'=>$title,
              'post_status'=>$post_status,
              'content'=>$content,
              'post_meta_content'=>$post_meta,
              'social_counts'=>$share_count,
              'post_views'=> $views,
              'reactions'=>$reactionsarray,
              'isReviewSend' => $isreviewd,
              'featured_media'=> $media_arr,
              'categories'=>$cats,
              'author'=> $user_arr,
              'date'=>$date
              );
        }
        echo json_encode(array('flag'=>true,'data'=>$response1));
      } 
          else 
          {
            echo json_encode(array(
              'flag'=>false,
              'message'=>'invalid article ID'
            ));
          }
        }
        else
        {
          echo json_encode(array(
              'flag'=>false,
              'message'=>'user ID doest not exists'
            ));
        }
  }
  else
  {
    echo json_encode(array(
      'flag'=>false,
      'message'=>'please enter login_id parameter value'
    ));
  }
  exit();
}

/*
Name : Vinay
Date : 28/02/2017
Desc : WP Rest API Review Api
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/posts/review/', array(
    'methods' => 'GET',
    'callback' => 'article_review_func',
  ) );
} );

function article_review_func(){
 if(!empty($_REQUEST)){
  global $wpdb;
  $querystr = "SELECT * FROM wp_reactions WHERE reaction_id = '".$_REQUEST['reaction_id']."'
    AND ip_address = '".$_REQUEST['device_token']."' 
    AND post_id = '".$_REQUEST['post_id']."'";
  $reactions = $wpdb->get_results($querystr, OBJECT);
  if(empty($reactions)){

    $inserted=$wpdb->insert('wp_reactions',array( 'post_id' => $_REQUEST['post_id'], 'ip_address' => $_REQUEST['device_token'],'reaction_id' => $_REQUEST['reaction_id'],'user_id'=>$_REQUEST['login_id']));

    if($inserted == 1){

        $query = "SELECT * FROM wp_reaction_total WHERE reaction_id = '".$_REQUEST['reaction_id']."' 
                    AND post_id = '".$_REQUEST['post_id']."'";
        $chktotal = $wpdb->get_results($query, OBJECT);
        if(empty($chktotal)){
          $total = 1;
          $wpdb->insert('wp_reaction_total',array( 'post_id' => $_REQUEST['post_id'],'reaction_id' => $_REQUEST['reaction_id'],'total' => $total));
        }else{
          $get_total = $chktotal[0]->total;
          $total = $get_total + 1;
          $result = $wpdb->update('wp_reaction_total', array('total'=>$total), array('post_id' => $_REQUEST['post_id'],'reaction_id' => $_REQUEST['reaction_id']));
        }
        $reaction_name = get_term( $_REQUEST['reaction_id'] , 'reaction');
        die(json_encode(array(
          'flag'=>true,
          'review'=>true,
          'message'=>'Done',
          'data'=>array(
          	'reaction_name' => $reaction_name->slug,
            'total' => $total,
            'reaction_id'=>$_REQUEST['reaction_id']
            )
        )));
    }
  }else{
      die(json_encode(array(
          'flag'=>false,
          'review'=>false,
          'message'=>'You have reacted on this article'
        )));
    }
}
else{
     die(json_encode(array(
          'flag'=>false,
          'review'=>false,
          'message'=>'empty requested data!'
        )));
    
}
}

/*
Name : Vinay
Date : 03/03/2017
Desc : WP Rest API Media upload
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/uploads/', array(
    'methods' => 'POST',
    'callback' => 'media_upload_func',
  ) );
} );

function media_upload_func(){
    $filename = $_FILES['media']['name'];
    $allowed = array('jpg','png','gif','jpeg','jpe');
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if(!in_array($ext,$allowed) ) {
       echo json_encode(array(
        'flag'=>false,
        'message'=>'Only JPG, PNG and GIF files are allowed.'
        )); 
    }else{
      $upload_dir       = wp_upload_dir(); 
      $unique_file_name = wp_unique_filename( $upload_dir['path'], $_FILES['media']['name']);     
      $media = wp_handle_upload( $_FILES['media'], array('test_form' => false) );
      if (!$media['error']) {
        $filename = $unique_file_name;
        $wp_filetype = wp_check_filetype($filename, null );
        $attachment = array(
          'guid' => $upload_dir['url'] . '/' . $filename,
          'post_mime_type' => $wp_filetype['type'],            
          'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
          'post_content' => '',
          'post_status' => 'inherit',
          'post_author' => $_REQUEST['login_id']
        );
        $attachment_id = wp_insert_attachment( $attachment, $media['file']);
        if (!is_wp_error($attachment_id)) {
            $img_url = wp_get_attachment_image_src($attachment_id);
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata( $attachment_id, $media['file'] );
            wp_update_attachment_metadata( $attachment_id,  $attachment_data );
            echo json_encode(array(
              'flag'=>true,                
              'media' => array(
                  'attachment_id' => $attachment_id,
                  'url' => $img_url[0],
                ),
              )); 
        }
      }
    }
  exit;
}

/*
Name : Vinay
Date : 06/03/2017
Desc : WP Rest API Create Post
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/posts/add/', array(
    'methods' => 'POST',
    'callback' => 'createpost_func',
  ) );
} );

function createpost_func(){
       /*=======Add Deviceinfo===============*/

      $dtoken = $_REQUEST['device_token'];
      $dtype  = $_REQUEST['device_type'];
      $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
      insert_deviceinfo( $dtoken,$dtype,$du_id);
      /*====================================*/
      $lang = $_REQUEST['lg'];
      if($lang == 'ar'){
         $addarcl_done = 'تم إرسال مقالتك بنجاح.';
         $addarcl_error = 'خطأ أثناء إدراج المقالة. حاول مرة اخرى';
         $addarcl_cntnl = 'محتوى المشاركة فارغ!'; 
      }else{
         $addarcl_done = 'Your article has been Submitted Successfully.';
         $addarcl_error = 'error while insert article. please try again';
         $addarcl_cntnl = 'Your post content is null!';
      }
      $postcntjson = stripcslashes($_REQUEST['post_content']);
      $postcntjson = json_decode($postcntjson);
      $postcntjson = json_encode($postcntjson);
      $validj = is_json($postcntjson,TRUE);
      if(!is_array($validj)){
         echo json_encode(array(
        'flag'=>false,
        'message'=> $addarcl_cntnl
        ));
        exit();
      }
      $post_id = wp_insert_post(array(
        'post_author' =>$_REQUEST['login_id'],
        'post_title'  =>$_REQUEST['title'],
        'comment_status' => 'closed',
        'post_status'=>'pending'
      ));
      if(!empty($post_id)){
        $post_meta = json_decode(stripcslashes($_REQUEST['post_content']),TRUE);
        update_post_meta($post_id, 'post_content', $post_meta); 
        update_post_meta($post_id,'device_type',$dtype);
        wp_set_post_terms( $post_id, $_REQUEST['cat_id'],'category');
        $image_url  = $_REQUEST['feature_image']; // Define the image URL here
        global $wpdb;
        $attachment = $wpdb->get_results($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='".$_REQUEST['feature_image']."'"));
        $attach_id = $attachment[0];
        set_post_thumbnail( $post_id, $attach_id->ID );
        echo json_encode(array(
        'flag'=>true,
        'message'=>$addarcl_done
        ));
      }
      else
      {
        echo json_encode(array(
        'flag'=>false,
        'message'=>$addarcl_error
        )); 
      }
      exit();
}


/*
Name : Vinay
Date : 06/03/2017
Desc : WP Rest API Update Post
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/posts/update/', array(
    'methods' => 'POST',
    'callback' => 'updatepost_func',
  ) );
} );

function updatepost_func(){
       /*=======Add Deviceinfo===============*/

      $dtoken = $_REQUEST['device_token'];
      $dtype  = $_REQUEST['device_type'];
      $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
      insert_deviceinfo( $dtoken,$dtype,$du_id);

      /*====================================*/
      $lang = $_REQUEST['lg'];
      if($lang == 'ar'){
         $updtart_done = 'تم تحديث مقالتك بنجاح.';
         $updtart_error = 'خطأ أثناء تحديث المقالة. حاول مرة اخرى';
      }else{
         $updtart_done = 'Your article has been Updated Successfully.';
         $updtart_error = 'error while update article. please try again';
      }
      $post_id = $_REQUEST['post_id'];
      $pstatus = get_post_status($post_id);
      if(get_post($post_id) && 'publish' != $pstatus && !empty($post_id)){
      $update_status = ($pstatus == 'reject') ? 'resubmission': $pstatus;
      wp_update_post(array(
        'ID'=>$post_id,
        'post_author' =>$_REQUEST['login_id'],
        'post_title'  =>$_REQUEST['title'],
        'post_status' => $update_status
       ));
      wp_set_post_terms( $post_id, $_REQUEST['cat_id'],'category');
        $post_meta = json_decode(stripcslashes($_REQUEST['post_content']),TRUE);
	    update_post_meta($post_id, 'post_content', $post_meta); 
        $image_url  = $_REQUEST['feature_image']; // Define the image URL here
        global $wpdb;
        $attachment = $wpdb->get_results($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='".$_REQUEST['feature_image']."'"));
        $attach_id = $attachment[0];
        set_post_thumbnail( $post_id, $attach_id->ID );
        echo json_encode(array(
        'flag'=>true,
        'message'=>$updtart_done
        ));
      }
      else
      {
        echo json_encode(array(
        'flag'=>false,
        'message'=>$updtart_error
        )); 
      }
      exit();
}
/*
Name : Vinay
Date : 11/03/2017
Desc : WP Rest API Delete Attachment
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/uploads/delete/', array(
    'methods' => 'POST',
    'callback' => 'media_delete_func',
  ) );
} );

function media_delete_func(){
  if(!empty($_REQUEST['attachment_id'])){
    $delete = wp_delete_attachment($_REQUEST['attachment_id']);
    if($delete){
      echo json_encode(array(
            'flag'=>true,
            'message'=>'Attachment has been deleted Successfully.'
          ));
      }else{
        echo json_encode(array(
            'flag'=>false,
            'message'=>'error while deleting attachment...'
          ));
      }
  }else{
    echo json_encode(array(
          'flag'=>false,
          'message'=>'empty requested data!'
        ));
  }
  exit();
}

/*
Name : Dharmesh
Date : 17/03/2017
Desc : WP Rest API Add Quiz
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/quiz/add/', array(
    'methods' => 'POST',
    'callback' => 'quiz_add_func',
  ) );
} );

function quiz_add_func(){
  /*=======Add Deviceinfo===============*/

    $dtoken = $_REQUEST['device_token'];
    $dtype  = $_REQUEST['device_type'];
    $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
    insert_deviceinfo( $dtoken,$dtype,$du_id);

  /*====================================*/
  $lang = $_REQUEST['lg'];
    if($lang == 'ar'){
       $updtqz_done = 'تم إرسال مسابقة بنجاح';
       $updtqz_error = 'هناك خطأ ما';
       $updtqz_nullcnt = 'محتوى المشاركة فارغ!';
    }else{
       $updtqz_done = 'Your Quiz has been submitted Successfully';
       $updtqz_error = 'something went wrong';
       $updtqz_nullcnt = 'Your post content is null!';
    }
    $postcntjson = stripcslashes($_REQUEST['post_content']);
      $postcntjson = json_decode($postcntjson);
      $postcntjson = json_encode($postcntjson);
      $validj = is_json($postcntjson,TRUE);
      if(!is_array($validj)){
         echo json_encode(array(
        'flag'=>false,
        'message'=> $updtqz_nullcnt
        ));
        exit();
    }
    $que_type = $_REQUEST['question_type'];
    $ans_type = $_REQUEST['answer_type'];
    $cat_id    = $_REQUEST['cat_id'];
    $quiz_type = $_REQUEST['quiz_type'];
    $image_url  = $_REQUEST['feature_image']; 
    $personality_type = $_REQUEST['personality_type'];
    $quiz_id = wp_insert_post(array(
      'post_author' =>$_REQUEST['login_id'],
      'post_title'  =>$_REQUEST['title'],
      'comment_status' => 'closed',
      'post_type'=>'quiz',
      'post_status'=>'pending'
    ));
    if(!empty($quiz_id)){
    $post_meta = json_decode(stripcslashes($_REQUEST['post_content']),TRUE);
    $perso_type_meta = json_decode(stripcslashes($personality_type),TRUE);
    wp_set_post_terms( $quiz_id, $cat_id,'category');
    wp_set_post_terms( $quiz_id, $quiz_type,'quiz_type'); 
    update_post_meta($quiz_id,'post_content',$post_meta);
    update_post_meta($quiz_id,'que_type',$que_type);
    update_post_meta($quiz_id,'ans_type',$ans_type);
    update_post_meta($quiz_id,'personality_type',$perso_type_meta);
    update_post_meta($quiz_id,'device_type',$dtype);
    global $wpdb;
    $attachment = $wpdb->get_results($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='".$image_url."'"));
    $attach_id = $attachment[0];
    set_post_thumbnail( $quiz_id, $attach_id->ID );
    echo json_encode(array(
      'flag'=>true,
      'message'=>$updtqz_done
      ));
    }  
    else
    {
     echo json_encode(array(
      'flag'=>false,
      'message'=>$updtqz_error
      ));
    }
  exit();
}

/*
Name : Dharmesh
Date : 17/03/2017
Desc : WP Rest API Update Quiz
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/quiz/edit/', array(
    'methods' => 'POST',
    'callback' => 'quiz_edit_func',
  ) );
} );

function quiz_edit_func(){

  /*=======Add Deviceinfo===============*/
    $dtoken = $_REQUEST['device_token'];
    $dtype  = $_REQUEST['device_type'];
    $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
    insert_deviceinfo( $dtoken,$dtype,$du_id);
  /*====================================*/
   $lang = $_REQUEST['lg'];
    if($lang == 'ar'){
       $updtqz_done = 'تم تحديث الاختبار بنجاح.';
       $updtqz_error = 'هناك خطأ ما';
    }else{
       $updtqz_done = 'Your quiz has been Updated Successfully.';
       $updtqz_error = 'something went wrong';
    }
    $cat_id    = $_REQUEST['cat_id'];
    $image_url  = $_REQUEST['feature_image'];
    $quiz_id = $_REQUEST['quiz_id'];
    $personality_type = $_REQUEST['personality_type'];
    $quiz_typeq = $_REQUEST['quiz_type'];
    $que_type = $_REQUEST['question_type'];
    $ans_type = $_REQUEST['answer_type'];
    if(!empty($quiz_id)){
    $pstatus = get_post_status($quiz_id);
    $update_status = ($pstatus == 'reject') ? 'resubmission': $pstatus;

    wp_update_post(array(
      'ID' =>  $quiz_id,
      'post_author' =>$_REQUEST['login_id'],
      'post_title'  =>$_REQUEST['title'],
      'comment_status' => 'closed',
      'post_type'=>'quiz',
      'post_status'=>$update_status
    ));
    $post_meta = json_decode(stripcslashes($_REQUEST['post_content']),TRUE);
    $perso_type_meta = json_decode(stripcslashes($personality_type),TRUE);
    wp_set_post_terms( $quiz_id, $cat_id,'category');
    wp_set_post_terms( $quiz_id, $quiz_typeq,'quiz_type');
    update_post_meta($quiz_id,'post_content',$post_meta);
    update_post_meta($quiz_id,'personality_type',$perso_type_meta);
    update_post_meta($quiz_id,'que_type',$que_type);
    update_post_meta($quiz_id,'ans_type',$ans_type);
    global $wpdb;
    $attachment = $wpdb->get_results($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='".$image_url."'"));
    $attach_id = $attachment[0];
    set_post_thumbnail( $quiz_id, $attach_id->ID );
    echo json_encode(array(
      'flag'=>true,
      'message'=>$updtqz_done
      ));
    }  
    else
    {
     echo json_encode(array(
      'flag'=>false,
      'message'=>$updtqz_error
      ));
    }
  exit();
}

/*
Name : Dharmesh
Date : 17/03/2017
Desc : WP Rest API Quiz taxonomies lists
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/quiz_taxos/', array(
    'methods' => 'POST',
    'callback' => 'quiz_taxos_func',
  ) );
} );
function quiz_taxos_func()
{
  $quiz_categories = get_categories(array('taxonomy'=>'category', 'hide_empty'=> 0));
  foreach ( $quiz_categories as $quiz_category ) {
    $quiz_cat_arr[] =  array(
      'id'=>$quiz_category->term_id,
      'category'=>$quiz_category->slug
      );
  }
  $quiz_types = get_categories(array(
                      'taxonomy'=>'quiz_type', 
                      'hide_empty'=> 0, 
                      'orderby' => 'ID',
                      'order'   => 'ASC'));
  foreach ( $quiz_types as $quiz_type ) {
    $quiz_type_arr[] =  array(
      'id'=>$quiz_type->term_id,
      'type'=>$quiz_type->slug
      );
  }
  if(!empty($quiz_categories) && !empty($quiz_types))
  {
  echo json_encode(
    array(
      'flag'=>true,
      'quiz_category'=>$quiz_cat_arr,
      'quiz_type'=>$quiz_type_arr
      )
    );
  }
  else
  {
   echo json_encode(
    array(
      'flag'=>false,
      'message'=>'getting empty quiz taxonomies!'
      )
    );
  }
  exit;
}

/*
Name : Dharmesh
Date : 24/03/2017
Desc : WP Rest API Social Sharing count
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/social_share/', array(
    'methods' => 'POST',
    'callback' => 'social_share_func',
  ) );
} );
function social_share_func(){
  $post_id      = $_REQUEST['post_id'];
  $device_token = $_REQUEST['device_token'];
  $share_type   = $_REQUEST['share_type'];
  if(!empty($post_id))
  {
    insert_sharing_info($post_id, $device_token ,$share_type);
    echo json_encode(array(
      'flag'=> true,
      'message'=> 'thank you for sharing!'
      ));
  }
  else
  {
   echo json_encode(array(
      'flag'=> false,
      'message'=> 'PostID is empty or not passed!'
      ));
  }
  exit();
}

/*
Name : Dharmesh
Date : 25/02/2017
Desc : WP REST API Articles
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/quiz/', array(
    'methods' => 'GET',
    'callback' => 'callback_quiz_func',
  ) );
} );

function callback_quiz_func()
{
  /*=======Add Deviceinfo===============*/

  $dtoken = $_REQUEST['device_token'];
  $dtype  = $_REQUEST['device_type'];
  $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
  insert_deviceinfo( $dtoken,$dtype,$du_id);

  /*====================================*/
  $lang = $_REQUEST['lang'];
  $login_id = $du_id;
  $cat = $_REQUEST['cat']; 
  $paged = ( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1;
  $posts_per_page = 10;
  $query_args = array(
    'post_type' => 'quiz',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'cat'=> array($cat)
  );
  $post = get_posts($query_args);
  $count_posts = wp_count_posts( 'quiz' );
  if(isset($cat) && !empty($cat)){
    $cposts = get_posts('post_type=quiz&cat=$cat'); 
    $pub_count = count($cposts);
  }
  else{
    $pub_count = $count_posts->publish;
  }
  $maxnos = ceil($pub_count / $posts_per_page);
  if(!empty($post)){
  foreach ($post as $key => $articles) {
    $id = $articles->ID;
    $link = site_url()."/?p=".$id;
    $title = $articles->post_title;
    $content = $articles->post_content;
    $user_id = $articles->post_author;
    $user = get_user_by('id',$user_id);
    $user_arr = array('id'=>$user->ID,'name'=>$user->first_name);
    $views = get_article_view_count($id);
    $share_count = get_sharing_count($id);
    $att_id = get_post_thumbnail_id( $id );
    if(!empty($att_id))
    {
      $thumb = get_the_post_thumbnail_url( $id , 'feature-image');
      $full = get_the_post_thumbnail_url( $id , 'full');
    }
    else
    {
     $thumb = "";
     $full = "";
    }
    $post_meta = get_post_meta( $id, 'post_content', true );
    $personality_type = get_post_meta( $id, 'personality_type', true );
    $que_type  = get_post_meta( $id, 'que_type', true );
    $ans_type  = get_post_meta( $id, 'ans_type', true );
    $post_categories = wp_get_post_categories( $id );
    $cats = array();  
    foreach($post_categories as $c){
    $cat = get_category( $c );
    $cats[] = array('id' => $cat->term_id,'name' => $cat->name,'slug' => $cat->slug);
    }
    $quiz_type_terms = wp_get_post_terms( $id, 'quiz_type');
    foreach($quiz_type_terms as $quiz_type_term){
    $quiz_type[] = array('id' => $quiz_type_term->term_id,'name' => $quiz_type_term->slug);
    }
    $media_arr = array('id'=>$att_id,'full_url'=>$full,'thumb_url'=>$thumb);
    $views = ($login_id == $user_id) ? $views: ''; 
    //$date = human_time_diff( get_the_modified_time( 'U',$id), current_time( 'timestamp' ) ) . " " . esc_html__( '[:en]ago[:ar]منذ[:]', 'boombox' );
    $date = get_the_date('d/m/Y',$id);
    $response[] = array(
          'id'=> $id,
          'link'=> $link,
          'perlink'=> get_permalink($id),
          'title'=>$title,
          'content'=>$content,
          'question_type'=>$que_type,
          'answer_type'=>$ans_type,
          'post_meta_content'=>$post_meta,
          'personality_type'=> $personality_type,
          'social_counts'=>$share_count,
          'post_views'=> $views,
          'featured_media'=> $media_arr,
          'categories'=>$cats,
          'quiz_type' => $quiz_type,
          'author'=> $user_arr,
          'date'=>$date,
          'isThisQuizData'=> true
          );
      }
  echo json_encode(array('flag'=>true,'max_num_page'=>$maxnos,'data'=>$response));
  }
  else{
    echo json_encode(array(
      'flag'=>false,
      'message'=>'there is no  quiz found'
    ));
  }
  exit();
}

/*
Name : Dharmesh
Date : 26/02/2017
Desc : WP REST API Article by ID
*/

add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/quiz/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'callback_quiz_byid_func',
  ) );
} );

function callback_quiz_byid_func($request)
{
  /*=======Add Deviceinfo===============*/

  $dtoken = $_REQUEST['device_token'];
  $dtype  = $_REQUEST['device_type'];
  $du_id  = !empty($_REQUEST['login_id']) ? $_REQUEST['login_id'] : 'null';
  insert_deviceinfo( $dtoken,$dtype,$du_id);

  /*====================================*/
  $lang = $_REQUEST['lang'];
  $id = $request['id'];
  $login_id = $du_id;
  $args = array('p'=> $id, 'post_type' => 'quiz','post_status'=>array('draft','pending','auto-draft','publish','reject','resubmission'));
  $my_posts = new WP_Query($args);
  if ( $my_posts->have_posts() ) {
    while ( $my_posts->have_posts() ) {
    $my_posts->the_post();
    $pid = get_the_ID();
    
    if(!empty($pid && $_REQUEST['device_token'])){
       if(get_post_status() == 'publish'){
      setPostViews($pid,$_REQUEST['device_token']);
     }
    }

    $isreviewd = isReviewSend_func($pid,$dtoken);
    $terms = get_terms( array(
    'taxonomy' => 'reaction',
    'hide_empty' => false,
     'orderby'=>'term_id',
     'order'=>'ASC'
  ) );    
    foreach ($terms as $reaction_key => $reaction_row) {      
      $reactionsarr = get_reaction_count($pid,$reaction_row->term_id);
      if(count($reactionsarr) > 0){
        $total = $reactionsarr[0]->total;
      } else {
        $total = 0;
      }     
       $reactionsarray[] = array(
         'reaction_name' => $reaction_row->slug,
         'total'=> $total,
        'reaction_id'=>$reaction_row->term_id,
        ); 
    }
    $title = html_entity_decode(get_the_title());
    //$title = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $title);
    $link = site_url()."/?p=".$pid;
    $content = get_the_content();
    $user_id = get_the_author_id();
    $user = get_user_by('id',$user_id);
    $user_arr = array('id'=>$user->ID,'name'=>$user->first_name);
    $views = get_article_view_count($pid);
    $share_count = get_sharing_count($pid);
    $att_id = get_post_thumbnail_id( $pid );
    if(!empty($att_id))
    {
      $thumb = get_the_post_thumbnail_url( $pid , 'feature-image');
      $full = get_the_post_thumbnail_url( $pid , 'full');
    }
    else
    {
     $thumb = "";
     $full = "";
    }
    $post_meta = get_post_meta( $pid, 'post_content', true );
    $que_type  = get_post_meta( $pid, 'que_type', true );
    $ans_type  = get_post_meta( $pid, 'ans_type', true );
    $personality_type = get_post_meta( $pid, 'personality_type', true );
    $post_categories = wp_get_post_categories( $pid );
    $cats = array();    
    foreach($post_categories as $c){
    $cat = get_category( $c );
    $cats[] = array('id' => $cat->term_id,'name' => $cat->name,'slug' => $cat->slug);
    }
    $quiz_type_terms = wp_get_post_terms( $pid, 'quiz_type');
    foreach($quiz_type_terms as $quiz_type_term){
    $quiz_type[] = array('id' => $quiz_type_term->term_id,'name' => $quiz_type_term->name);
    }
    $media_arr = array('id'=>$att_id,'full_url'=>$full,'thumb_url'=>$thumb);
    $views = ($login_id == $user_id) ? $views: ''; 
    //$date = human_time_diff( get_the_modified_time( 'U',$pid), current_time( 'timestamp' ) ) . " " . esc_html__( '[:en]ago[:ar]منذ[:]', 'boombox' );
    $date = get_the_date('d/m/Y');
    $response1[] = array(
          'id'=> $pid,
          'link'=> $link,
          'perlink'=> get_permalink($pid),
          'title'=>$title,
          'content'=>$content,
          'question_type'=>$que_type,
          'answer_type'=>$ans_type,
          'personality_type'=>$personality_type,
          'post_meta_content'=>$post_meta,
          'social_counts'=>$share_count,
          'post_views'=> $views,
          'reactions'=>$reactionsarray,
          'isReviewSend' => $isreviewd,
          'featured_media'=> $media_arr,
          'categories'=>$cats,
          'quiz_type'=> $quiz_type,
          'author'=> $user_arr,
          'date'=>$date
          );
    }
    echo json_encode(array('flag'=>true,'data'=>$response1));
  } else {
    echo json_encode(array(
      'flag'=>false,
      'message'=>'invalid quiz ID'
    ));
  }
  exit();
}

/*
Name : Dharmesh
Date : 5/04/2017
Desc : WP REST API MyQuiz listing  by User ID
*/

add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v2', '/myquizlisting/(?P<uid>\d+)', array(
    'methods' => 'GET',
    'callback' => 'callback_myquizlisting_func',
  ) );
} );

function callback_myquizlisting_func($request)
{
  $lang     = $_REQUEST['lang'];
  $id = $request['uid'];
  $login_id = $id;
  $user = get_user_by( 'ID', $id );
  $email = $user->user_email;
  $exists = email_exists($email);
  if($exists)
  {
    $args = array('author'=> $id, 'post_type' => 'quiz','post_status'=>array('draft','pending','auto-draft','publish','reject','resubmission'),'posts_per_page'=>-1);
    $mylistings = new WP_Query($args);
    if ( $mylistings->have_posts() ) 
    {
      while ( $mylistings->have_posts() ) 
      {
        $mylistings->the_post();
        $pid = get_the_ID();
        $post_status = get_post_status();
        $title = html_entity_decode(get_the_title());
        //$title = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $title);
        $content = get_the_content();
        $user_id = get_the_author_id();
        $user = get_user_by('id',$user_id);
        $user_arr = array('id'=>$user->ID,'name'=>$user->first_name);
        $views = get_article_view_count($pid);
        $views = ($login_id == $user_id) ? $views: '';
        $share_count = get_sharing_count($pid);
        $att_id = get_post_thumbnail_id( $pid );
        if(!empty($att_id))
        {
          $thumb = get_the_post_thumbnail_url( $pid , 'feature-image');
          $full = get_the_post_thumbnail_url( $pid , 'full');
        }
        else
        {
          $thumb = "";
          $full = "";
        }
        $post_meta = get_post_meta( $pid, 'post_content', true );
        $personality_type = get_post_meta( $pid, 'personality_type', true );
        $post_categories = wp_get_post_categories( $pid );
        $cats = array();    
        foreach($post_categories as $c){
        $cat = get_category( $c );
        $cats[] = array('id' => $cat->term_id,'name' => $cat->slug,'title' => $cat->name);
        }
        $quiz_types = wp_get_post_terms($pid, 'quiz_type',  array("fields" => "all"));
        foreach($quiz_types as $quiz_type){
        $quiz_cats[] = array('id' => $quiz_type->term_id,'name' => $quiz_type->slug);
        }
        $media_arr = array('id'=>$att_id,'full_url'=>$full,'thumb_url'=>$thumb);
        //$date = human_time_diff( get_the_modified_time( 'U',$pid), current_time( 'timestamp' ) ) . " " . esc_html__( '[:en]ago[:ar]منذ[:]', 'boombox' );
        $date = get_the_date('d/m/Y');
        $mylisting[] = array(
              'id'=> $pid,
              'link'=> get_permalink($pid),
              'title'=>$title,
              'content'=>$content,
              'post_status'=>$post_status,
              'personality_type'=>$personality_type,
              'post_meta_content'=>$post_meta,
              'social_counts'=>$share_count,
              'post_views'=> $views,
              'featured_media'=> $media_arr,
              'categories'=>$cats,
              'quiz_type'=>$quiz_cats,
              'author'=> $user_arr,
              'date'=>$date
              );
      }
        echo json_encode(array('flag'=>true,'data'=>$mylisting));
    }
    else
    {
        echo json_encode(array(
          'flag'=>false,
          'message'=>'no quiz found for this user'
        ));
    }
  }
  else
  {
     echo json_encode(array(
        'flag'=>false,
        'message'=>'invalid User ID'
      ));
  }  
  exit();
}