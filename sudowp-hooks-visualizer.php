<?php
/**
 * Plugin Name: SudoWP Hooks Visualizer
 * Plugin URI:  https://sudowp.com
 * Description: A secure, developer-focused tool to visualize WordPress Action and Filter hooks in real-time. Maintained by SudoWP.
 * Version:     1.3.0 (SudoWP Edition)
 * Author:      SudoWP (Maintained by WP Republic)
 * Author URI:  https://sudowp.com
 * License:     GPLv2 or later
 * Text Domain: sudowp-hooks-visualizer
 * Domain Path: /localization/
 * * Based on "Simply Show Hooks" by Stuart O'Brien & cxThemes.
 */

defined( 'ABSPATH' ) || exit; // Modern exit

class SudoWP_Hooks_Visualizer {
	
	private $status;
	private $all_hooks = array();
	private $recent_hooks = array();
	private $ignore_hooks = array();
	private $doing = 'collect';

	public static function get_instance() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new self();
			$instance->init();
		}
		return $instance;
	}
	
	public function __construct() {}
	
	function init() {
        // Allow developers to ignore noisy hooks via filter
		$this->ignore_hooks = apply_filters( 'sudowp_hooks_ignore', array(
			'attribute_escape',
			'body_class',
			'the_post',
			'post_edit_form_tag',
            'gettext',
            'gettext_with_context',
            'translations_api'
		) );

		add_action( 'plugins_loaded', array( $this, 'load_translation' ) );
		
        // Securely set status
        $this->set_active_status();
		
		$this->attach_hooks();
		add_action( 'init', array( $this, 'plugin_init' ) );
	}
	
	/**
	 * Security Fix: Sanitize inputs from Cookies and Requests
	 */
	public function set_active_status() {
        $cookie_name = 'sudowp_hooks_status';
        
		if ( ! isset( $this->status ) ) {
            // Check Request (GET/POST) first
			if ( isset( $_REQUEST['sudowp-hooks'] ) ) {
                $status_val = sanitize_key($_REQUEST['sudowp-hooks']); // Sanitize!
                
                // Only allow specific values
                if (in_array($status_val, ['off', 'show-action-hooks', 'show-filter-hooks'])) {
                    setcookie( $cookie_name, $status_val, time()+3600*24*30, '/', COOKIE_DOMAIN, is_ssl(), true ); // Secure Cookie
                    $this->status = $status_val;
                }
			}
			elseif ( isset( $_COOKIE[$cookie_name] ) ) {
                $cookie_val = sanitize_key($_COOKIE[$cookie_name]);
				$this->status = $cookie_val;
			}
			else {
				$this->status = 'off';
			}
		}
	}
	
	public function attach_hooks() {
		if ( $this->status == 'show-action-hooks' || $this->status == 'show-filter-hooks' ) {
			add_filter( 'all', array( $this, 'hook_all_hooks' ), 100 );
			add_action( 'shutdown', array( $this, 'notification_switch' ) );
			add_action( 'shutdown', array( $this, 'filter_hooks_panel' ) );
		}
	}
	
	public function detach_hooks() {
		remove_filter( 'all', array( $this, 'hook_all_hooks' ), 100 );
		remove_action( 'shutdown', array( $this, 'notification_switch' ) );
		remove_action( 'shutdown', array( $this, 'filter_hooks_panel' ) );
	}
	
	/*
	 * Admin Menu top bar
	 */
	function admin_bar_menu( $wp_admin_bar ) {
		$this->detach_hooks();
		
		$url = remove_query_arg( 'sudowp-hooks' );
        
		if ( 'show-action-hooks' == $this->status ) {
			$title 	= __( 'Stop Showing Action Hooks' , 'sudowp-hooks-visualizer' );
			$href 	= add_query_arg( 'sudowp-hooks', 'off', $url );
			$css 	= 'sudowp-hooks-on sudowp-hooks-normal';
		}
		else {
			$title 	= __( 'Show Action Hooks' , 'sudowp-hooks-visualizer' );
			$href 	= add_query_arg( 'sudowp-hooks', 'show-action-hooks', $url );
			$css 	= '';
		}
		
		$wp_admin_bar->add_menu( array(
			'title'		=> '<span class="ab-icon"></span><span class="ab-label">' . __( 'SudoWP Hooks' , 'sudowp-hooks-visualizer' ) . '</span>',
			'id'		=> 'sudowp-main-menu',
			'parent'	=> false,
			'href'		=> $href,
		) );
        
		$wp_admin_bar->add_menu( array(
			'title'		=> $title,
			'id'		=> 'sudowp-hooks-viz',
			'parent'	=> 'sudowp-main-menu',
			'href'		=> $href,
			'meta'		=> array( 'class' => $css ),
		) );
        
		if ( $this->status=="show-filter-hooks" ) {
			$title	= __( 'Stop Showing Action & Filter Hooks' , 'sudowp-hooks-visualizer' );
			$href 	= add_query_arg( 'sudowp-hooks', 'off', $url );
			$css 	= 'sudowp-hooks-on sudowp-hooks-sidebar';
		}
		else {
			$title	= __( 'Show Action & Filter Hooks' , 'sudowp-hooks-visualizer' );
			$href 	= add_query_arg( 'sudowp-hooks', 'show-filter-hooks', $url );
			$css 	= '';
		}
		
		$wp_admin_bar->add_menu( array(
			'title'		=> $title,
			'id'		=> 'sudowp-show-all-hooks',
			'parent'	=> 'sudowp-main-menu',
			'href'		=> $href,
			'meta'		=> array( 'class' => $css ),
		) );
		
		$this->attach_hooks();
	}
	
	function add_builder_edit_button_css() {
		?>
		<style>
		#wp-admin-bar-sudowp-main-menu .ab-icon:before{
			font-family: "dashicons" !important;
			content: "\f323" !important;
			font-size: 16px !important;
		}
		</style>
		<?php
	}

	function notification_switch() {
		$this->detach_hooks();
		$url = add_query_arg( 'sudowp-hooks', 'off' );
		?>
		<a class="sudowp-notification-switch" href="<?php echo esc_url( $url ); ?>">
			<span class="sudowp-notification-indicator"></span>
			<?php echo _e( 'Stop Showing Hooks' , 'sudowp-hooks-visualizer' ); ?>
		</a>
		<?php
		$this->attach_hooks();
	}
	
	function plugin_init() {
		if (
				! current_user_can( 'manage_options' ) || // Restrict to Admins
				! $this->plugin_active()
			) {
			$this->status = 'off';
			return;
		}
		
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_script' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu'), 90 );
		add_action( 'wp_print_styles', array( $this, 'add_builder_edit_button_css' ) );
		add_action( 'admin_print_styles', array( $this, 'add_builder_edit_button_css' ) );

		if ( $this->status == 'show-action-hooks' || $this->status == 'show-filter-hooks' ) {
			add_action( 'admin_head', array( $this, 'render_head_hooks'), 100 );
			add_action( 'wp_head', array( $this, 'render_head_hooks'), 100 );
			add_action( 'login_head', array( $this, 'render_head_hooks'), 100 );
			add_action( 'customize_controls_print_scripts', array( $this, 'render_head_hooks'), 100 );
		}
	}
	
	public function enqueue_script() {
        // Updated paths to match SudoWP naming convention
		wp_register_style( 'sudowp-hooks-css', plugins_url( 'assets/css/sudowp-hooks-main.css', __FILE__ ), '', '1.3.0', 'screen' );
		wp_enqueue_style( 'sudowp-hooks-css' );
	}
	
	public function load_translation() {
		load_plugin_textdomain( 'sudowp-hooks-visualizer', false, dirname( plugin_basename( __FILE__ ) ) . '/localization/' );
	}
	
	function render_head_hooks() {
		$this->render_hooks();
		$this->doing = 'write';
	}
	
	function render_hooks() {
		foreach ( $this->all_hooks as $nested_value ) {
			if ( 'action' == $nested_value['type'] ) {
				$this->render_action( $nested_value );
			}
		}
	}
	
	public function hook_all_hooks( $hook ) {
		global $wp_actions;
		if ( ! in_array( $hook, $this->recent_hooks ) ) {
			if ( isset( $wp_actions[$hook] ) ) {
				$this->all_hooks[] = array( 'ID' => $hook, 'callback' => false, 'type' => 'action' );
			}
			else {
				$this->all_hooks[] = array( 'ID' => $hook, 'callback' => false, 'type' => 'filter' );
			}
		}
		
		if ( isset( $wp_actions[$hook] ) && !in_array( $hook, $this->recent_hooks ) && !in_array( $hook, $this->ignore_hooks ) ) {
			if ( 'write' == $this->doing ) {
				$this->render_action( end( $this->all_hooks ) );
			}
		}
		
		$this->recent_hooks[] = $hook;
		if ( count( $this->recent_hooks ) > 100 ) {
			array_shift( $this->recent_hooks );
		}
	}
	
	function render_action( $args = array() ) {
		global $wp_filter;
		$nested_hooks = ( isset( $wp_filter[ $args['ID'] ] ) ) ? $wp_filter[ $args['ID'] ] : false ;
		
		$nested_hooks_count = 0;
		if ( $nested_hooks ) {
			foreach ($nested_hooks as $key => $value) {
				$nested_hooks_count += count($value);
			}
		}
		?>
		<span style="display:none;" class="sudowp-hook sudowp-hook-<?php echo esc_attr($args['type']); ?> <?php echo ( $nested_hooks ) ? 'sudowp-hook-has-hooks' : '' ; ?>" >
			<?php if ( 'action' == $args['type'] ) { ?> <span class="sudowp-hook-type">A</span> <?php } 
             else if ( 'filter' == $args['type'] ) { ?> <span class="sudowp-hook-type">F</span> <?php } ?>
			
			<?php echo esc_html($args['ID']); ?>
			
			<?php if ( $nested_hooks_count ) { ?>
				<span class="sudowp-hook-count"><?php echo intval($nested_hooks_count); ?></span>
			<?php } 
            
			if ( isset( $wp_filter[$args['ID']] ) ):
				$nested_hooks = $wp_filter[$args['ID']];
				if ( $nested_hooks ): ?>
					<ul class="sudowp-hook-dropdown">
						<li class="sudowp-hook-heading">
							<strong><?php echo esc_html($args['type']); ?>:</strong> <?php echo esc_html($args['ID']); ?>
						</li>
						<?php
						foreach ( $nested_hooks as $nested_key => $nested_value ) :
							?>
							<li class="sudowp-priority">
								<span class="sudowp-priority-label"><strong>Priority:</strong> <?php echo intval($nested_key); ?></span>
							</li>
							<?php
							foreach ( $nested_value as $nested_inner_key => $nested_inner_value ) :
								?>
								<li>
									<?php
									if ( isset($nested_inner_value['function']) && is_array( $nested_inner_value['function'] ) && count( $nested_inner_value['function'] ) > 1 ):
										?>
										<span class="sudowp-function-string">
											<?php
											$classname = false;
											if ( is_object( $nested_inner_value['function'][0] ) ) {
												$classname = get_class($nested_inner_value['function'][0] );
											} elseif ( is_string( $nested_inner_value['function'][0] ) ) {
												$classname = $nested_inner_value['function'][0];
											}
											
											if ( $classname ) {
												echo esc_html($classname) . '&ndash;&gt;';
											}
											echo esc_html($nested_inner_value['function'][1]);
											?>
										</span>
									<?php else : ?>
										<span class="sudowp-function-string">
											<?php echo esc_html($nested_inner_key); ?>
										</span>
									<?php endif; ?>
								</li>
								<?php
							endforeach;
						endforeach;
						?>
					</ul>
				<?php endif;
			endif;
			?>
		</span>
		<?php
	}
	
	function filter_hooks_panel() {
		?>
		<div class="sudowp-nested-hooks-block <?php echo ( 'show-filter-hooks' == $this->status ) ? 'sudowp-active' : '' ; ?> ">
			<?php
			foreach ( $this->all_hooks as $va_nested_value ) {
				if ( 'action' == $va_nested_value['type'] || 'filter' == $va_nested_value['type'] ) {
					$this->render_action( $va_nested_value );
				} else{
					?><div class="sudowp-collection-divider"><?php echo esc_html($va_nested_value['ID']); ?></div><?php
				}
			}
			?>
		</div>
		<?php
	}
	
	function plugin_active() {
        // SudoWP Filters to programmatically disable hooks if needed
		if ( ! apply_filters( 'sudowp_hooks_active', TRUE ) ) return FALSE;
		
		if ( is_admin() ) {
			if ( ! apply_filters( 'sudowp_hooks_backend_active', TRUE ) ) return FALSE;
		} else {
			if ( ! apply_filters( 'sudowp_hooks_frontend_active', TRUE ) ) return FALSE;
		}
		return TRUE;
	}
}

SudoWP_Hooks_Visualizer::get_instance();