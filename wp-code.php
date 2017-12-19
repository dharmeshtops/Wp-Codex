<?php 
/**
* @package : Wordpress Ready Code For Developement
* @author  : Az
**/

/*======================================================
  ==        1. Add Style In Wordpress by Hook         ==
/*======================================================*/
add_action( 'wp_enqueue_scripts', 'enqueue_scripts_css');
function enqueue_scripts_css()
{
	$css_dir     =  get_template_directory_uri().'/css/';
    $style_array = array(
    			"example.css",
    			'developer.css'
    			 );
    foreach ($style_array as $style) {
    	$ext    =  explode('.css', $style);
    	$handle = $ext[0];
    	wp_enqueue_style($handle, $css_dir.$style, array(),'', true);
    }
}
/*======================================================
  ==        2. Add Sripts In Wordpress by Hook        ==
/*======================================================*/
add_action( 'wp_enqueue_scripts', 'enqueue_scripts_js');
function enqueue_scripts_js()
{
	$js_dir       =  get_template_directory_uri().'/js/';
    $script_array = array(   			
    			'developer.js'
    			 );
    foreach ($script_array as $script) {
    	$ext    =  explode('.js', $script);
    	$handle = $ext[0];
    	wp_enqueue_script($handle, $js_dir.$script, array(),false, false);
    }
}
/*======================================================
  ==        3. Worpdress breadcrumb function          ==
/*======================================================*/
if (!function_exists('get_the_bredcrumb')) {
function get_the_bredcrumb(){
	global $post;
    $delimiter = '<i class="fa fa-angle-double-right"></i>';
    if (!is_front_page()) {
        $bredcrumb = '<a href="';
        $bredcrumb .= home_url();
        $bredcrumb .= '">' . __('Home', '');
        $bredcrumb .= "</a>" . $delimiter;
    }
    if (is_page() && !$post->post_parent) {
	    $bredcrumb .= get_the_title();
    } 
    if(is_singular('post-type')){
    	$bredcrumb .= '<a href="';
        $bredcrumb .= home_url().'/posttype-slug/';
        $bredcrumb .= '">' . __('post type name', '');
        $bredcrumb .= "</a>" . $delimiter;
        
    }
    if (is_category() || is_single()) {
        $categories = get_the_category();
        $ID = $categories[0]->cat_ID;
        $bredcrumb .= is_wp_error($cat_parents = get_category_parents($ID, TRUE, ' <span>/</span> ')) ? '' : '<span class="breadcrumb-categoris-holder">' . $cat_parents . '</span>';
            if (is_single()) {
                $bredcrumb .= get_the_title();
            }
    }
    else if (is_single()) {
        $bredcrumb .= get_the_title();
    }elseif (is_page() && $post->post_parent) {        
        $parent_id = $post->post_parent;
        $breadcrumbs = array();  
        while ($parent_id) {
            $page = get_page($parent_id);
            $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '" rel="v:url" property="v:title">' . get_the_title($page->ID) . '</a>';
            $parent_id = $page->post_parent;
        }
        $breadcrumbs = array_reverse($breadcrumbs);
        foreach ($breadcrumbs as $crumb) 
        $bredcrumb .= $crumb . '' . $delimiter;        
        $bredcrumb .= get_the_title();
    } 
    elseif (is_attachment()) {  
        $parent = get_post($post->post_parent);
        $cat = get_the_category($parent->ID);
        $cat = $cat[0];
         $bredcrumb .= is_wp_error($cat_parents = get_category_parents($cat, TRUE, '' . $delimiter . '')) ? '' : $cat_parents;
         $bredcrumb .= '<a href="' . get_permalink($parent) . '" rel="v:url" property="v:title">' . $parent->post_title . '</a>' . $delimiter;
         $bredcrumb .= get_the_title();
    } 
    elseif (is_archive()) {
      $bredcrumb .=   post_type_archive_title(false);
    } 
    elseif (is_search()) {
        
        $bredcrumb .= __('Search results for &ldquo;', '') . get_search_query() . '&rdquo;';
    } 
    elseif (is_tag()) {
        
        $bredcrumb .=  __('Tag &ldquo;', '') . single_tag_title('', false) . '&rdquo;';
    } 
    elseif (is_author()) {
        
        $userdata = get_userdata(get_the_author_meta('ID'));
        $bredcrumb .=  __('Author:', '') . ' ' . $userdata->display_name;
    } 
    elseif (is_day()) {
        
        $bredcrumb .=  '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a>' . $delimiter;
        $bredcrumb .=  '<a href="' . get_month_link(get_the_time('Y') , get_the_time('m')) . '">' . get_the_time('F') . '</a>' . $delimiter;
        $bredcrumb .=  get_the_time('d');
    } 
    elseif (is_month()) {
        
        $bredcrumb .=  '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a>' . $delimiter;
        $bredcrumb .=  get_the_time('F');
    } 
    elseif (is_year()) {
        
        $bredcrumb .=  get_the_time('Y');
    }
    if (get_query_var('paged')) $bredcrumb .=  ' (' . __('Page', '') . ' ' . get_query_var('paged') . ')';
    if (is_tax()) {
        $term = get_term_by('slug', get_query_var('term') , get_query_var('taxonomy'));
        $bredcrumb .=  '<span>' . $term->name . '</span>';
    }
    if ( is_404() ) {
    	$bredcrumb .=  "404 Error";
    }
    $before_bredcrumb = '<div class="sa-breadcrumb-wrapper '.$bredcrumb_align_class.'">';
    $after_bredcrumb = '</div>';
    $bredcrumb = $before_bredcrumb . $bredcrumb . $after_bredcrumb;

    echo  $bredcrumb;
}
}
/*======================================================
  ==        4. Worpdress plugin update unset hook     ==
/*======================================================*/
function filter_plugin_updates( $value ) {
    unset( $value->response['{plugin-folder-name}/{plugin-main-file}.php'] );
    return $value;
}
add_filter( 'site_transient_update_plugins', 'filter_plugin_updates' );

/*======================================================
  ==        5. Worpdress post column reorder hook     ==
/*======================================================*/

add_filter('manage_posts_columns', 'thumbnail_column');
function thumbnail_column($columns) {
  $new = array();
  foreach($columns as $key => $title) {
    if ($key=='author')
    $new['featured_image'] = 'Featured Image';
    $new[$key] = $title;
  }
  return $new;
}

/*======================================================
  ==    6. Worpdress post column add & and set value  ==
/*======================================================*/
/*method 1*/
add_filter('manage_edit-portfolio_columns', 'my_columns');
function my_columns($columns) {
    $columns['content_status'] = 'Content Status';
    return $columns;
}
/*method 2*/
add_filter('manage_posts_columns', 'bootpress_slider_columns');  
function bootpress_slider_columns($defaults) {  
    $defaults['featured_image'] = 'Featured Image'; 
    return $defaults;  
}
add_action( 'manage_posts_custom_column' , 'custom_columns_frimg', 10, 2 );
function custom_columns_frimg( $column, $post_id ) {
    switch ( $column ) {
        case 'featured_image':
        $fimg = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) ); 
        $fimage = '<img src="'.$fimg.'" width=80 height=80px/>';
           echo $fimage;
            break;
    }
}
/*======================================================
  ==    7. Worpdress post column sortable             ==
/*======================================================*/
add_filter( 'manage_edit-{post-type}_sortable_columns', 'my_sortable_views_column' );
function my_sortable_views_column( $columns ) {
$columns['view_counts'] = 'view_counts';
    return $columns;
}

/*======================================================
  ==    7. Worpdress Create new table codex           ==
/*======================================================*/

add_action('after_setup_theme','my_table_create_func');
function my_table_create_func(){
	global $wpdb;
	$table_name = $wpdb->prefix . '{table-name}';
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          device_token varchar(255) NOT NULL,
          device_type varchar(255) NOT NULL,
          user_id  int(11)  NULL,
          PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
/*======================================================
  ==    7. Worpdress Custom table INSERT Query        ==
/*======================================================*/
global $wpdb;
$table_name = $wpdb->prefix."{table-name}";
$insert = $wpdb->insert($table_name, array(
                '{id}' => 1,
                '{amount}' => 1.5,
                '{name}' => 'dharmesh',
            ),array('%d','%f','%s')   // d = decimal, f= float, s = strng
        );

/*======================================================
  ==    8. Worpdress script enqueued or not to add    ==
/*======================================================*/
$handle = 'jquery.scrollTo1.js';
   $list = 'enqueued';
     if (wp_script_is( $handle, $list )) {
       return;
     } else {
       wp_register_script( 'jquery.scrollTo1.js', get_theme_file_uri( '/assets/js/jquery.scrollTo1.js' ), array( 'jquery' ), '2.1.2', true );
       wp_enqueue_script( 'jquery.scrollTo1.js' );
     }
  
/*======================================================
  ==    9. PHP CSV / EXCEL File Generation            ==
/*======================================================*/
$args = array(
  'post_type'=> array( 'post', 'recipes', 'learning_article'),
  'posts_per_page'=>'5',
  'orderby'        => 'ID',
  'order'          => 'ASC',
  'post_status' => array('any')
  );
$wp_Query = new WP_Query($args);
$i = 1;
while ($wp_Query->have_posts()) {
  $wp_Query->the_post();
  $title = html_entity_decode(get_the_title());
  $arr[] = array(get_the_ID(),$title,get_post_status(),get_post_type(),$sku,$perlink);
  $i++;
}
        $filename = 'lists-SKU-'.DATE('dmyhis').'.csv';
        $fp = fopen('php://output', 'w');
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$filename);
        foreach ($arr as $fields) {
            fputcsv($fp, $fields);
        }
fclose($fp);
/*================================================================
  ==    10. PHP Function Args values use in child or nested func==
/*================================================================*/
function myfunc_With_args($one,$two,$three){
  $use = function() use ($one,$two,$three)
  {
    echo $one;
  }
  $use();
}
/*================================================================
  ==    11. WORDPRESS Add SVG Action Hook for Allow             ==
/*================================================================*/
function add_file_types_to_uploads($file_types){
    $new_filetypes = array();
    $new_filetypes['svg'] = 'image/svg+xml';
    $file_types = array_merge($file_types, $new_filetypes );
    return $file_types;
}
add_action('upload_mimes', 'add_file_types_to_uploads');  
/*================================================================
  ==    12. WORDPRESS SOCIAL SHARING DYNAMIC FUNCTION           ==
/*================================================================*/
function single_property_share($title,$url,$desc,$img){ 
        $sharehtml  = '<div class="sa-share-title"> Share this: </div><ul>';
        $sharehtml .= '<li><a data-url="'.$url.'" href="http://www.facebook.com/sharer.php?u='.$url.'&amp;title='.esc_html($title).'&amp;display=popup&amp;picture='.$img.'&amp;description='.esc_html($desc).'" onclick="openWin(this.href); return false;" target="_blank"><i class="fa fa-facebook"></i></a></li>';
        $sharehtml .= '<li><a href="http://twitter.com/share?text='.esc_html($title).' - ' . $url.'" title="Share on Twitter" rel="nofollow" data-url="'.$url.'" onclick="openWin(this.href); return false;"><i class="fa fa-twitter"></i></a></li>';
        $sharehtml .= '<li><a href="http://www.linkedin.com/shareArticle?mini=true&url='.$url.'&title='.esc_html($title).'&summary='.esc_html($desc).'" onclick="openWin(this.href); return false;"><i class="fa fa-linkedin"></i></a></li>';
        $sharehtml .= '<li><a href="https://plus.google.com/share?url='.$url.'" onclick="openWin(this.href); return false;"><i class="fa fa-google-plus"></i></a></li>';
        $sharehtml .= '<li><a href="http://www.pinterest.com/pin/create/button/?url='.$url.'&media='.$img.'&description='.esc_html($desc).'" data-pin-do="buttonPin" data-pin-config="above" onclick="openWin(this.href); return false;"><i class="fa fa-pinterest"></i></a></li>';
        $sharehtml .= '</ul>';
        return $sharehtml;
}
/*=======JAVASCRIPT FUNCTION FOR POPUP SHARING=======*/
?>
<script type="text/javascript">
  function openWin(url)
  {
    w = window.open(url, '_blank', width = 600, height = 600, scrollbars = 'yes', menubar = 'no', resizable = 'yes', toolbar = 'no', 'false');
    w.focus();
  }
</script>

<?php
/*================================================================
  ==    13.         WORDPRESS PAGINATION FUNCTION                ==
/*================================================================*/
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$wp_query_args = array(
  'post_type' => 'album',
  'posts_per_page' => '-1',
  'orderby' => 'ID',
  'order' => 'ASC',
  'paged' => $paged
);
$wp_query = new WP_Query($wp_query_args);
$total_pages = $wp_query->max_num_pages;
function wp_pagination($wp_query,$total_pages){
  if ($total_pages > 1) {
    $big = 999999999;
    $nav_args = paginate_links(array(
      'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))) ,
      'format' => '/page/%#%',
      'current' => max(1, get_query_var('paged')) ,
      'total' => $total_pages,
      'show_all' => false,
      'prev_text' => '<i class="fa fa-angle-double-left"></i>',
      'next_text' => '<i class="fa fa-angle-double-right"></i>',
      'type' => 'array'
    ));
    if (is_array($nav_args)) {
      $paged = (get_query_var('paged') == 0) ? 1 : get_query_var('paged');
      echo '<ul class="pagination">';
      foreach($nav_args as $page) {
        echo "<li>$page</li>";
      }

      echo '</ul>';
    }
    wp_reset_query();
  }
}
/*================================================================
  ==    14.      WORDPRESS ADD Menu on Tops Menu bar HOOK        ==
/*================================================================*/
function dwwp_add_google_link() {
  global $wp_admin_bar;
  $wp_admin_bar->add_menu( array(
    'id'    => 'google_analytics',
    'title' => 'Google Analytics',
    'href'  => 'http://google.com/analytics/'
  ) );
}
add_action( 'wp_before_admin_bar_render', 'dwwp_add_google_link' );

/*================================================================
  ==    15.      PHP Object to Array conversation function      ==
/*================================================================*/
function object_to_array($data)
{
    if (is_array($data) || is_object($data))
    {
        $result = array();
        foreach ($data as $key => $value)
        {
            $result[$key] = object_to_array($value);
        }
        return $result;
    }
    return $data;
}
/*================================================================
  ==    17.  PHP Request URI                                    ==
/*================================================================*/
function requesturl(){
  $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE ? 'http' : 'https';
  $host     = $_SERVER['HTTP_HOST'];
  $script   = $_SERVER['REQUEST_URI'];
  $actual_link = $protocol . '://' . $host . $script;
  return $actual_link;
}
/*================================================================
  ==    17.  Wordpress All Action hooks print for every pages   ==
/*================================================================*/
add_action( 'login_form', 'myplugin_add_login_fields' );
function myplugin_add_login_fields() {
  echo "<pre>";
  print_r( $GLOBALS['wp_actions'] );
}
/*================================================================
  ==    18.  Wordpress Disable login hints  				    ==
/*================================================================*/
function no_wordpress_errors(){
  return 'What the heck are you doing?! Back off!';
}
add_filter( 'login_errors', 'no_wordpress_errors' );
/*================================================================
  ==    19.  Wordpress Security TIPS                            ==
/*================================================================*/
    /*
    1. Set file and folder permissions to correct values
    		    folders = 755
    			files = 644
    2. Disable login hints
    3. Prevent directory browsing (indexing) of your WordPress website
    			Options All -Indexes
    4. Don’t use “Admin” as your administrator username
    5. Pick strong passwords (long, with numbers, capital letters, and symbols)
    6. Use 2-factor authentication for login
    7. Download plugins only from known resources
    8. Keep your WordPress environment updated
    9. Keep your WordPress clean						
    */
/*================================================================
  ==    19.  WP html Replace with live images url to own url==
/*================================================================*/
$post_id = wp_insert_post( $args );
if( !empty($post_id) && is_numeric($post_id)){
  $pattern = '@src="([^"]+)"@';
  preg_match_all($pattern, $article_longdesc, $out);
  foreach ($out[1] as $value) {
    $image_path_url =   pathinfo($value); 
    $image_name =  $image_path_url['basename'];
    if( !empty($image_name) && $image_name != "NULL" && !empty($value)){
      $upload_dir       = wp_upload_dir(); // Set upload folder
      $image_data       = file_get_contents($value); // Get image data
      $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
      $filename         = basename( $unique_file_name );
      if( wp_mkdir_p( $upload_dir['path'] ) ) {
        $file = $upload_dir['path'] . '/' . $filename;
      } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
      }
      file_put_contents( $file, $image_data );
      $wp_filetype = wp_check_filetype( $filename, null );
      $attachment = array(
      'post_mime_type' => $wp_filetype['type'],
      'post_title'     => sanitize_file_name( $filename ),
      'post_content'   => '',
      'post_status'    => 'inherit'
      );
      $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
      wp_update_attachment_metadata( $attach_id, $attach_data );
    }
    $modified_string = wp_get_attachment_url($attach_id);
    $article_longdesc = str_replace($value, $modified_string, $article_longdesc);
  }
  $update_article = array(
  'ID'           => $post_id,
  'post_content' => $article_longdesc,
  ); 
  wp_update_post( $update_article );
}
/*================================================================
  ==    20.  WP html Replace with live images url to own url==
/*================================================================*/
$args = array('post_type'=>'page','posts_per_page'=>-1);
$wpquery = new WP_Query($args);
$j = 1;
if($wpquery->have_posts())
{
  $i = 1;
  while($wpquery->have_posts())
  {
    $wpquery->the_post();
    $id = get_the_ID();
      $content = get_the_content();
      $pattern = '@href="([^"]+)"@';
      preg_match_all($pattern, $content, $forloop);
      $forloopdata = $forloop[1];
      if(!empty($forloopdata) && is_array($forloopdata))
      {
        foreach ($forloopdata as $wpurl)
        {
          $host = parse_url($wpurl,PHP_URL_HOST);
          if(!empty($host) && $host == 'www.tenerifepropertyshop.com')
          {
            echo "<pre>";
            echo $wpurl;
            echo "</pre>";
            if (strpos($wpurl, 'http://www.tenerifepropertyshop.com/ref.php?ref=') !== false) {
              $newurl = str_replace('http://www.tenerifepropertyshop.com/ref.php?ref=', 'http://topsdemo.org/spaincrm/v1.1/property-view/', $wpurl);
              if(!empty($newurl))
              {
                $content = str_replace($wpurl, $newurl, $content);
                $update_article = array(
                  'ID'=> $id,
                  'post_content' => $content,
                  ); 
                $post_id = wp_update_post( $update_article );
                update_post_meta($id,'_sa_replace_urls','all');
                
              }
              $i++;
            }
            $j++;
          }
        }
      }
  }
}
/*================================================================
  ==    21.       FACEBOOK SHARE USING CUSTOM DATAS             ==
/*================================================================*/
function facebook_share($app_id,$href,$title,$desc,$img){
  ?>
  <script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.10&appId=<?php echo $app_id; ?>";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<script type="text/javascript">
  FB.ui({
          method: 'share_open_graph',
          action_type: 'og.shares',
          display: 'popup',
          action_properties: JSON.stringify({
            object: {
              'og:url': <?php echo $href; ?>,
              'og:title': <?php echo $title; ?>,
              'og:description': <?php echo $desc; ?>,
              'og:image': <?php echo $img; ?>,
            }
          })
        },function(response) {});
</script>
}
<?php
/*================================================================
  ==    22. Converting Currency Numbers to words currency format==
/*================================================================*/
function currency_to_word($number){
      $no = round($number);
       $point = round($number - $no, 2) * 100;
       $hundred = null;
       $digits_1 = strlen($no);
       $i = 0;
       $str = array();
       $words = array('0' => '', '1' => 'one', '2' => 'two',
        '3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
        '7' => 'seven', '8' => 'eight', '9' => 'nine',
        '10' => 'ten', '11' => 'eleven', '12' => 'twelve',
        '13' => 'thirteen', '14' => 'fourteen',
        '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
        '18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty',
        '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
        '60' => 'sixty', '70' => 'seventy',
        '80' => 'eighty', '90' => 'ninety');
       $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
       while ($i < $digits_1) {
         $divider = ($i == 2) ? 10 : 100;
         $number = floor($no % $divider);
         $no = floor($no / $divider);
         $i += ($divider == 10) ? 1 : 2;
         if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number] .
                " " . $digits[$counter] . $plural . " " . $hundred
                :
                $words[floor($number / 10) * 10]
                . " " . $words[$number % 10] . " "
                . $digits[$counter] . $plural . " " . $hundred;
         } else $str[] = null;
      }
      $str = array_reverse($str);
      $result = implode('', $str);
      $points = ($point) ?
        "." . $words[$point / 10] . " " . 
              $words[$point = $point % 10] : '';
      echo $result . "Rupees  " . $points . " Only";
  }
/*================================================================
  ==    23. Add Parameters using Jquery to Site URL             ==
/*================================================================*/  
?>
<script type="text/javascript">
function addParameter(url, param, value) {
    var val = new RegExp('(\\?|\\&)' + param + '=.*?(?=(&|$))'),
        parts = url.toString().split('#'),
        url = parts[0],
        hash = parts[1]
    qstring = /\?.+$/,
        newURL = url;
    if (val.test(url)) {
        newURL = url.replace(val, '$1' + param + '=' + value);
    } else if (qstring.test(url)) {
        newURL = url + '&' + param + '=' + value;
    } else {
        newURL = url + '?' + param + '=' + value;
    }
    if (hash) {
        newURL += '#' + hash;
    }
    return newURL;
}
</script>
<?php 
