<?php

class _Beacon_Customizer_Layout_Builder {
    static $_instance;
    function __construct()
    {

        require_once get_template_directory().'/inc/customizer-layout-builder/config/header/panel.php';

        add_action( 'customize_controls_enqueue_scripts', array( $this, 'scripts' ) );
        add_action( 'customize_controls_print_footer_scripts', array( $this, 'template' ) );
    }

    static function get_header_sections(){
        $elements = array(
            'row-top',
            'row-main',
            'row-bottom',
            'row-sidebar',

            'templates',

            'logo',
            'primary-menu',
            'nav-icon',
            'search',
            'button',
            'icon-list',
            'user',
            'social-icons',
            'html',
        );

        return $elements;
    }

    function scripts(){

        wp_enqueue_script( '_beacon-layout-builder', get_template_directory_uri() . '/assets/js/customizer/builder.js', array( 'customize-controls', 'jquery-ui-resizable', 'jquery-ui-droppable', 'jquery-ui-draggable' ), false, true );
        wp_localize_script( '_beacon-layout-builder',  '_Beacon_Layout_Builder',  array(
            'header' => array(
                'id'         => 'header',
                'control_id' => 'header_builder_panel',
                'panel'      => 'header_settings',
                'section'    => 'header_builder_panel',
                'items'      => $this->get_header_items(),
                'devices' => array(
                    'desktop'   => __( 'Desktop', '_beacon' ),
                    'mobile'    => __( 'Mobile/Tablet', '_beacon' ),
                ),
            )

        ) );
    }

    static function get_instance(){
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance ;
    }

    function get_header_items(){
        $items = array(
            array(
                'name' => __( 'Logo', '_beacon' ),
                'id' => 'logo',
                'width' => '3',
                'section' => 'header_logo' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Nav Icon', '_beacon' ),
                'id' => 'nav-icon',
                'width' => '3',
                'section' => 'header_nav_icon' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Primary Menu', '_beacon' ),
                'id' => 'nav-menu',
                'width' => '6',
                'section' => 'header_menu_primary' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Search', '_beacon' ),
                'id' => 'search',
                'col' => 0,
                'width' => '3',
                'section' => 'header_search' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Social Icons', '_beacon' ),
                'id' => 'social-icons',
                'col' => 0,
                'width' => '4',
                'section' => 'header_social_icons' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Button', '_beacon' ),
                'id' => 'button',
                'col' => 0,
                'width' => '4',
                'section' => 'header_button' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Icon List', '_beacon' ),
                'id' => 'icon-list',
                'col' => 0,
                'width' => '4',
                'section' => 'header_icon_list' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'HTML', '_beacon' ),
                'id' => 'html',
                'col' => 0,
                'width' => '4',
                'section' => 'header_html' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'User', '_beacon' ),
                'id' => 'user',
                'col' => 0,
                'width' => '4',
                'section' => 'header_user' // Customizer section to focus when click settings
            ),

        );


        $items = apply_filters( '_beacon/builder/header/items', $items );
        $new_items = array();
        foreach (  $items as $k => $i ) {
            $new_items[ $i['id'] ] = $i;
        }

        return $new_items;
    }

    function template(){
        ?>
        <script type="text/html" id="tmpl-_beacon--builder-panel">
            <div class="_beacon--customize-builder">
                <div class="_beacon--cb-inner">
                    <div class="_beacon--cb-header">
                        <div class="_beacon--cb-devices-switcher">
                        </div>
                        <div class="_beacon--cb-actions">
                            <a data-id="{{ data.id }}_templates" class="focus-section" href="#"><?php _e( 'Templates', '_beacon' ); ?></a>
                            <a class="_beacon--panel-close" href="#"><?php _e( 'Close', '_beacon' ); ?></a>
                        </div>
                    </div>
                    <div class="_beacon--cb-body"></div>
                </div>
            </div>
        </script>


        <script type="text/html" id="tmpl-_beacon--cb-panel">
            <div class="_beacon--cp-rows">
                <div class="_beacon--row-top _beacon--cb-row">
                    <a class="_beacon--cb-row-settings" data-id="top" href="#">set</a>
                    <div class="_beacon--row-inner">
                        <div class="_beacon--cb-items grid-stack gridster" data-id="top"></div>
                    </div>
                </div>
                <div class="_beacon--row-main _beacon--cb-row">
                    <a class="_beacon--cb-row-settings" data-id="main" href="#">set</a>
                    <div class="_beacon--row-inner">
                        <div class="_beacon--cb-items grid-stack gridster" data-id="main"></div>
                    </div>
                </div>
                <div class="_beacon--row-bottom _beacon--cb-row">
                    <a class="_beacon--cb-row-settings" data-id="bottom" href="#">set</a>
                    <div class="_beacon--row-inner">
                        <div class="_beacon--cb-items grid-stack gridster" data-id="bottom"></div>
                    </div>
                </div>
            </div>

            <# if ( data.device != 'desktop' ) { #>
            <div class="_beacon--cp-sidebar">
                <div class="_beacon--row-bottom _beacon--cb-row">
                    <a class="_beacon--cb-row-settings" data-id="sidebar" href="#">set</a>
                    <div class="_beacon--row-inner">
                        <div class="_beacon--cb-items _beacon--sidebar-items grid-stack----" data-id="sidebar"></div>
                    </div>
                </div>
            <div>
            <# } #>

        </script>

        <script type="text/html" id="tmpl-_beacon--cb-item">
            <div class="grid-stack-item item-from-list"
                 title="{{ data.name }}"
                 data-id="{{ data.id }}"

                data-gs-x="{{ data.x }}" data-gs-y="{{ data.y }}"
                data-gs-width="{{ data.width }}" data-gs-height="1"
            >
                <div class="grid-stack-item-content">
                    <span class="_beacon--cb-item-name">{{ data.name }}</span>
                    <span class="_beacon--cb-item-remove _beacon-cb-icon"></span>
                    <span class="_beacon--cb-item-setting _beacon-cb-icon" data-section="{{ data.section }}"></span>
                </div>
            </div>
        </script>
        <?php
    }



}

new _Beacon_Customizer_Layout_Builder();


function _beacon_customize_render_header(){
    ?>
    <div class="header-top">
        <div class="_beacon-container">
            header top
        </div> <!-- #._beacon-container -->
    </div><!-- #.header-top -->

    <div class="header-main">
        <div class="_beacon-container">
            <div class="site-branding">
                <?php
                the_custom_logo();
                if ( is_front_page() && is_home() ) : ?>
                    <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
                <?php else : ?>
                    <p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
                    <?php
                endif;

                $description = get_bloginfo( 'description', 'display' );
                if ( $description || is_customize_preview() ) : ?>
                    <p class="site-description"><?php echo $description; /* WPCS: xss ok. */ ?></p>
                    <?php
                endif; ?>
            </div><!-- .site-branding -->

            <nav id="site-navigation" class="main-navigation">
                <?php
                //                            wp_nav_menu( array(
                //                                'theme_location' => 'menu-1',
                //                                'menu_id'        => 'primary-menu',
                //                            ) );
                ?>
            </nav><!-- #site-navigation -->
        </div> <!-- #._beacon-container -->
    </div><!-- #.header-main -->

    <div class="header-bottom">
        <div class="_beacon-container">
            header bottom
        </div> <!-- #._beacon-container -->
    </div><!-- #.header-bottom -->
    <pre class="debug"><?php print_r( get_theme_mod( 'header_builder_panel' ) ); ?></pre>
    <?php
}