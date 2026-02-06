<?php
/**
 * Plugin Name: SudoWP Hooks Visualizer
 * Plugin URI:  https://sudowp.com
 * Description: A secure, developer-focused tool to visualize WordPress Action and Filter hooks in real-time. Maintained by SudoWP.
 * Version:     1.3.2
 * Author:      SudoWP
 * Author URI:  https://sudowp.com
 * License:     GPLv2 or later
 * Text Domain: sudowp-hooks-visualizer
 * Domain Path: /localization/
 *
 * Based on "Simply Show Hooks" by Stuart O'Brien & cxThemes.
 */

declare(strict_types=1);

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SudoWP_Hooks_Visualizer {

    /**
     * @var string
     */
	private string $status = 'off';

    /**
     * @var array
     */
	private array $all_hooks = array();

    /**
     * @var array
     */
	private array $recent_hooks = array();

    /**
     * @var array
     */
	private array $ignore_hooks = array();

    /**
     * @var string
     */
	private string $doing = 'collect';

    /**
     * Singleton instance
     *
     * @return self
     */
	public static function get_instance(): self {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new self();
			$instance->init();
		}
		return $instance;
	}

	public function __construct() {}

    /**
     * Initialize the plugin
     */
	public function init(): void {
		// Allow developers to ignore noisy hooks via filter
		$this->ignore_hooks = (array) apply_filters( 'sudowp_hooks_ignore', array(
			'attribute_escape',
			'body_class',
			'the_post',
			'post_edit_form_tag',
			'gettext',
			'gettext_with_context',
			'translations_api',
			'wp_cache_get',
			'wp_cache_set'
		) );

		add_action( 'plugins_loaded', array( $this, 'load_translation' ) );

		// Securely set status
		$this->set_active_status();

		$this->attach_hooks();
		add_action( 'init', array( $this, 'plugin_init' ) );
	}

	/**
	 * Securely retrieve status from Request or Cookies with sanitation
     * Implements SameSite cookie attributes for security.
     * Includes CSRF protection via nonce verification.
	 */
	public function set_active_status(): void {
		$cookie_name = 'sudowp_hooks_status';

		// Check Request (GET/POST) first
		if ( isset( $_REQUEST['sudowp-hooks'] ) ) {
			// CSRF Protection: Verify nonce if changing status
			if ( ! isset( $_REQUEST['sudowp-hooks-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['sudowp-hooks-nonce'] ) ), 'sudowp_hooks_toggle' ) ) {
				// Invalid nonce - don't change status
				if ( isset( $_COOKIE[ $cookie_name ] ) ) {
					$this->status = sanitize_key( $_COOKIE[ $cookie_name ] );
				} else {
					$this->status = 'off';
				}
				return;
			}

			$status_val = sanitize_key( $_REQUEST['sudowp-hooks'] );

			// Only allow specific values
			if ( in_array( $status_val, array( 'off', 'show-action-hooks', 'show-filter-hooks' ), true ) ) {
				
				// Ensure COOKIE_DOMAIN is defined
				$cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
				
				// Modern setcookie signature (PHP 7.3+) for SameSite support
				setcookie( $cookie_name, $status_val, array(
					'expires'  => time() + 3600 * 24 * 30,
					'path'     => '/',
					'domain'   => $cookie_domain,
					'secure'   => is_ssl(),
					'httponly' => true,
					'samesite' => 'Lax',
				) );

				$this->status = $status_val;
			}
		} elseif ( isset( $_COOKIE[ $cookie_name ] ) ) {
			$this->status = sanitize_key( $_COOKIE[ $cookie_name ] );
		} else {
			$this->status = 'off';
		}
	}

	public function attach_hooks(): void {
		if ( 'show-action-hooks' === $this->status || 'show-filter-hooks' === $this->status ) {
			add_filter( 'all', array( $this, 'hook_all_hooks' ), 100 );
			add_action( 'shutdown', array( $this, 'notification_switch' ) );
			add_action( 'shutdown', array( $this, 'filter_hooks_panel' ) );
		}
	}

	public function detach_hooks(): void {
		remove_filter( 'all', array( $this, 'hook_all_hooks' ), 100 );
		remove_action( 'shutdown', array( $this, 'notification_switch' ) );
		remove_action( 'shutdown', array( $this, 'filter_hooks_panel' ) );
	}

	/*
	 * Admin Menu top bar
	 */
	public function admin_bar_menu( WP_Admin_Bar $wp_admin_bar ): void {
		$this->detach_hooks();
		$url = remove_query_arg( array( 'sudowp-hooks', 'sudowp-hooks-nonce' ) );

		if ( 'show-action-hooks' === $this->status ) {
			$title = __( 'Stop Showing Action Hooks', 'sudowp-hooks-visualizer' );
			$href  = add_query_arg( array(
				'sudowp-hooks'       => 'off',
				'sudowp-hooks-nonce' => wp_create_nonce( 'sudowp_hooks_toggle' ),
			), $url );
			$css   = 'sudowp-hooks-on sudowp-hooks-normal';
		} else {
			$title = __( 'Show Action Hooks', 'sudowp-hooks-visualizer' );
			$href  = add_query_arg( array(
				'sudowp-hooks'       => 'show-action-hooks',
				'sudowp-hooks-nonce' => wp_create_nonce( 'sudowp_hooks_toggle' ),
			), $url );
			$css   = '';
		}

		$wp_admin_bar->add_menu( array(
			'title'  => '<span class="ab-icon"></span><span class="ab-label">' . __( 'SudoWP Hooks', 'sudowp-hooks-visualizer' ) . '</span>',
			'id'     => 'sudowp-main-menu',
			'parent' => false,
			'href'   => $href,
		) );

		$wp_admin_bar->add_menu( array(
			'title'  => $title,
			'id'     => 'sudowp-hooks-viz',
			'parent' => 'sudowp-main-menu',
			'href'   => $href,
			'meta'   => array( 'class' => $css ),
		) );

		if ( 'show-filter-hooks' === $this->status ) {
			$title = __( 'Stop Showing Action & Filter Hooks', 'sudowp-hooks-visualizer' );
			$href  = add_query_arg( array(
				'sudowp-hooks'       => 'off',
				'sudowp-hooks-nonce' => wp_create_nonce( 'sudowp_hooks_toggle' ),
			), $url );
			$css   = 'sudowp-hooks-on sudowp-hooks-sidebar';
		} else {
			$title = __( 'Show Action & Filter Hooks', 'sudowp-hooks-visualizer' );
			$href  = add_query_arg( array(
				'sudowp-hooks'       => 'show-filter-hooks',
				'sudowp-hooks-nonce' => wp_create_nonce( 'sudowp_hooks_toggle' ),
			), $url );
			$css   = '';
		}

		$wp_admin_bar->add_menu( array(
			'title'  => $title,
			'id'     => 'sudowp-show-all-hooks',
			'parent' => 'sudowp-main-menu',
			'href'   => $href,
			'meta'   => array( 'class' => $css ),
		) );

		$this->attach_hooks();
	}

	public function add_builder_edit_button_css(): void {
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

	public function notification_switch(): void {
		// Additional security: Verify user has proper capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$this->detach_hooks();
		$url = add_query_arg( array(
			'sudowp-hooks'       => 'off',
			'sudowp-hooks-nonce' => wp_create_nonce( 'sudowp_hooks_toggle' ),
		) );
		?>
		<a class="sudowp-notification-switch" href="<?php echo esc_url( $url ); ?>">
			<span class="sudowp-notification-indicator"></span>
			<?php echo esc_html__( 'Stop Showing Hooks', 'sudowp-hooks-visualizer' ); ?>
		</a>
		<?php
		$this->attach_hooks();
	}

	public function plugin_init(): void {
		if (
				! current_user_can( 'manage_options' ) || // Restrict to Admins
				! $this->plugin_active()
			) {
			$this->status = 'off';
			return;
		}

		// Add security headers
		add_action( 'send_headers', array( $this, 'add_security_headers' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_script' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 90 );
		add_action( 'wp_print_styles', array( $this, 'add_builder_edit_button_css' ) );
		add_action( 'admin_print_styles', array( $this, 'add_builder_edit_button_css' ) );

		if ( 'show-action-hooks' === $this->status || 'show-filter-hooks' === $this->status ) {
			add_action( 'admin_head', array( $this, 'render_head_hooks' ), 100 );
			add_action( 'wp_head', array( $this, 'render_head_hooks' ), 100 );
			add_action( 'login_head', array( $this, 'render_head_hooks' ), 100 );
			add_action( 'customize_controls_print_scripts', array( $this, 'render_head_hooks' ), 100 );
		}
	}

	public function enqueue_script(): void {
		wp_register_style( 'sudowp-hooks-css', plugins_url( 'assets/css/sudowp-hooks-main.css', __FILE__ ), array(), '1.3.2', 'screen' );
		wp_enqueue_style( 'sudowp-hooks-css' );
	}

	/**
	 * Add security headers for enhanced protection
	 */
	public function add_security_headers(): void {
		// Only add headers when plugin is active for admin users
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// X-Content-Type-Options: Prevent MIME type sniffing
		if ( ! headers_sent() ) {
			header( 'X-Content-Type-Options: nosniff' );
		}
	}

	public function load_translation(): void {
		load_plugin_textdomain( 'sudowp-hooks-visualizer', false, dirname( plugin_basename( __FILE__ ) ) . '/localization/' );
	}

	public function render_head_hooks(): void {
		// Additional security: Verify user has proper capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$this->render_hooks();
		$this->doing = 'write';
	}

	public function render_hooks(): void {
		foreach ( $this->all_hooks as $nested_value ) {
			if ( 'action' === $nested_value['type'] ) {
				$this->render_action( $nested_value );
			}
		}
	}

	public function hook_all_hooks( string $hook ): void {
		global $wp_actions;
		
		// Validate hook name to prevent potential injection
		if ( empty( $hook ) || ! is_string( $hook ) ) {
			return;
		}
		
		if ( ! in_array( $hook, $this->recent_hooks, true ) ) {
			if ( isset( $wp_actions[ $hook ] ) ) {
				$this->all_hooks[] = array(
					'ID'       => $hook,
					'callback' => false,
					'type'     => 'action',
				);
			} else {
				$this->all_hooks[] = array(
					'ID'       => $hook,
					'callback' => false,
					'type'     => 'filter',
				);
			}
		}

		if ( isset( $wp_actions[ $hook ] ) && ! in_array( $hook, $this->recent_hooks, true ) && ! in_array( $hook, $this->ignore_hooks, true ) ) {
			if ( 'write' === $this->doing ) {
				$this->render_action( end( $this->all_hooks ) );
			}
		}

		$this->recent_hooks[] = $hook;
		if ( count( $this->recent_hooks ) > 100 ) {
			array_shift( $this->recent_hooks );
		}
	}

	public function render_action( array $args = array() ): void {
		global $wp_filter;
		
		// Validate input arguments
		if ( empty( $args['ID'] ) || ! is_string( $args['ID'] ) ) {
			return;
		}
		
		if ( empty( $args['type'] ) || ! in_array( $args['type'], array( 'action', 'filter' ), true ) ) {
			return;
		}
		
		$nested_hooks = ( isset( $wp_filter[ $args['ID'] ] ) ) ? $wp_filter[ $args['ID'] ] : false;

		$nested_hooks_count = 0;
		if ( $nested_hooks ) {
			foreach ( $nested_hooks as $key => $value ) {
				$nested_hooks_count += count( $value );
			}
		}
		?>
		<span style="display:none;" class="sudowp-hook sudowp-hook-<?php echo esc_attr( $args['type'] ); ?> <?php echo ( $nested_hooks ) ? 'sudowp-hook-has-hooks' : ''; ?>" >
			<?php
			if ( 'action' === $args['type'] ) {
				?>
				 <span class="sudowp-hook-type">A</span> 
				<?php
			} elseif ( 'filter' === $args['type'] ) {
				?>
				 <span class="sudowp-hook-type">F</span> <?php } ?>
			
			<?php echo esc_html( $args['ID'] ); ?>
			
			<?php if ( $nested_hooks_count ) { ?>
				<span class="sudowp-hook-count"><?php echo intval( $nested_hooks_count ); ?></span>
			<?php }

			if ( isset( $wp_filter[ $args['ID'] ] ) ) :
				$nested_hooks = $wp_filter[ $args['ID'] ];
				if ( $nested_hooks ) :
					?>
					<ul class="sudowp-hook-dropdown">
						<li class="sudowp-hook-heading">
							<strong><?php echo esc_html( $args['type'] ); ?>:</strong> <?php echo esc_html( $args['ID'] ); ?>
						</li>
						<?php
						foreach ( $nested_hooks as $nested_key => $nested_value ) :
							?>
							<li class="sudowp-priority">
								<span class="sudowp-priority-label"><strong>Priority:</strong> <?php echo intval( $nested_key ); ?></span>
							</li>
							<?php
							foreach ( $nested_value as $nested_inner_key => $nested_inner_value ) :
								?>
								<li>
									<?php
									if ( isset( $nested_inner_value['function'] ) && is_array( $nested_inner_value['function'] ) && count( $nested_inner_value['function'] ) > 1 ) :
										?>
										<span class="sudowp-function-string">
											<?php
											$classname = false;
											if ( is_object( $nested_inner_value['function'][0] ) ) {
												$classname = get_class( $nested_inner_value['function'][0] );
											} elseif ( is_string( $nested_inner_value['function'][0] ) ) {
												$classname = $nested_inner_value['function'][0];
											}

											if ( $classname ) {
												echo esc_html( $classname ) . '&ndash;&gt;';
											}
											echo esc_html( $nested_inner_value['function'][1] );
											?>
										</span>
									<?php else : ?>
										<span class="sudowp-function-string">
											<?php echo esc_html( $nested_inner_key ); ?>
										</span>
									<?php endif; ?>
								</li>
								<?php
							endforeach;
						endforeach;
						?>
					</ul>
				<?php
				endif;
			endif;
			?>
		</span>
		<?php
	}

	public function filter_hooks_panel(): void {
		// Additional security: Verify user has proper capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="sudowp-nested-hooks-block <?php echo ( 'show-filter-hooks' === $this->status ) ? 'sudowp-active' : ''; ?> ">
			<?php
			foreach ( $this->all_hooks as $va_nested_value ) {
				if ( 'action' === $va_nested_value['type'] || 'filter' === $va_nested_value['type'] ) {
					$this->render_action( $va_nested_value );
				} else {
					?><div class="sudowp-collection-divider"><?php echo esc_html( $va_nested_value['ID'] ); ?></div><?php
				}
			}
			?>
		</div>
		<?php
	}

	public function plugin_active(): bool {
		// Filters to programmatically disable hooks if needed
		if ( ! apply_filters( 'sudowp_hooks_active', true ) ) {
			return false;
		}
		if ( is_admin() ) {
			if ( ! apply_filters( 'sudowp_hooks_backend_active', true ) ) {
				return false;
			}
		} else {
			if ( ! apply_filters( 'sudowp_hooks_frontend_active', true ) ) {
				return false;
			}
		}
		return true;
	}
}

SudoWP_Hooks_Visualizer::get_instance();