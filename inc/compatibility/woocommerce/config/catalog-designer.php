<?php

class Customify_WC_Catalog_Designer {
	function __construct() {
		add_filter( 'customify/customizer/config', array( $this, 'config' ), 100 );
		if ( is_admin() || is_customize_preview() ) {
			add_filter( 'Customify_Control_Args', array( $this, 'add_catalog_url' ), 35 );
		}

		// Loop
		add_action( 'customify_wc_product_loop', array( $this, 'render' ) );

	}

	function render() {

		$items = Customify()->get_setting( 'wc_cd_positions' );

		$this->product__media();

		echo '<div class="wc-product-contents">';

        /**
         * Hook: woocommerce_before_shop_loop_item.
         *
         */
        do_action( 'woocommerce_before_shop_loop_item' );

		foreach ( ( array ) $items as $item ) {
			$item = wp_parse_args( $item, array(
				'_key'         => '',
				'_visibility'  => '',
				'show_in_grid' => 1,
				'show_in_list' => 1,
			) );
			if ( $item['_visibility'] !== 'hidden' ) {
			    $cb = apply_filters( 'customify/product-designer/part', false, $item['_key'] );
			    if ( ! is_callable( $cb ) ) {
                    $cb = array( $this, 'product__' . $item['_key'] );
                }

				if ( is_callable( $cb ) ) {
					$classes   = array();
					$classes[] = 'wc-product__part';
					$classes[] = 'wc-product__' . $item['_key'];

					if ( $item['show_in_grid'] ) {
						$classes[] = 'show-in-grid';
					} else {
						$classes[] = 'hide-in-grid';
					}
					if ( $item['show_in_list'] ) {
						$classes[] = 'show-in-list';
					} else {
						$classes[] = 'hide-in-list';
					}

					$item_html = '';
					ob_start();
					call_user_func( $cb, array() );
					$item_html = ob_get_contents();
					ob_end_clean();

					if ( trim( $item_html ) != '' ) {
                        echo '<div class="' . esc_attr( join( ' ', $classes ) ) . '">';
                        echo $item_html;
                        echo '</div>';
                    }

				}
			}
		}

        /**
         * Hook: woocommerce_after_shop_loop_item.
         *
         */
        do_action( 'woocommerce_after_shop_loop_item' );

		echo '</div>'; // end .wc-product-contents

	}

	/**
	 * Preview url when section open
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	function add_catalog_url( $args ) {
		$args['section_urls']['wc_catalog_designer'] = get_permalink( wc_get_page_id( 'shop' ) );

		return $args;
	}

	function config( $configs ) {

        $section = 'wc_catalog_designer';

        $configs[] = array(
            'name'  => $section,
            'type'  => 'section',
            'panel' => 'woocommerce',
            'label' => __( 'Product Catalog Designer', 'customify' ),
        );

        // catalog header
        $configs[] = array(
            'name'            => 'wc_cd_show_catalog_header',
            'type'            => 'checkbox',
            'section'         => $section,
            'default'         => 1,
            'selector'        => '.wc-product-listing',
            'render_callback' => 'woocommerce_content',
            'checkbox_label'  => __( 'Show Catalog header', 'customify' ),
        );

		// Show view mod
		$configs[] = array(
			'name'            => 'wc_cd_show_view_mod',
			'type'            => 'checkbox',
			'section'         => $section,
			'default'         => 1,
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'checkbox_label'  => __( 'Show Grid/List View Buttons', 'customify' ),
			'required'  => array( 'wc_cd_show_catalog_header' , '=', 1 ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_default_view',
			'type'            => 'select',
			'section'         => $section,
			'default'         => 'grid',
			'choices'         => array(
				'grid' => __( 'Grid', 'customify' ),
				'list' => __( 'List', 'customify' ),
			),
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'required'        => array(
			    array(  'wc_cd_show_view_mod', '=', 1 ),
                array( 'wc_cd_show_catalog_header' , '=', 1 ),
            ),
			'label'           => __( 'Default View Mod', 'customify' ),
		);

		$configs[] = array(
			'name'             => "wc_cd_positions",
			'section'          => $section,
			'label'            => __( 'Item Positions', 'customify' ),
			'type'             => 'repeater',
			'title'            => __( 'Body', 'customify' ),
			'live_title_field' => 'title',
			'limit'            => 4,
			'addable'          => false,
			'selector'         => '.wc-product-listing',
			'render_callback'  => 'woocommerce_content',
			'default'          => array(
				array(
					'_key'         => 'category',
					'_visibility'  => '',
					'show_in_grid' => 1,
					'show_in_list' => 1,
					'title'        => __( 'Category', 'customify' ),
				),
				array(
					'_visibility'  => '',
					'_key'         => 'title',
					'title'        => __( 'Title', 'customify' ),
					'show_in_grid' => 1,
					'show_in_list' => 1,
				),
				array(
					'_key'         => 'rating',
					'_visibility'  => '',
					'show_in_grid' => 1,
					'show_in_list' => 1,
					'title'        => __( 'Rating', 'customify' ),
				),

				array(
					'_key'         => 'price',
					'_visibility'  => '',
					'show_in_grid' => 1,
					'show_in_list' => 1,
					'title'        => __( 'Price', 'customify' ),
				),
				array(
					'_key'         => 'description',
					'_visibility'  => '',
					'show_in_grid' => 0,
					'show_in_list' => 1,
					'title'        => __( 'Short Description', 'customify' ),
				),
				array(
					'_key'         => 'add_to_cart',
					'_visibility'  => '',
					'show_in_grid' => 1,
					'show_in_list' => 1,
					'title'        => __( 'Add To Cart', 'customify' ),
				),
			),
			'fields'           => array(
				array(
					'name' => '_key',
					'type' => 'hidden',
				),
				array(
					'name'  => 'title',
					'type'  => 'hidden',
					'label' => __( 'Title', 'customify' ),
				),
				array(
					'name'           => 'show_in_grid',
					'type'           => 'checkbox',
					'checkbox_label' => __( 'Show in grid view', 'customify' ),
				),
				array(
					'name'           => 'show_in_list',
					'type'           => 'checkbox',
					'checkbox_label' => __( 'Show in list view', 'customify' ),
				),
			)
		);

		// Product Media
		$configs[] = array(
			'name'    => 'wc_cd_memdia_h',
			'type'    => 'heading',
			'section' => $section,
			'label'   => __( 'Product Media', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_list_media_width',
			'type'            => 'slider',
			'section'         => $section,
			'unit'            => '%',
			'max'             => 100,
			'device_settings' => true,
			'selector'        => 'format',
			'css_format'      => '.woocommerce-listing.wc-list-view .product.customify-col:not(.product-category) .wc-product-inner .wc-product-media { flex-basis: {{value_no_unit}}%; } .woocommerce-listing.wc-list-view .product.customify-col:not(.product-category) .wc-product-inner .wc-product-contents{ flex-basis: calc(100% - {{value_no_unit}}%); }',
			'title'           => __( 'List View Media Width', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_media_secondary',
			'type'            => 'select',
			'choices'         => array(
				'first' => __( 'Use first image of product gallery', 'customify' ),
				'last'  => __( 'Use last image of product gallery', 'customify' ),
				'none'  => __( 'Disable', 'customify' ),
			),
			'section'         => $section,
			'default'         => 'first',
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'description'     => __( 'This setting adds a hover effect that will reveal a secondary product thumbnail to product images on your product listings. This is ideal for displaying front and back images of products.', 'customify' ),
			'title'           => __( 'Secondary Thumbnail', 'customify' ),
		);

		// Product Item Alignment
		$configs[] = array(
			'name'    => 'wc_cd_align_h',
			'type'    => 'heading',
			'section' => $section,
			'label'   => __( 'Product Content Alignment', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_item_grid_align',
			'type'            => 'text_align_no_justify',
			'section'         => $section,
			'device_settings' => true,
			'selector'        => '.wc-grid-view .wc-product-contents',
			'css_format'      => 'text-align: {{value}};',
			'title'           => __( 'Grid View Alignment', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_item_list_align',
			'type'            => 'text_align_no_justify',
			'section'         => $section,
			'device_settings' => true,
			'selector'        => '.wc-list-view .wc-product-contents',
			'css_format'      => 'text-align: {{value}};',
			'title'           => __( 'List View Alignment', 'customify' ),
		);

		// Product title
		$configs[] = array(
			'name'    => 'wc_cd_title_h',
			'type'    => 'heading',
			'section' => $section,
			'label'   => __( 'Product Title', 'customify' ),
		);

		$configs[] = array(
			'name'       => 'wc_cd_title_typo',
			'type'       => 'typography',
			'css_format' => 'typography',
			'selector'   => '.wc-product__part.wc-product__title',
			'section'    => $section,
			'label'      => __( 'Typography', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_title_list_font_size',
			'type'            => 'slider',
			'max'             => 100,
			'device_settings' => true,
			'css_format'      => 'font-size: {{value}};',
			'selector'        => '.wc-list-view .wc-product__part.wc-product__title',
			'section'         => $section,
			'label'           => __( 'List View Font Size', 'customify' ),
		);

		$configs[] = array(
			'name'       => 'wc_cd_title_styling',
			'type'       => 'modal',
			'section'    => $section,
			'css_format' => 'styling',
			'selector'   => '.wc-product__part.wc-product__title',
			'label'      => __( 'Styling', 'customify' ),
			'fields'     => array(
				'tabs'           => array(
					'default' => __( 'Normal', 'customify' ),
					'hover'   => __( 'Hover', 'customify' ),
				),
				'default_fields' => array(
					array(
						'name'       => 'color',
						'type'       => 'color',
						'label'      => __( 'Color', 'customify' ),
						'css_format' => "color: {{value}};",
						'selector'   => '.wc-product__part.wc-product__title',
					),
					array(
						'name'       => 'bg_color',
						'type'       => 'color',
						'label'      => __( 'Background Color', 'customify' ),
						'css_format' => "background-color: {{value}};",
						'selector'   => '.wc-product__part.wc-product__title',
					),
					array(
						'name'            => 'padding',
						'type'            => 'css_ruler',
						'device_settings' => true,
						'label'           => __( 'Padding', 'customify' ),
						'css_format'      => array(
							'top'    => 'padding-top: {{value}};',
							'bottom' => 'padding-bottom: {{value}};',
							'left'   => 'padding-left: {{value}};',
							'right'  => 'padding-right: {{value}};',
						),
						'selector'        => '.wc-product__part.wc-product__title',
					),
					array(
						'name'            => 'margin',
						'type'            => 'css_ruler',
						'device_settings' => true,
						'label'           => __( 'Margin', 'customify' ),
						'css_format'      => array(
							'top'    => 'margin-top: {{value}};',
							'bottom' => 'margin-bottom: {{value}};',
							'left'   => 'margin-left: {{value}};',
							'right'  => 'margin-right: {{value}};',
						),
						'selector'        => '.wc-product__part.wc-product__title',
					),
					array(
						'name'  => 'border_heading',
						'type'  => 'heading',
						'label' => __( 'Border', 'customify' ),
					),
					array(
						'name'       => 'border_style',
						'type'       => 'select',
						'class'      => 'clear',
						'label'      => __( 'Border Style', 'customify' ),
						'default'    => '',
						'css_format' => "border-style: {{value}};",
						'choices'    => array(
							''       => __( 'Default', 'customify' ),
							'none'   => __( 'None', 'customify' ),
							'solid'  => __( 'Solid', 'customify' ),
							'dotted' => __( 'Dotted', 'customify' ),
							'dashed' => __( 'Dashed', 'customify' ),
							'double' => __( 'Double', 'customify' ),
							'ridge'  => __( 'Ridge', 'customify' ),
							'inset'  => __( 'Inset', 'customify' ),
							'outset' => __( 'Outset', 'customify' ),
						),
						'selector'   => '.wc-product__part.wc-product__title',
					),

					array(
						'name'       => 'border_width',
						'type'       => 'css_ruler',
						'label'      => __( 'Border Width', 'customify' ),
						'required'   => array(
							array( 'border_style', '!=', 'none' ),
							array( 'border_style', '!=', '' )
						),
						'selector'   => '.wc-product__part.wc-product__title',
						'css_format' => array(
							'top'    => 'border-top-width: {{value}};',
							'bottom' => 'border-bottom-width: {{value}};',
							'left'   => 'border-left-width: {{value}};',
							'right'  => 'border-right-width: {{value}};',
						),
					),
					array(
						'name'       => 'border_color',
						'type'       => 'color',
						'label'      => __( 'Border Color', 'customify' ),
						'css_format' => "border-color: {{value}};",
						'required'   => array(
							array( 'border_style', '!=', 'none' ),
							array( 'border_style', '!=', '' )
						),
						'selector'   => '.wc-product__part.wc-product__title',
					),

				),
				'hover_fields'   => array(
					array(
						'name'       => 'color',
						'type'       => 'color',
						'label'      => __( 'Color', 'customify' ),
						'css_format' => "color: {{value}};",
						'selector'   => '.wc-product__part.wc-product__title:hover',
					),
					array(
						'name'       => 'bg_color',
						'type'       => 'color',
						'label'      => __( 'Background Color', 'customify' ),
						'css_format' => "background-color: {{value}};",
						'selector'   => '.wc-product__part.wc-product__title:hover',
					),
				)
			)
		);


		// Product category
		$configs[] = array(
			'name'    => 'wc_cd_cat_h',
			'type'    => 'heading',
			'section' => $section,
			'label'   => __( 'Product Category', 'customify' ),
		);

		$configs[] = array(
			'name'       => 'wc_cd_cat_typo',
			'type'       => 'typography',
			'css_format' => 'typography',
			'selector'   => '.wc-product__part.wc-product__category a',
			'section'    => $section,
			'label'      => __( 'Typography', 'customify' ),
		);

		$configs[] = array(
			'name'       => 'wc_cd_cat_styling',
			'type'       => 'modal',
			'section'    => $section,
			'css_format' => 'styling',
			'selector'   => '.wc-product__part.wc-product__title',
			'label'      => __( 'Styling', 'customify' ),
			'fields'     => array(
				'tabs'           => array(
					'default' => __( 'Normal', 'customify' ),
					'hover'   => __( 'Hover', 'customify' ),
				),
				'default_fields' => array(
					array(
						'name'       => 'color',
						'type'       => 'color',
						'label'      => __( 'Color', 'customify' ),
						'css_format' => "color: {{value}};",
						'selector'   => '.wc-product__part.wc-product__category a',
					),
					array(
						'name'       => 'bg_color',
						'type'       => 'color',
						'label'      => __( 'Background Color', 'customify' ),
						'css_format' => "background-color: {{value}};",
						'selector'   => '.wc-product__part.wc-product__category a',
					),
					array(
						'name'            => 'padding',
						'type'            => 'css_ruler',
						'device_settings' => true,
						'label'           => __( 'Padding', 'customify' ),
						'css_format'      => array(
							'top'    => 'padding-top: {{value}};',
							'bottom' => 'padding-bottom: {{value}};',
							'left'   => 'padding-left: {{value}};',
							'right'  => 'padding-right: {{value}};',
						),
						'selector'        => '.wc-product__part.wc-product__category a',
					),
					array(
						'name'            => 'margin',
						'type'            => 'css_ruler',
						'device_settings' => true,
						'label'           => __( 'Margin', 'customify' ),
						'css_format'      => array(
							'top'    => 'margin-top: {{value}};',
							'bottom' => 'margin-bottom: {{value}};',
							'left'   => 'margin-left: {{value}};',
							'right'  => 'margin-right: {{value}};',
						),
						'selector'        => '.wc-product__part.wc-product__category a',
					),
					array(
						'name'  => 'border_heading',
						'type'  => 'heading',
						'label' => __( 'Border', 'customify' ),
					),
					array(
						'name'       => 'border_style',
						'type'       => 'select',
						'class'      => 'clear',
						'label'      => __( 'Border Style', 'customify' ),
						'default'    => '',
						'css_format' => "border-style: {{value}};",
						'choices'    => array(
							''       => __( 'Default', 'customify' ),
							'none'   => __( 'None', 'customify' ),
							'solid'  => __( 'Solid', 'customify' ),
							'dotted' => __( 'Dotted', 'customify' ),
							'dashed' => __( 'Dashed', 'customify' ),
							'double' => __( 'Double', 'customify' ),
							'ridge'  => __( 'Ridge', 'customify' ),
							'inset'  => __( 'Inset', 'customify' ),
							'outset' => __( 'Outset', 'customify' ),
						),
						'selector'   => '.wc-product__part.wc-product__category a',
					),

					array(
						'name'       => 'border_width',
						'type'       => 'css_ruler',
						'label'      => __( 'Border Width', 'customify' ),
						'required'   => array(
							array( 'border_style', '!=', 'none' ),
							array( 'border_style', '!=', '' )
						),
						'selector'   => '.wc-product__part.wc-product__category a',
						'css_format' => array(
							'top'    => 'border-top-width: {{value}};',
							'bottom' => 'border-bottom-width: {{value}};',
							'left'   => 'border-left-width: {{value}};',
							'right'  => 'border-right-width: {{value}};',
						),
					),
					array(
						'name'       => 'border_color',
						'type'       => 'color',
						'label'      => __( 'Border Color', 'customify' ),
						'css_format' => "border-color: {{value}};",
						'required'   => array(
							array( 'border_style', '!=', 'none' ),
							array( 'border_style', '!=', '' )
						),
						'selector'   => '.wc-product__part.wc-product__category a',
					),

					array(
						'name'       => 'border_radius',
						'type'       => 'slider',
						'max'        => 100,
						'label'      => __( 'Border Radius', 'customify' ),
						'css_format' => "border-radius: {{value}};",
						'selector'   => '.wc-product__part.wc-product__category a',
					),

				),
				'hover_fields'   => array(
					array(
						'name'       => 'color',
						'type'       => 'color',
						'label'      => __( 'Color', 'customify' ),
						'css_format' => "color: {{value}};",
						'selector'   => '.wc-product__part.wc-product__category:hover a',
					),
					array(
						'name'       => 'bg_color',
						'type'       => 'color',
						'label'      => __( 'Background Color', 'customify' ),
						'css_format' => "background-color: {{value}};",
						'selector'   => '.wc-product__part.wc-product__category:hover a',
					),
					array(
						'name'       => 'border_color',
						'type'       => 'color',
						'label'      => __( 'Border Color', 'customify' ),
						'css_format' => "border-color: {{value}};",
						'selector'   => '.wc-product__part.wc-product__category:hover a',
					),
				)
			)
		);

		// Product Price
		$configs[] = array(
			'name'    => 'wc_cd_price_h',
			'type'    => 'heading',
			'section' => $section,
			'label'   => __( 'Product Price', 'customify' ),
		);

		$configs[] = array(
			'name'       => 'wc_cd_price_typo',
			'type'       => 'typography',
			'css_format' => 'typography',
			'selector'   => '.wc-product__part.wc-product__price',
			'section'    => $section,
			'label'      => __( 'Typography', 'customify' ),
		);

		$configs[] = array(
			'name'       => 'wc_cd_r_price_typo',
			'type'       => 'typography',
			'css_format' => 'typography',
			'selector'   => '.wc-product__part.wc-product__price del',
			'section'    => $section,
			'label'      => __( 'Regular Price Typography', 'customify' ),
		);

		$configs[] = array(
			'name'       => 'wc_cd_price_styling',
			'type'       => 'modal',
			'section'    => $section,
			'css_format' => 'styling',
			'selector'   => '.wc-product__part.wc-product__price',
			'label'      => __( 'Styling', 'customify' ),
			'fields'     => array(
				'tabs'           => array(
					'default' => __( 'Normal', 'customify' ),
				),
				'default_fields' => array(
					array(
						'name'       => 'color',
						'type'       => 'color',
						'label'      => __( 'Color', 'customify' ),
						'css_format' => "color: {{value}};",
						'selector'   => '.woocommerce .wc-product__part.wc-product__price .price',
					),
					array(
						'name'       => 'regular_color',
						'type'       => 'color',
						'label'      => __( 'Regular Price Color', 'customify' ),
						'css_format' => "color: {{value}};",
						'selector'   => '.woocommerce .wc-product__part.wc-product__price .price del',
					),
					array(
						'name'            => 'padding',
						'type'            => 'css_ruler',
						'device_settings' => true,
						'label'           => __( 'Padding', 'customify' ),
						'css_format'      => array(
							'top'    => 'padding-top: {{value}};',
							'bottom' => 'padding-bottom: {{value}};',
							'left'   => 'padding-left: {{value}};',
							'right'  => 'padding-right: {{value}};',
						),
						'selector'        => '.wc-product__part.wc-product__price .price',
					),
					array(
						'name'            => 'margin',
						'type'            => 'css_ruler',
						'device_settings' => true,
						'label'           => __( 'Margin', 'customify' ),
						'css_format'      => array(
							'top'    => 'margin-top: {{value}};',
							'bottom' => 'margin-bottom: {{value}};',
							'left'   => 'margin-left: {{value}};',
							'right'  => 'margin-right: {{value}};',
						),
						'selector'        => '.wc-product__part.wc-product__price .price',
					),
					array(
						'name'  => 'border_heading',
						'type'  => 'heading',
						'label' => __( 'Border', 'customify' ),
					),
					array(
						'name'       => 'border_style',
						'type'       => 'select',
						'class'      => 'clear',
						'label'      => __( 'Border Style', 'customify' ),
						'default'    => '',
						'css_format' => "border-style: {{value}};",
						'choices'    => array(
							''       => __( 'Default', 'customify' ),
							'none'   => __( 'None', 'customify' ),
							'solid'  => __( 'Solid', 'customify' ),
							'dotted' => __( 'Dotted', 'customify' ),
							'dashed' => __( 'Dashed', 'customify' ),
							'double' => __( 'Double', 'customify' ),
							'ridge'  => __( 'Ridge', 'customify' ),
							'inset'  => __( 'Inset', 'customify' ),
							'outset' => __( 'Outset', 'customify' ),
						),
						'selector'   => '.wc-product__part.wc-product__price .price',
					),

					array(
						'name'       => 'border_width',
						'type'       => 'css_ruler',
						'label'      => __( 'Border Width', 'customify' ),
						'required'   => array(
							array( 'border_style', '!=', 'none' ),
							array( 'border_style', '!=', '' )
						),
						'selector'   => '.wc-product__part.wc-product__price .price',
						'css_format' => array(
							'top'    => 'border-top-width: {{value}};',
							'bottom' => 'border-bottom-width: {{value}};',
							'left'   => 'border-left-width: {{value}};',
							'right'  => 'border-right-width: {{value}};',
						),
					),
					array(
						'name'       => 'border_color',
						'type'       => 'color',
						'label'      => __( 'Border Color', 'customify' ),
						'css_format' => "border-color: {{value}};",
						'required'   => array(
							array( 'border_style', '!=', 'none' ),
							array( 'border_style', '!=', '' )
						),
						'selector'   => '.wc-product__part.wc-product__price .price',
					),

					array(
						'name'       => 'border_radius',
						'type'       => 'slider',
						'max'        => 100,
						'label'      => __( 'Border Radius', 'customify' ),
						'css_format' => "border-radius: {{value}};",
						'selector'   => '.wc-product__part.wc-product__price .price',
					),

				),
			)
		);

		// Product Rating
		$configs[] = array(
			'name'    => 'wc_cd_rating_h',
			'type'    => 'heading',
			'section' => $section,
			'label'   => __( 'Product Settings', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_rating_color',
			'type'            => 'color',
			'section'         => $section,
			'device_settings' => true,
			'selector'        => '.wc-product__part.wc-product__rating',
			'css_format'      => 'color: {{value}};',
			'title'           => __( 'Star Color', 'customify' ),
		);

		// Product Add To cart
		$configs[] = array(
			'name'    => 'wc_cd_button_h',
			'type'    => 'heading',
			'section' => $section,
			'label'   => __( 'Product Add to Cart', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_button_show_label',
			'type'            => 'checkbox',
			'default'         => 1,
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'section'         => $section,
			'checkbox_label'  => __( 'Show Text', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_button_show_icon',
			'type'            => 'checkbox',
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'section'         => $section,
			'checkbox_label'  => __( 'Show Icon', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_button_icon',
			'type'            => 'icon',
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'section'         => $section,
			'label'           => __( 'Icon', 'customify' ),
			'required'        => array( 'wc_cd_button_show_icon', '=', 1 )
		);

		$configs[] = array(
			'name'            => 'wc_cd_button_cart_icon',
			'type'            => 'icon',
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'section'         => $section,
			'label'           => __( 'View Cart icon', 'customify' ),
			'required'        => array( 'wc_cd_button_show_icon', '=', 1 )
		);

		$configs[] = array(
			'name'            => 'wc_cd_button_icon_pos',
			'type'            => 'select',
			'choices'         => array(
				'before' => __( 'Before', 'customify' ),
				'after'  => __( 'After', 'customify' ),
			),
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'section'         => $section,
			'label'           => __( 'Icon Position', 'customify' ),
			'required'        => array( 'wc_cd_button_show_icon', '=', 1 )
		);

		$configs[] = array(
			'name'       => 'wc_cd_button_typo',
			'type'       => 'typography',
			'css_format' => 'typography',
			'selector'   => '.wc-product__part.wc-product__add_to_cart a',
			'section'    => $section,
			'label'      => __( 'Typography', 'customify' ),
		);

		$configs[] = array(
			'name'       => 'wc_cd_button_styling',
			'type'       => 'styling',
			'section'    => $section,
			'css_format' => 'styling',
			'selector'   => array(
				'normal' => '.wc-product__part.wc-product__add_to_cart a, .wc-product__part.wc-product__add_to_cart button',
				'hover'  => '.wc-product__part.wc-product__add_to_cart a:hover, .wc-product__part.wc-product__add_to_cart button:hover'
			),
			'label'      => __( 'Button Styling', 'customify' ),
			'fields'     => array(
				'normal_fields' => array(
					'link_color' => false,
				),
				'hover_fields'  => array(
					'link_color' => false,
				)
			)
		);

		$configs[] = array(
			'name'       => 'wc_cd_button_icon_styling',
			'type'       => 'styling',
			'section'    => $section,
			'css_format' => 'styling',
			'selector'   => array(
				'normal' => '.wc-product__part.wc-product__add_to_cart a i, .wc-product__part.wc-product__add_to_cart button i',
				'hover'  => '.wc-product__part.wc-product__add_to_cart a:hover i, .wc-product__part.wc-product__add_to_cart button:hover i'
			),
			'label'      => __( 'Button Icon Styling', 'customify' ),
			'fields'     => array(
				'normal_fields' => array(
					'link_color' => false,
				),
				'hover_fields'  => array(
					'link_color' => false,
				)
			),
			'required'   => array( 'wc_cd_button_show_icon', '=', 1 )
		);

		// Product Sale Bubble
		$configs[] = array(
			'name'    => 'wc_cd_sale_bubble_h',
			'type'    => 'heading',
			'section' => $section,
			'label'   => __( 'Product Onsale Bubble', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_sale_bubble_type',
			'type'            => 'select',
			'default'         => 'text',
			'choices'         => array(
				'text'    => __( 'Text', 'customify' ),
				'percent' => __( 'Discount percent', 'customify' ),
				'value'   => __( 'Discount value', 'customify' ),
			),
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'section'         => $section,
			'label'           => __( 'Display Type', 'customify' ),
		);

		$configs[] = array(
			'name'       => 'wc_cd_sale_bubble_typo',
			'type'       => 'typography',
			'css_format' => 'typography',
			'selector'   => '.woocommerce .wc-product-inner .onsale',
			'section'    => $section,
			'label'      => __( 'Typography', 'customify' ),
		);

		$configs[] = array(
			'name'       => 'wc_cd_sale_bubble_styling',
			'type'       => 'styling',
			'section'    => $section,
			'css_format' => 'styling',
			'selector'   => array(
				'normal' => '.woocommerce .wc-product-inner span.onsale',
				'hover'  => '.woocommerce .wc-product-inner span.onsale'
			),
			'label'      => __( 'Onsale Bubble Styling', 'customify' ),
			'fields'     => array(
				'normal_fields' => array(
					'link_color' => false,
				),
				'hover_fields'  => array(
					'link_color' => false,
				)
			),
		);

		return $configs;
	}

	function product__media() {
		echo '<div class="wc-product-media">';
		woocommerce_template_loop_product_link_open();
		woocommerce_show_product_loop_sale_flash();
		woocommerce_template_loop_product_thumbnail();
		customify_wc_secondary_product_thumbnail();
		do_action( 'customify_after_loop_product_media' );
		woocommerce_template_loop_product_link_close();
		echo '</div>';
	}

	function product__title() {

        /**
         * Hook: woocommerce_before_shop_loop_item_title.
         *
         * @hooked woocommerce_show_product_loop_sale_flash - 10
         * @hooked woocommerce_template_loop_product_thumbnail - 10
         */
        do_action( 'woocommerce_before_shop_loop_item_title' );

        woocommerce_template_loop_product_link_open();
        /**
         * Hook: woocommerce_shop_loop_item_title.
         *
         * @hooked woocommerce_template_loop_product_title - 10
         */
        do_action( 'woocommerce_shop_loop_item_title' );

        woocommerce_template_loop_product_link_close();

        /**
         * Hook: woocommerce_after_shop_loop_item_title.
         *
         * @hooked woocommerce_template_loop_rating - 5
         * @hooked woocommerce_template_loop_price - 10
         */
        do_action( 'woocommerce_after_shop_loop_item_title' );

	}

	function product__description() {
		echo '<div class="woocommerce-loop-product__desc">';
		the_excerpt();
		echo '</div>';

	}

	function product__price() {
		woocommerce_template_loop_price();
	}

	function product__rating() {
		woocommerce_template_loop_rating();
	}

	function product__category() {
		global $post;

		$tax = 'product_cat';
		$num = 1;

		$terms = get_the_terms( $post, $tax );

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		if ( empty( $terms ) ) {
			return false;
		}

		$links = array();

		foreach ( $terms as $term ) {
			$link = get_term_link( $term, $tax );
			if ( is_wp_error( $link ) ) {
				return $link;
			}
			$links[] = '<a href="' . esc_url( $link ) . '" rel="tag">' . esc_html( $term->name ) . '</a>';
		}

		$categories_list = array_slice( $links, 0, $num );

		echo join( ' ', $categories_list );
	}

	function product__add_to_cart() {
		woocommerce_template_loop_add_to_cart();
	}


}

new Customify_WC_Catalog_Designer();