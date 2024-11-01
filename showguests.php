<?php
/*
Plugin Name: Showguests
Plugin URI: http://www.tiandiyoyo.com
Description: Display guests who visited your website recently.
Version: 1.02
Author: Tiandi
Author URI: http://www.tiandiyoyo.com
*/

function Showguestspanel() { ?>
	<div class="wrap">
	    <?php screen_icon(); ?>
	    <h2>Showguests Settings</h2>			
	    <form method="post" action="" id="showguestsform">
            <p><div style="background:#5CA12F; border:1px solid gray;width: 800px; padding:5px; color:#fff;">Showguests is a plugin for showing the newest guests on your blog. Please visit <a href="http://www.tiandiyoyo.com" target=_blank>http://www.tiandiyoyo.com</a> for more information.</div><p>How to use: Insert [sgshow] into your post/page to show it or use widget tools to load it on the slidebar!<br><br>
	        <?php
            echo "Show the newest";
	            $abc = $_POST['showguestcounts'];
				if (!empty($abc)&& check_admin_referer('check-update'))  {
					update_option('showguestcounts',$abc); ?> 
					<input type="text" name="showguestcounts" id="showguestcounts" value= <?php echo $abc; ?> size=3 />guests.
				<?php } else if(get_option('showguestcounts') == null) {?>
                    <input type="text" name="showguestcounts" id="showguestcounts" value = 50 size=3 />guests.
                <?php } else { ?>
				<input type="text" name="showguestcounts" id="showguestcounts" value= <?php echo get_option('showguestcounts') ;?>  size=3 />guests.
				<?php  } 
			echo "<br>Do not show ID = ";
				$abc = $_POST['showguestmyname'];
				if (!empty($abc)&& check_admin_referer('check-update'))  {
					update_option('showguestmyname',$abc); ?> 
					<input type="text" name="showguestmyname" id="showguestmyname" value= <?php echo $abc; ?> size=8 />
				<?php } else if(get_option('showguestmyname') == null) {?>
                    <input type="text" name="showguestmyname" id="showguestmyname" size=8 />
                <?php } else { ?>
				<input type="text" name="showguestmyname" id="showguestmyname" value= <?php echo get_option('showguestmyname') ;?>  size=8 />
				<?php  } 
				echo "(Only for one id. Please input your admin account here.)";
				submit_button(); 
			  wp_nonce_field('check-update'); 
			   ?>
	    </form>
	</div>
	<?php
}
function Showguests_check_guests_info() {
		
		$guestname = $_COOKIE["comment_author_" . COOKIEHASH];
		$myname = get_option('showguestmyname');
		if(empty($myname)) $myname = "admin";
		if(!empty($guestname) && $guestname != $myname) {
			global $wpdb;
			$counts = $wpdb->get_results("SELECT COUNT(comment_author) AS cnt, comment_author, comment_author_url, comment_author_email FROM $wpdb->comments where comment_author = '" . $guestname . "' and comment_approved = 1 GROUP BY comment_author_email");
			foreach($counts as $count){

			$guesturl = $count->comment_author_url;
			$guestavatar = get_avatar($count->comment_author_email, 50);
			$guestenter = date("Y-m-d H:i", time() + 8 * 3600);
			if ($guesturl == "") 
				$guesturl = "http://www.tiandiyoyo.com";
			$guestinfo = get_option("Showguests_guests_info");
			if(!empty($guestinfo)) {
				$myarray = get_option("Showguests_guests_info");
				array_unshift($myarray,$guestname,$guesturl,$guestavatar,$guestenter,$count->cnt);
				$temparray = array_slice($myarray,5);
				$tmp = array_search($guestname,$temparray);
				if($tmp > -1)  
					array_splice($myarray,5+$tmp,5);
			}
			else {
				$myarray = array($guestname,$guesturl,$guestavatar,$guestenter,$count->cnt);
			}
			$myarray = array_slice($myarray,0,5*get_option('showguestcounts'));
			update_option("Showguests_guests_info",$myarray);
			break;
			}
		}
}
function Showguests_show_guests_info($count) {
		$guestinfo = get_option("Showguests_guests_info");
		if(!empty($count))  {
			if($count > 200) 
				$count = 200;
			else if($count > count($guestinfo)/5)
				$count = count($guestinfo)/5;
		}
		else 
			$count = count($guestinfo)/5;
		if(!empty($guestinfo)) {
			for( $i=0;$i<$count*5;$i=$i+5) {
				if(empty($guestinfo[$i+4]) )
					$guestinfo[$i+4] = 1;
				$guester .= "<div class = 'showguests' ><a href=". $guestinfo[$i+1] . " title='" . $guestinfo[$i] .  " visited at " . $guestinfo[$i+3] . "(with ". $guestinfo[$i+4] . " comments)' target=_blank rel='nofollow'>" . $guestinfo[$i+2] . "</a></div>";
			}
			echo $guester;
		}
					
}

class showguestswidget extends WP_Widget {
	function showguestswidget() {
		wp_register_style('myshowguestscss', WP_PLUGIN_URL . '/showguests/my.css');
        wp_enqueue_style('myshowguestscss');
		$widgetinfo = array('classname'=>'showguestswt','description'=>'Show newest guests');
        $this->WP_Widget(false,'newest guests',$widgetinfo,null);
	}

	function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title',empty($instance['title']) ? '&nbsp;' : $instance['title']);
		$showPosts = empty($instance['showunit']) ? 50 : $instance['showunit'];
  
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo "<div class = 'showguestscss'>";
		echo "<ul><li>";
		Showguests_show_guests_info($showPosts);
		echo "</li></ul></div>";
		echo $after_widget;
		
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['showunit'] = strip_tags(stripslashes($new_instance['showunit']));
		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args((array)$instance,array('title'=>'newest guests','showunit'=>50));
		$title = htmlspecialchars($instance['title']);
		$showunit = htmlspecialchars($instance['showunit']);
		echo '<p style="text-align:left;"><label for="'.$this->get_field_name('title').'">Title:<input style="width:200px;" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.$title.'" /></label></p>';
        echo '<p style="text-align:left;"><label for="'.$this->get_field_name('showunit').'">Show:<input style="width:200px;" id="'.$this->get_field_id('showunit').'" name="'.$this->get_field_name('showunit').'" type="text" value="'.$showunit.'" /></label></p>';
	}
}

add_shortcode('sgcheck','Showguests_check_guests_info');
add_shortcode('sgshow','Showguests_show_guests_info');


function showguests_admin_actions() {
    add_options_page("Showguests", "Showguests", 1, "Showguestsinfo", "Showguestspanel"); 
}

function showguestsinit(){
	register_widget('showguestswidget');
}
add_action( 'wp_footer', 'Showguests_check_guests_info', 99 );

add_action('admin_menu', 'showguests_admin_actions');  
add_action('widgets_init','showguestsinit');
?>