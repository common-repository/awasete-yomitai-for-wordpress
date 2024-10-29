<?php
/*
 Plugin Name: Awasete Yomitai for Wordpress
 Plugin URI: http://wordpress.org/extend/plugins/awasete-yomitai-for-wordpress/
 Description: Awasete Yomitai for Wordpress. Wordpress 2.8+
 Version: 1.1.1
 Author: makoto_kw
 Author URI: http://www.makotokw.com/
 */
if (class_exists("WP_Widget")) {
	
	class AwaseteYomitai_Widget extends WP_Widget {
		const VERSION = '1.1.1';
		
		var $defaults = array('displaycount' =>10, 'target'=>'_blank', 'poweredby' => false );
		static function init() {
			register_widget('AwaseteYomitai_Widget');
		}
		
		static $current = null;
		function AwaseteYomitai_Widget() {
			parent::WP_Widget('awaste_yomitai', 'あわせて読みたい', array('description'=>'あわせて読みたい'));
			if (!self::$current) {
				self::$current = $this;
				if (!is_admin()) {
					// add style/script
					$dir = end(explode(DIRECTORY_SEPARATOR, dirname(__FILE__)));
					$wpurl = (function_exists('site_url')) ? site_url() : get_bloginfo('wpurl');
					$url = $wpurl.'/wp-content/plugins/'.$dir.'/';
					wp_enqueue_style('awasete-yomitai', $url.'awasete-yomitai.css',array(),self::VERSION);
					add_action('wp_footer', array($this,'footer'));
				}
			}
		}
	
		function widget($args, $instance) {
			extract($args);
			$instance = wp_parse_args((array)$instance, $this->defaults);
			if (empty($instance['title'])) $instance['title'] = $this->name;
			if (empty($instance['target'])) $instance['target'] = $this->defaults['target'];
			if (empty($instance['url'])) $instance['url'] = get_bloginfo('url');
			?>
				<?php echo $before_widget; ?>
					<?php echo $before_title. $instance['title']. $after_title; ?>
					<ul id="awasete_blog_list">
						<li class="awasete_loading">LOADING...</li>
					</ul>
<?php if ($instance['poweredby']):?>
					<div class="awasete_footer">powered by <a href="http://awasete.com/show.phtml?u=<?php echo urlencode($instance['url'])?>" target="<?php echo $instance['target']?>" title="awasate.com">awasate.com</a></div>
<?php endif ?>
				<?php echo $after_widget; ?>
			<?php
			$this->instance = $instance;
		}
	
		function update($new_instance, $old_instance) {
			$new_instance['poweredby'] = $new_instance['poweredby'] ? 1 : 0;
			return $new_instance;
		}
	
		function form($instance) {
			$instance = wp_parse_args((array)$instance, $this->defaults);
			$title = esc_attr($instance['title']);
			$url = @$instance['url'];
			if (empty($instance['target'])) $instance['target'] = $this->defaults['target'];
			$displaycount = intval($instance['displaycount']);
			if ($displaycount<=0) $displaycount = $this->defaults['displaycount'];
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('url'); ?>"><?php _e('Url:'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo $url; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('displaycount'); ?>"><?php _e('Display Count:'); ?></label>
				<input id="<?php echo $this->get_field_id('displaycount'); ?>" name="<?php echo $this->get_field_name('displaycount'); ?>" type="text" size="3" value="<?php echo $displaycount; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('target'); ?>"><?php _e('Link Target:'); ?></label>
				<input id="<?php echo $this->get_field_id('target'); ?>" name="<?php echo $this->get_field_name('target'); ?>" type="text" size="8" value="<?php echo $instance['target']; ?>" />
			</p>
			<input class="checkbox" type="checkbox" <?php checked($instance['poweredby'], true) ?> id="<?php echo $this->get_field_id('poweredby'); ?>" name="<?php echo $this->get_field_name('poweredby'); ?>" />
			<label for="<?php echo $this->get_field_id('poweredby'); ?>"><?php _e('Show PoweredBy'); ?></label><br />
			<?php 
		}
	
		function footer() {
			$url = $this->instance['url'];
			$max = $this->instance['displaycount'];
			if (empty($max) || $max<=0) $max = $this->defaults['displaycount'];
			?>
<script type="text/javascript">
function awasete_yomitai(blogs) {
	//var blogs = [{titile:,url:,favicon:,more:},...];
	var el = document.getElementById('awasete_blog_list');
	if (el) {
		var a = [], target = '<?php echo $this->instance['target']?>', max = <?php echo $max?>, len = Math.min(max,blogs.length);
		for (var i=0; i<len; i++) {
			var b = blogs[i];
			a.push('<li style="background:transparent url('+b.favicon+') no-repeat left" class="awasete_blog"><a href='+b.url+' target="'+target+'" title="'+b.title+'">'+b.title+'<\/a><\/li>');
		}
		el.innerHTML = a.join('');
	}
}
</script>
<script type="text/javascript" src="http://api.awasete.com/showjson.phtml?u=<?php echo urlencode($url)?>" defer="defer"></script>
<?php
		}
	}
	
	add_action('init', array('AwaseteYomitai_Widget','init'), 1);
}
