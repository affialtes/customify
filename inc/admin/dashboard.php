<?php

class Customify_Dashboard {
	static $_instance;
	public $title;
	public $config;
	public $current_tab = '';
	public $url         = '';

	static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance      = new self();
			self::$_instance->url = admin_url( 'admin.php' );
			self::$_instance->url = add_query_arg(
				array( 'page' => 'customify' ),
				self::$_instance->url
			);

			self::$_instance->title = __( 'Customify Options', 'customify' );
			add_action( 'admin_menu', array( self::$_instance, 'add_menu' ), 5 );
			add_action( 'admin_enqueue_scripts', array( self::$_instance, 'scripts' ) );
			add_action( 'customify/dashboard/main', array( self::$_instance, 'box_links' ), 10 );
			add_action( 'customify/dashboard/main', array( self::$_instance, 'pro_modules_box' ), 15 );
			add_action( 'customify/dashboard/sidebar', array( self::$_instance, 'box_plugins' ), 10 );
			add_action( 'customify/dashboard/sidebar', array( self::$_instance, 'box_recommend_plugins' ), 20 );
			add_action( 'customify/dashboard/sidebar', array( self::$_instance, 'box_community' ), 25 );

			add_action( 'admin_notices', array( self::$_instance, 'admin_notice' ) );

			// Tabs.
			add_action( 'customify/dashboard/tab/changelog', array( self::$_instance, 'tab_changelog' ) );

		}
		return self::$_instance;
	}

	function add_url_args( $args = array() ) {
		return add_query_arg( $args, self::$_instance->url );
	}

	/**
	 * Add admin notice when active theme.
	 *
	 */
	function admin_notice() {
		global $pagenow;
		if ( is_admin() && ( 'themes.php' == $pagenow ) && isset( $_GET['activated'] ) ) {
			?>
		<div class="customify-notice-wrapper notice is-dismissible">
			<div class="customify-notice">
				<div class="customify-notice-img">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/admin/customify_logo@2x.png' ); ?>" alt="<?php esc_attr_e( 'logo', 'customify' ); ?>">
				</div>
				<div class="customify-notice-content">
					<div class="customify-notice-heading"><?php _e( 'Thanks for installing Customify, you rock! <img draggable="false" class="emoji" alt="" src="https://s.w.org/images/core/emoji/2.4/svg/1f918.svg">', 'customify' ); ?></div>
					<p><?php printf( __( 'To fully take advantage of the best our theme can offer please make sure you visit our <a href="%1$s">Customify options page</a>.', 'customify' ), esc_url( admin_url( 'themes.php?page=customify' ) ) ); ?></p>
				</div>
			</div>
		</div>
			<?php
		}
	}

	function add_menu() {
		add_theme_page(
			$this->title,
			$this->title,
			'manage_options',
			'customify',
			array( $this, 'page' )
		);
	}

	/**
	 * Register scripts
	 *
	 * @param string $id
	 */
	function scripts( $id ) {
		if ( 'appearance_page_customify' != $id && 'themes.php' != $id ) {
			return;
		}
		$suffix = Customify()->get_asset_suffix();
		wp_enqueue_style( 'customify-admin', esc_url( get_template_directory_uri() ) . '/assets/css/admin/dashboard' . $suffix . '.css', false, Customify::$version );
		if ( 'themes' != $id ) {
			wp_enqueue_style( 'plugin-install' );
			wp_enqueue_script( 'plugin-install' );
			wp_enqueue_script( 'updates' );
			add_thickbox();
		}
	}

	function setup() {
		$theme        = wp_get_theme();
		$this->config = array(
			'name'       => $theme->get( 'Name' ),
			'theme_uri'  => $theme->get( 'ThemeURI' ),
			'desc'       => $theme->get( 'Description' ),
			'author'     => $theme->get( 'Author' ),
			'author_uri' => $theme->get( 'AuthorURI' ),
			'version'    => $theme->get( 'Version' ),
		);

		$this->current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : ''; // phpcs:ignore
	}

	function page() {
		$this->setup();
		$this->page_header();
		echo '<div class="wrap">';
		$cb = apply_filters( 'customify/dashboard/content_cb', false );
		if ( ! is_callable( $cb ) ) {
			$cb = array( $this, 'page_inner' );
		}

		if ( is_callable( $cb ) ) {
			call_user_func_array( $cb, array( $this ) );
		}

		echo '</div>';
	}

	public function page_header() {
		?>
		<div class="cd-header">
			<div class="cd-row">
				<div class="cd-header-inner">
					<a href="https://wpcustomify.com" target="_blank" class="cd-branding">
						<img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/admin/customify_logo@2x.png'; ?>" alt="<?php esc_attr_e( 'logo', 'customify' ); ?>">
					</a>
					<span class="cd-version"><?php echo esc_html( $this->config['version'] ); ?></span>
					<a class="cd-top-link" href="<?php echo esc_url( $this->add_url_args( array( 'tab' => 'changelog' ) ) ); ?>"><?php _e( 'Changelog', 'customify' ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}

	function tab_changelog() {
		global $wp_filesystem;
		WP_Filesystem();
		$file = get_template_directory() . '/changelog.txt';
		if ( file_exists( $file ) ) {
			$file_contents = $wp_filesystem->get_contents( $file );
		}
		?>
		<p>
			<a class="button button-secondary" href="<?php echo esc_url( $this->url ); ?>"><?php _e( 'Back', 'customify' ); ?></a>
		</p>

		<?php
		do_action( 'customify/dashboard/changelog/before' );
		?>
		<div class="cd-box theme-changelog">
			<div class="cd-box-top"><?php _e( 'Changelog', 'customify' ); ?></div>
			<div class="cd-box-content">
				<pre style="width: 100%; max-height: 60vh; overflow: auto"><?php echo esc_textarea( $file_contents ); ?></pre>
			</div>
		</div>
		<?php
		do_action( 'customify/dashboard/changelog/after' );

	}

	function box_links() {
		$url = admin_url( 'customize.php' );

		$links = array(
			array(
				'label' => __( 'Logo & Site Identity', 'customify' ),
				'url'   => add_query_arg( array( 'autofocus' => array( 'section' => 'title_tagline' ) ), $url ),
			),
			array(
				'label' => __( 'Layout Settings', 'customify' ),
				'url'   => add_query_arg( array( 'autofocus' => array( 'section' => 'global_layout_section' ) ), $url ),
			),
			array(
				'label' => __( 'Header Builder', 'customify' ),
				'url'   => add_query_arg( array( 'autofocus' => array( 'panel' => 'header_settings' ) ), $url ),
			),
			array(
				'label' => __( 'Footer Builder', 'customify' ),
				'url'   => add_query_arg( array( 'autofocus' => array( 'panel' => 'footer_settings' ) ), $url ),
			),
			array(
				'label' => __( 'Styling', 'customify' ),
				'url'   => add_query_arg( array( 'autofocus' => array( 'panel' => 'styling_panel' ) ), $url ),
			),
			array(
				'label' => __( 'Typography', 'customify' ),
				'url'   => add_query_arg( array( 'autofocus' => array( 'panel' => 'typography_panel' ) ), $url ),
			),
			array(
				'label' => __( 'Sidebar Settings', 'customify' ),
				'url'   => add_query_arg( array( 'autofocus' => array( 'section' => 'sidebar_layout_section' ) ), $url ),
			),
			array(
				'label' => __( 'Titlebar Settings', 'customify' ),
				'url'   => add_query_arg( array( 'autofocus' => array( 'section' => 'titlebar' ) ), $url ),
			),

			array(
				'label' => __( 'Blog Posts', 'customify' ),
				'url'   => add_query_arg( array( 'autofocus' => array( 'panel' => 'blog_panel' ) ), $url ),
			),
			array(
				'label' => __( 'Homepage Settings', 'customify' ),
				'url'   => add_query_arg( array( 'autofocus' => array( 'section' => 'static_front_page' ) ), $url ),
			),
		);

		$links = apply_filters( 'customify/dashboard/links', $links );
		?>
		<div class="cd-box">
			<div class="cd-box-top"><?php _e( 'Links to Customizer Settings', 'customify' ); ?></div>
			<div class="cd-box-content">
				<ul class="cd-list-flex">
					<?php foreach ( $links as $l ) { ?>
						<li class="">
							<a class="cd-quick-setting-link" href="<?php echo esc_url( $l['url'] ); ?>" target="_blank"><?php echo esc_html( $l['label'] ); ?></a>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Display community info
	 */
	function box_community() {
		?>
		<div class="cd-box">
			<div class="cd-box-top"><?php _e( 'Join the community!', 'customify' ); ?></div>
			<div class="cd-box-content">
				<p><?php _e( 'Join the Facebook group for updates, discussions, chat with other Customify lovers.', 'customify' ); ?></p>
				<a target="_blank" href="https://www.facebook.com/groups/133106770857743"><?php _e( 'Join Our Facebook Group &rarr;	', 'customify' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Display recommend plugins
	 */
	function box_plugins() {

		?>
		<div class="cd-box box-plugins">
			<div class="cd-box-top"><?php _e( 'Customify ready to import sites', 'customify' ); ?></div>
			<div class="cd-sites-thumb">
				<img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/admin/sites_thumbnail.jpg'; ?>">
			</div>
			<div class="cd-box-content">
				<p><?php _e( '<strong>Customify Sites</strong> is a free add-on for the Customify theme which help you browse and import ready made websites with few clicks.', 'customify' ); ?></p>
				<?php

				$plugin_slug = 'customify-sites';
				$plugin_info = array(
					'name'            => 'customify-sites',
					'active_filename' => 'customify-sites/customify-sites.php',
				);

				$plugin_info  = wp_parse_args(
					$plugin_info,
					array(
						'name'            => '',
						'active_filename' => '',
					)
				);
				$status       = is_dir( WP_PLUGIN_DIR . '/' . $plugin_slug );
				$button_class = 'install-now button';               if ( $plugin_info['active_filename'] ) {
					$active_file_name = $plugin_info['active_filename'];
				} else {
					$active_file_name = $plugin_slug . '/' . $plugin_slug . '.php';
				}

				$sites_url = add_query_arg(
					array(
						'page' => 'customify-sites',
					),
					admin_url( 'themes.php' )
				);

				$view_site_txt = __( 'View Site Library', 'customify' );

				if ( ! is_plugin_active( $active_file_name ) ) {
					$button_txt = esc_html__( 'Install Now', 'customify' );
					if ( ! $status ) {
						$install_url = wp_nonce_url(
							add_query_arg(
								array(
									'action' => 'install-plugin',
									'plugin' => $plugin_slug,
								),
								network_admin_url( 'update.php' )
							),
							'install-plugin_' . $plugin_slug
						);

					} else {
						$install_url  = add_query_arg(
							array(
								'action'        => 'activate',
								'plugin'        => rawurlencode( $active_file_name ),
								'plugin_status' => 'all',
								'paged'         => '1',
								'_wpnonce'      => wp_create_nonce( 'activate-plugin_' . $active_file_name ),
							),
							network_admin_url( 'plugins.php' )
						);
						$button_class = 'activate-now button-primary';
						$button_txt   = esc_html__( 'Active Now', 'customify' );
					}

					$detail_link = add_query_arg(
						array(
							'tab'       => 'plugin-information',
							'plugin'    => $plugin_slug,
							'TB_iframe' => 'true',
							'width'     => '772',
							'height'    => '349',

						),
						network_admin_url( 'plugin-install.php' )
					);

					echo '<div class="rcp">';
					echo '<p class="action-btn plugin-card-' . esc_attr( $plugin_slug ) . '"><a href="' . esc_url( $install_url ) . '" data-slug="' . esc_attr( $plugin_slug ) . '" class="' . esc_attr( $button_class ) . '">' . $button_txt . '</a></p>'; // WPCS: XSS OK.
					echo '<a class="plugin-detail thickbox open-plugin-details-modal" href="' . esc_url( $detail_link ) . '">' . esc_html__( 'Details', 'customify' ) . '</a>';
					echo '</div>';
				} else {
					echo '<div class="rcp">';
					echo '<p ><a href="' . esc_url( $sites_url ) . '" data-slug="' . esc_attr( $plugin_slug ) . '" class="view-site-library">' . $view_site_txt . '</a></p>'; // // WPCS: XSS OK.
					echo '</div>';
				}

				?>
				<script type="text/javascript">
					jQuery( document ).ready( function($){
						var  sites_url = <?php echo json_encode( $sites_url ); // phpcs:ignore ?>;
						var  view_sites = <?php echo json_encode( $view_site_txt ); // phpcs:ignore ?>;
						$( '#plugin-filter .box-plugins' ).on( 'click', '.activate-now', function( e ){
							e.preventDefault();
							var button = $( this );
							var url = button.attr('href');
							button.addClass( 'button installing updating-message' );
							$.get( url, function( ){
								$( '.rcp .plugin-detail' ).hide();
								button.attr( 'href', sites_url );
								button.attr( 'class', 'view-site-library' );
								button.text( view_sites );
							} );
						} );
					} );
				</script>
			</div>
		</div>
		<?php
	}

	function get_plugin_file( $plugin_slug ) {
		$installed_plugins = get_plugins();
		foreach ( (array) $installed_plugins as $plugin_file => $info ) {
			if ( strpos( $plugin_file, $plugin_slug . '/' ) === 0 ) {
				return $plugin_file;
			}
		}
		return false;
	}

	function get_first_tag( $content ) {
		$content = wp_kses(
			$content,
			array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
				),
				'br'     => array(),
				'p'      => array(),
				'em'     => array(),
				'strong' => array(),
			)
		);
		$content = substr( $content, 0, strpos( $content, '</p>' ) + 4 );
		return $content;
	}

	function box_recommend_plugins() {

		$list_plugins = array(
			'themeisle-companion',
			'elementor',
			'beaver-builder-lite-version',
			'wpforms-lite',
		);

		$list_plugins = apply_filters( 'customify/recommend-plugins', $list_plugins );
		$key          = 'customify_plugins_info_' . wp_hash( json_encode( $list_plugins ) ); // phpcs:ignore
		$plugins_info = get_transient( $key );
		if ( false === $plugins_info ) {
			$plugins_info = array();
			if ( ! function_exists( 'plugins_api' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin-install.php';
			}
			foreach ( $list_plugins as $slug ) {
				$info = plugins_api( 'plugin_information', array( 'slug' => $slug ) );
				if ( ! is_wp_error( $info ) ) {
					$plugins_info[ $slug ] = $info;
				}
			}
			set_transient( $key, $plugins_info );
		}

		$html = '';
		foreach ( $plugins_info as $plugin_slug => $info ) {
			$status      = is_dir( WP_PLUGIN_DIR . '/' . $plugin_slug );
			$plugin_file = $this->get_plugin_file( $plugin_slug );
			if ( ! is_plugin_active( $plugin_file ) ) {
				$html .= '<div class="cd-list-item">';
				$html .= '<p class="cd-list-name">' . esc_html( $info->name ) . '</p>';
				if ( $status ) {
					$button_class = 'activate-now';
					$button_txt   = esc_html__( 'Activate', 'customify' );
					$url          = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $plugin_file ), 'activate-plugin_' . $plugin_file ); // phpcs:ignore
				} else {
					$button_class = 'install-now';
					$button_txt   = esc_html__( 'Install Now', 'customify' );
					$url          = wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'install-plugin',
								'plugin' => $plugin_slug,
							),
							network_admin_url( 'update.php' )
						),
						'install-plugin_' . $plugin_slug
					);
				}

				$detail_link = add_query_arg(
					array(
						'tab'       => 'plugin-information',
						'plugin'    => $plugin_slug,
						'TB_iframe' => 'true',
						'width'     => '772',
						'height'    => '349',
					),
					network_admin_url( 'plugin-install.php' )
				);

				$class = 'action-btn plugin-card-' . $plugin_slug;

				$html .= '<div class="rcp">';
				$html .= '<p class="' . esc_attr( $class ) . '"><a href="' . esc_url( $url ) . '" data-slug="' . esc_attr( $plugin_slug ) . '" class="' . esc_attr( $button_class ) . '">' . $button_txt . '</a></p>';
				$html .= '<a class="plugin-detail thickbox open-plugin-details-modal" href="' . esc_url( $detail_link ) . '">' . esc_html__( 'Details', 'customify' ) . '</a>';
				$html .= '</div>';

				$html .= '</div>';
			}
		} // end foreach

		if ( $html ) {
			?>
			<div class="cd-box">
				<div class="cd-box-top"><?php _e( 'Recommend Plugins', 'customify' ); ?></div>
				<div class="cd-box-content cd-list-border">
					<?php
						echo $html; // WPCS: XSS OK.
					?>
				</div>
			</div>
			<?php
		}
	}

	function pro_modules_box() {

		$modules = array(
			array(
				'name' => __( 'Header Transparent', 'customify' ),
				'desc' => __( 'Make your website stand out with transparent header modules.', 'customify' ),
				'url'  => 'https://wpcustomify.com/help/documentation/customify-pro-modules/header-transparent/',
			),
			array(
				'name' => __( 'Header Sticky', 'customify' ),
				'desc' => __( 'Let your header accessible when users scroll up or down in unique style.', 'customify' ),
				'url'  => 'https://wpcustomify.com/help/documentation/customify-pro-modules/header-sticky/',
			),
			array(
				'name' => __( 'Header and Footer Builder Booster', 'customify' ),
				'desc' => __( 'Get more header and footer builder items, plus advanced styling options.', 'customify' ),
				'url'  => 'https://wpcustomify.com/help/documentation/customify-pro-modules/advanced-header-footer-builder/',
			),
			array(
				'name' => __( 'Scroll To Top', 'customify' ),
				'desc' => __( 'Get a better user experience with a scroll to top button with beautiful animation.', 'customify' ),
				'url'  => 'https://wpcustomify.com/help/documentation/customify-pro-modules/scroll-to-top/',
			),
			array(
				'name' => __( 'Blog Pro', 'customify' ),
				'desc' => __( 'Take advantage of the Blog Pro module to show off your posts in any layouts.', 'customify' ),
				'url'  => 'https://wpcustomify.com/help/documentation/customify-pro-modules/blog-pro/',
			),
			array(
				'name' => __( 'Advanced Styling', 'customify' ),
				'desc' => __( 'Control the layout and typography setting for page header title, page header cover and more.', 'customify' ),
				'url'  => 'https://wpcustomify.com/help/documentation/customify-pro-modules/advanced-styling/',
			),
			array(
				'name' => __( 'Portfolio', 'customify' ),
				'desc' => __( 'Show off your best project in a beautiful way.', 'customify' ),
				'url'  => 'https://wpcustomify.com/help/documentation/customify-pro-modules/portfolio/',
			),
			array(
				'name' => __( 'Multiple Headers', 'customify' ),
				'desc' => __( 'Create unique header for each page, post, archive or WooCommerce pages.', 'customify' ),
				'url'  => 'https://wpcustomify.com/help/documentation/customify-pro-modules/multiple-headers/',
			),
			array(
				'name' => __( 'Mega Menu', 'customify' ),
				'desc' => __( 'Create mega menu for your sites that need more space for navigation.', 'customify' ),
				'url'  => 'https://wpcustomify.com/help/documentation/customify-pro-modules/mega-menu/',
			),
			array(
				'name' => __( 'Multilingual Integration', 'customify' ),
				'desc' => __( 'WPML multilingual plugin support, plus a fully customized language switcher header builder item.', 'customify' ),
				'url'  => '',
			),
			array(
				'name' => __( 'Custom Fonts', 'customify' ),
				'desc' => __( 'Custom Fonts module allows you to add your self-hosted fonts and use them on your Customify powered websites.', 'customify' ),
				'url'  => '',
			),

			array(
				'name' => __( 'Typekit', 'customify' ),
				'desc' => __( 'Typekit module allows you to add Typekit fonts and use them on your Customify powered websites.', 'customify' ),
				'url'  => '',
			),
			array(
				'name' => __( 'Customify Hooks', 'customify' ),
				'desc' => __( 'Add custom hook scripts.', 'customify' ),
				'url'  => '',
			),

			array(
				'name' => __( 'WooCommerce Booster', 'customify' ),
				'desc' => __( 'Gives you creative control of style and layout options for your shop.', 'customify' ),
				'url'  => '',
			),

			array(
				'name' => __( 'Single Product Layouts', 'customify' ),
				'desc' => __( 'More beautiful layouts for your single product.', 'customify' ),
				'url'  => '',
				'sub'  => true,
			),
			array(
				'name' => __( 'Off Canvas Filter', 'customify' ),
				'desc' => __( 'Add off canvas products filter for shop and product archive pages.', 'customify' ),
				'url'  => '',
				'sub'  => true,
			),
			array(
				'name' => __( 'Product Gallery Slider', 'customify' ),
				'desc' => __( 'Add slider for product gallery.', 'customify' ),
				'url'  => '',
				'sub'  => true,
			),
			array(
				'name' => __( 'Quick View', 'customify' ),
				'desc' => __( 'Add product quick view modal for product listing..', 'customify' ),
				'url'  => '',
				'sub'  => true,
			),

			array(
				'name' => __( 'Infinity Scroll.', 'customify' ),
				'desc' => __( 'Loads the next posts, products automatically when the reader approaches the bottom of the page.', 'customify' ),
				'url'  => '',
			),

		);

		?>
		<div class="cd-box">
			<div class="cd-box-top"><?php _e( 'Customify Pro Modules', 'customify' ); ?>
				<a class="cd-upgrade" target="_blank" href="https://wpcustomify.com/pricing/?utm_source=theme_dashboard&utm_medium=links&utm_campaign=pro_modules"><?php _e( 'Upgrade Now &rarr;', 'customify' ); ?></a></div>
			<div class="cd-box-content cd-modules">
				<?php foreach ( $modules as $m ) { ?>
				<div class="cd-module-item <?php echo isset( $m['sub'] ) && $m['sub'] ? 'cd-sub-module' : ''; ?>">
					<div class="cd-module-info">
						<div class="cd-module-name"><?php echo esc_html( $m['name'] ); ?></div>
						<?php if ( isset( $m['desc'] ) ) { ?>
						<div class="cd-module-desc"><?php echo esc_html( $m['desc'] ); ?></div>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	private function page_inner() {
		?>
		<div id="plugin-filter" class="cd-row metabox-holder">
			<hr class="wp-header-end">
			<?php

			do_action( 'customify/dashboard/start', $this );

			if ( $this->current_tab && has_action( 'customify/dashboard/tab/' . $this->current_tab ) ) {
				do_action( 'customify/dashboard/tab/' . $this->current_tab, $this );
			} else {
				?>
				<div class="cd-main">
					<?php do_action( 'customify/dashboard/main', $this ); ?>
				</div>
				<div class="cd-sidebar">
					<?php do_action( 'customify/dashboard/sidebar', $this ); ?>
				</div>
				<?php
			}

			do_action( 'customify/dashboard/end', $this );

			?>
		</div>
		<?php
	}

}

Customify_Dashboard::get_instance();


