<?php

/**
 * Plugin Name: Image Compositions
 * Plugin URI:  https://github.com/wpcomvip/metro/
 * Description: A tool for combining media library images into a single, new image.
 * Version:     1.0.2
 * Author:      Metro.co.uk
 * Author URI:  https://github.com/wpcomvip/metro/graphs/contributors
 * Text Domain: image-compositions
 */

namespace MDT;

if ( ! class_exists( 'MDT\Image_Compositions' ) ) :

	/**
	 * Class Image_Compositions
	 *
	 * This class adds a new admin page called Image Compositions
	 * under the Media submenu.
	 *
	 * It also adds all the necessary hooks for filtering those words
	 * out of your permalinks.
	 */
	class Image_Compositions {

		/**
		 * Define the width of the image dividers in pixels.
		 *
		 * @var int
		 */
		public static $divider_width = 4;

		/**
		 * Define the color of the divider.
		 *
		 * @var string
		 */
		public static $divider_color = '#ffffff';

		/**
		 * Define the width of the composition.
		 *
		 * @var int
		 */
		public static $composition_width = 1000;

		/**
		 * Zoom rate (percentage).
		 *
		 * @var int
		 */
		public static $zoom_rate = 10;

		/**
		 * Define the default aspect ratios.
		 *
		 * @var array
		 */
		public static $aspect_ratios = [
			'16:9',
			'4:3',
		];

		/**
		 * Default composition layouts.
		 *
		 * Layouts are defined by one or more `zones`.
		 * Each drop zone must have a `width` and `height`
		 * as well as a placement from the `left` and `top`
		 * edges of the composition. All values are expressed
		 * in percentages.
		 *
		 * Layouts may also contain `divides`, which are the
		 * vertical or horizontal separators intended to
		 * separate the zones. 
		 *
		 * @var array
		 */
		public static $layouts = [
			'single' => [
				'zones' => [
					[
						'width'  => 100,
						'height' => 100,
						'left'   => 0,
						'top'    => 0,
					]
				]
			],
			'equalHalf' => [
				'zones' => [
					[
						'width'  => 50,
						'height' => 100,
						'left'   => 0,
						'top'    => 0
					],
					[
						'width'  => 50,
						'height' => 100,
						'left'   => 50,
						'top'    => 0,
					]
				],
				'divides' => [
					[
						'type'   => 'vertical',
						'width'  => 50,
						'height' => 100,
					]
				]
			],
			'equalThirds' => [
				'zones' => [
					[
						'width'  => 33.33333333333333333333,
						'height' => 100,
						'left'   => 0,
						'top'    => 0
					],
					[
						'width'  => 33.33333333333333333333,
						'height' => 100,
						'left'   => 33.33333333333333333333,
						'top'    => 0,
					],
					[
						'width'  => 33.33333333333333333333,
						'height' => 100,
						'left'   => 66.66666666666666666666,
						'top'    => 0,
					]
				],
				'divides' => [
					[
						'type'   => 'vertical',
						'width'  => 33.33333333333333333333,
						'height' => 100,
					],
					[
						'type'   => 'vertical',
						'width'  => 66.66666666666666666666,
						'height' => 100,
					]
				]
			],
			'offsetHalf' => [
				'zones' => [
					[
						'width'  => 66.66666666666666666666,
						'height' => 100,
						'left'   => 0,
						'top'    => 0
					],
					[
						'width'  => 33.33333333333333333333,
						'height' => 100,
						'left'   => 66.66666666666666666666,
						'top'    => 0,
					]
				],
				'divides' => [
					[
						'type'   => 'vertical',
						'width'  => 66.66666666666666666666,
						'height' => 100,
					]
				]
			],
			'offsetHalfInverse' => [
				'zones' => [
					[
						'width'  => 66.66666666666666666666,
						'height' => 100,
						'left'   => 33.33333333333333333333,
						'top'    => 0
					],
					[
						'width'  => 33.33333333333333333333,
						'height' => 100,
						'left'   => 0,
						'top'    => 0,
					]
				],
				'divides' => [
					[
						'type'   => 'vertical',
						'width'  => 33.33333333333333333333,
						'height' => 100,
					]
				]
			],
			'equalHalfQuarters' => [
				'zones' => [
					[
						'width'  => 50,
						'height' => 100,
						'left'   => 0,
						'top'    => 0
					],
					[
						'width'  => 50,
						'height' => 50,
						'left'   => 50,
						'top'    => 0
					],
					[
						'width'  => 50,
						'height' => 50,
						'left'   => 50,
						'top'    => 50
					]
				],
				'divides' => [
					[
						'type'   => 'vertical',
						'width'  => 50,
						'height' => 100,
					],
					[
						'type'   => 'horizontal',
						'width'  => 50,
						'height' => 50,
						'left'   => 50
					]
				]
			],
			'offsetHalfQuarters' => [
				'zones' => [
					[
						'width'  => 66.66666666666666666666,
						'height' => 100,
						'left'   => 0,
						'top'    => 0
					],
					[
						'width'  => 33.33333333333333333333,
						'height' => 50,
						'left'   => 66.66666666666666666666,
						'top'    => 0
					],
					[
						'width'  => 33.33333333333333333333,
						'height' => 50,
						'left'   => 66.66666666666666666666,
						'top'    => 50
					]
				],
				'divides' => [
					[
						'type'   => 'vertical',
						'width'  => 66.66666666666666666666,
						'height' => 100,
					],
					[
						'type'   => 'horizontal',
						'width'  => 33.33333333333333333333,
						'height' => 50,
						'left'   => 66.66666666666666666666
					]
				]
			],
			'equalQuarters' => [
				'zones' => [
					[
						'width'  => 50,
						'height' => 50,
						'left'   => 0,
						'top'    => 0
					],
					[
						'width'  => 50,
						'height' => 50,
						'left'   => 0,
						'top'    => 50
					],
					[
						'width'  => 50,
						'height' => 50,
						'left'   => 50,
						'top'    => 0
					],
					[
						'width'  => 50,
						'height' => 50,
						'left'   => 50,
						'top'    => 50
					]
				],
				'divides' => [
					[
						'type'   => 'vertical',
						'width'  => 50,
						'height' => 100,
					],
					[
						'type'   => 'horizontal',
						'width'  => 100,
						'height' => 50,
					]
				]
			],
			'equalSixths' => [
				'zones' => [
					[
						'width'  => 33.33333333333333333333,
						'height' => 50,
						'left'   => 0,
						'top'    => 0
					],
					[
						'width'  => 33.33333333333333333333,
						'height' => 50,
						'left'   => 33.33333333333333333333,
						'top'    => 0
					],
					[
						'width'  => 33.33333333333333333333,
						'height' => 50,
						'left'   => 66.66666666666666666666,
						'top'    => 0
					],
					[
						'width'  => 33.33333333333333333333,
						'height' => 50,
						'left'   => 0,
						'top'    => 50
					],
					[
						'width'  => 33.33333333333333333333,
						'height' => 50,
						'left'   => 33.33333333333333333333,
						'top'    => 50
					],
					[
						'width'  => 33.33333333333333333333,
						'height' => 50,
						'left'   => 66.66666666666666666666,
						'top'    => 50
					]
				],
				'divides' => [
					[
						'type'   => 'vertical',
						'width'  => 33.33333333333333333333,
						'height' => 100,
					],
					[
						'type'   => 'vertical',
						'width'  => 66.66666666666666666666,
						'height' => 100,
					],
					[
						'type'   => 'horizontal',
						'width'  => 100,
						'height' => 50,
					]
				]
			]
		];

		/**
		 * Menu slug
		 *
		 * @var string
		 */
		const MENU_SLUG = 'mdt-image-compositions';

		/**
		 * Add the admin hooks.
		 */
		public static function load() {
			add_action( 'admin_init', [ __CLASS__, 'process_form' ] );
			add_action( 'admin_menu', [ __CLASS__, 'add_submenu_page' ] );
			add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
		}

		/**
		 * Add submenu page.
		 */
		public static function add_submenu_page() {
			add_submenu_page( 
				'upload.php',
				'Image Compositions',
				'Image Compositions',
				'upload_files',
				self::MENU_SLUG,
				[ __CLASS__, 'page_callback' ]
			);
		}

		/**
		 * The page callback.
		 */
		public static function page_callback() {
			$configuration = self::get_composition_configuration();
			?>
			<div class="wrap">
				<h2>Image Compositions</h2>
				<ul class="mdt-image-composition-nav"></ul>
				<div class="mdt-image-composition mdt-image-composition-16-9">
					<canvas width="<?php echo (int) $configuration['compositionWidth']; ?>" height="<?php echo (int) ( $configuration['compositionWidth'] * 9 / 16 ); ?>" class="mdt-image-compositions-canvas"></canvas>
				</div>
				
				<form method="POST">
					<?php wp_nonce_field( 'mdt-image-composition', 'mdt-image-composition' ); ?>
					<input type="hidden" id="mdt-image-composition-data" name="mdt-image-composition-data" value="" />
					<input type="hidden" id="mdt-image-composition-image-ids" name="mdt-image-composition-image-ids" value="" />
					<input type="text" class="widefat mdt-image-composition-title" id="mdt-image-composition-title" name="mdt-image-composition-title" value="" placeholder="Image title (optional)" />
					<input type="submit" name="submit" id="submit" class="mdt-image-compositions-submit button button-primary button-large" value="Save Image" />
				</form>
			</div>
			<?php
		}

		/**
		 * Add scripts and styles.
		 */
		public static function enqueue_scripts() {

			// Ensure we're on the correct page.
			global $pagenow;
			if ( $pagenow !== 'upload.php'
				|| empty( $_GET['page'] )
				|| $_GET['page'] !== self::MENU_SLUG
			) {
				return;
			}

			add_action( 'admin_print_styles', [ __CLASS__, 'print_styles' ] );

			// Enqueue standard media scripts.
			wp_enqueue_media();

			// Enqueue custom script and styles.
			wp_enqueue_script(
				self::MENU_SLUG,
				plugin_dir_url( __FILE__ ) . 'assets/js/admin.js',
				[
					'jquery',
					'jquery-ui-draggable'
				],
				null,
				true
			);

			wp_enqueue_style(
				self::MENU_SLUG,
				plugin_dir_url( __FILE__ ) . 'assets/css/styles.css'
			);

			// Localize the tool configuration.
			wp_localize_script(
				self::MENU_SLUG,
				'mdtImageCompositionsConfiguration',
				self::get_composition_configuration()
			);
		}

		/**
		 * Get composition configuration.
		 *
		 * @return array The composition configuration.
		 */
		public static function get_composition_configuration() {

			/**
			 * Allow filtering on the tool configuration before
			 * passing it off to be localized.
			 *
			 * @param array $configuration The current tool configuration.
			 */
			return apply_filters( 'mdt_image_composition_configuration', [
				'layouts'          => self::$layouts,
				'aspectRatios'     => self::$aspect_ratios,
				'compositionWidth' => self::$composition_width,
				'dividerColor'     => self::$divider_color,
				'dividerWidth'     => self::$divider_width,
				'zoomRate'         => self::$zoom_rate,
			] );
		}

		/**
		 * Print styles related to composition size and aspect ratio.
		 */
		public static function print_styles() {
			$configuration = self::get_composition_configuration();

			// Break up aspect ratio into width/height.
			$configuration['aspectRatios'] = array_map( function( $aspect_ratio ) {
				$pieces = explode( ':', $aspect_ratio );

				return [
					'width'  => (int) $pieces[0],
					'height' => (int) $pieces[1]
				];
			}, $configuration['aspectRatios'] );
			?>
			<style type="text/css">
				.mdt-image-composition {
					width: <?php echo (int) $configuration['compositionWidth']; ?>px;
				}

				.mdt-image-composition-title {
					max-width: <?php echo (int) $configuration['compositionWidth']; ?>px;
				}

				<?php foreach ( $configuration['aspectRatios'] as $aspect_ratio ) : ?>

				.mdt-image-composition-<?php echo esc_attr( $aspect_ratio['width'] ); ?>-<?php echo esc_attr( $aspect_ratio['height'] ); ?> {
					height: <?php echo (int) ( $configuration['compositionWidth'] * $aspect_ratio['height'] / $aspect_ratio['width'] ); ?>px;
				}

				<?php endforeach; ?>
			</style>
			<?php
		}

		/**
		 * Process image submission.
		 */
		public static function process_form() {

			// Ensure we're on the correct page
			global $pagenow;
			if ( $pagenow !== 'upload.php'
				|| empty( $_GET['page'] )
				|| $_GET['page'] !== self::MENU_SLUG
			) {
				return;
			}

			// Make sure nonce is set
			if ( ! isset( $_POST['mdt-image-composition'] ) || ! wp_verify_nonce( $_POST['mdt-image-composition'], 'mdt-image-composition' ) ) {
				return;
			}

			// Make sure data is set
			if ( empty( $_POST['mdt-image-composition-data'] )
				|| empty( $_POST['mdt-image-composition-image-ids'] )
			) {
				return;
			}

			// Sanitize image IDs
			$image_ids = (array) json_decode( $_POST['mdt-image-composition-image-ids'] );
			$image_ids = array_map( 'intval', $image_ids );
			$image_ids = array_filter( $image_ids );
			$image_ids = array_unique( $image_ids );
			if ( empty( $image_ids ) ) {
				return;
			}

			// Extract image data
			$data  = base64_decode( preg_replace('#^data:image/\w+;base64,#i', '', $_POST['mdt-image-composition-data'] ) );
			$image = \imagecreatefromstring( $data );

			// Require files that may not have been included
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Sanitize image title if passed
			$image_title = ( ! empty( $_POST['mdt-image-composition-title'] ) ) ? sanitize_text_field( $_POST['mdt-image-composition-title'] ) : '';

			// Determine file names
			$filename = ( ! empty( $image_title ) ) ? sanitize_title( $image_title ) : 'comp-' . time();
			$filename .= '.png';
			$tempnam  = wp_tempnam( $filename );

			imagepng( $image, $tempnam );

			// Save temp file
			$files = [
				'name'     => $filename,
				'tmp_name' => $tempnam,
			];

			// Pass image title if set
			$post_data = [];
			if ( ! empty( $image_title ) ) {
				$post_data['post_title'] = $image_title;
			}

			// Upload the new image
			$uploaded_id = media_handle_sideload(
				$files,
				0,
				null,
				$post_data
			);

			// Redirect to upload.php
			if ( $uploaded_id > 0 ) {

				/**
				 * Run an action when composition successfully uploaded.
				 *
				 * @param int $uploaded_id The post ID of the uploaded attachment.
				 */
				do_action( 'mdt_image_composition_uploaded', $uploaded_id );

				// Add post meta for each source image ID
				foreach ( $image_ids as $image_id ) {
					add_post_meta( $uploaded_id, 'mdt_image_composition_source_id', $image_id );
				}

				wp_redirect( get_edit_post_link( $uploaded_id, ''  ) );
				exit;
			}
		}
	}

	Image_Compositions::load();

endif;
