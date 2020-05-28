<?php
/**
 * Class WC.
 *   This is a MOCK class. Replaces the real WooCommerce methods for our SettingsAsWoocommerce.
 */

/** Our namespace. */
namespace SettingsAsWoocommerce\Woocommerce;

/** Load composer libraries. */
require_once __DIR__ . '/../../vendor/autoload.php';

/** Aliases. */
use Automattic\Jetpack\Constants;

/**
 * Class WC.
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class WC {

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/class-woocommerce.php
	 *
	 * To update:
	 *   * Copy original code;
	 *
	 * ------------------------------------------
	 *
	 * WooCommerce version.
	 *
	 * @var string
	 */
	public $version = '4.0.0';

	/**
	 * Our menu item id.
	 * Replaces the 'woocommerce' prefix on actions and filters.
	 *
	 * @var string $id.
	 */
	protected $id = 'settings_as_wc';

	/**
	 * Mock "WC()->countries".
	 *
	 * @var array $countries.
	 */
	public $countries = [];

	/**
	 * Error messages.
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Update messages.
	 *
	 * @var array
	 */
	private $messages = array();

	/**
	 * Current tab (from `$_GET['tab']`).
	 *
	 * @var string
	 */
	public static $current_tab;

	/**
	 * Current section (`from `$_REQUEST['section']`).
	 *
	 * @var string
	 */
	public static $current_section;

	/**
	 * Setter method for $id.
	 *
	 * @param string $id The menu item id.
	 */
	public function set_id( $id ) {
		$this->id = $id;

		return $this;
	}

	/**
	 * Getter method for $id.
	 *
	 * @return string The menu item id.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Setter for $current_tab.
	 *
	 * @param string $tab The current tab.
	 */
	public function set_current_tab( $tab ) {
		self::$current_tab = $tab;

		return $this;
	}

	/**
	 * Getter for $current_tab.
	 *
	 * @return string
	 */
	public function get_current_tab() {
		return self::$current_tab;
	}

	/**
	 * Getter for $current_section.
	 *
	 * @return string
	 */
	public function get_current_section() {
		return self::$current_section;
	}

	/**
	 * Constructor.
	 *
	 * @param string $id [OPTIONAL] The menu item id.
	 */
	public function __construct( $id = null ) {
		$this->id = $id;
	}

	/**
	 * Bugfix: The original version gets the WC's plugin base URL.
	 * For our library, we should get the library URL path instead.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Bugfix: WC has hardcoded its WC links on the template. I.e. "/admin.php?page=wc-settings"
	 *
	 * To minimize changes on the original code, and considering that all these hardcoded links are going
	 * through WP's `admin_url` function, we create this `$this->admin_url` that slightly changes the passed URL
	 * to `/{current admin URL}.php?page={current page}`.
	 *
	 * @return string
	 */
	public function admin_url( $path = null, $scheme = null ) {
		/** Some people still use PHP 5.5 and below, which doesn't support
		 * default arguments, even though WP min requirements is 5.6
		 * since 2019. */
		$path   = $path ? $path : '';
		$scheme = $scheme ? $scheme : 'admin';

		$current_admin_url = admin_url( basename( parse_url( add_query_arg( null, null ), PHP_URL_PATH ) ), $scheme );
		$query             = parse_url( $path, PHP_URL_QUERY );

		/** Merge components to form final URL. */
		$url = join( '?', array_filter( array( $current_admin_url, $query ) ) );

		/** Change `page` query argument to the current page. */
		$url = add_query_arg( 'page', $this->id, $url );

		return $url;
	}

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/admin/wc-admin-functions.php#L144
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Replace `function` with `public function`;
	 *   * Remove first `if`;
	 *   * Replace `WC_Admin_Settings::` with `$this->`;
	 *
	 * ------------------------------------------
	 *
	 * Output admin fields.
	 *
	 * Loops though the woocommerce options array and outputs each field.
	 *
	 * @param array $options Opens array to output.
	 */
	public function woocommerce_admin_fields( $options ) {

		// if ( ! class_exists( 'WC_Admin_Settings', false ) ) {
		// 	include dirname( __FILE__ ) . '/class-wc-admin-settings.php';
		// }

		$this->output_fields( $options );
	}

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/admin/wc-admin-functions.php#L159
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Replace `function` with `public function`;
	 *   * Remove first `if`;
	 *   * Replace `WC_Admin_Settings::` with `$this->`;
	 *
	 * ------------------------------------------
	 *
	 * Update all settings which are passed.
	 *
	 * @param array $options Option fields to save.
	 * @param array $data Passed data.
	 */
	public function woocommerce_update_options( $options, $data = null ) {

		// if ( ! class_exists( 'WC_Admin_Settings', false ) ) {
		// 	include dirname( __FILE__ ) . '/class-wc-admin-settings.php';
		// }

		$this->save_fields( $options, $data );
	}

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/class-woocommerce.php
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Replace "woocommerce" with "$this->id". I.e. "'woocommerce_settings_'" with "$this->id . '_settings_'":
	 *
	 * ------------------------------------------
	 *
	 * Returns true if the request is a non-legacy REST API request.
	 *
	 * Legacy REST requests should still run some extra code for backwards compatibility.
	 *
	 * @todo: replace this function once core WP function is available: https://core.trac.wordpress.org/ticket/42061.
	 *
	 * @return bool
	 */
	public function is_rest_api_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix         = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return apply_filters( $this->id . '_is_rest_api_request', $is_rest_api_request );
	}

	/**
	 * Mock function. The original one includes the core tabs/pages. Here we do nothing.
	 *
	 * @return void
	 */
	public function get_settings_pages() {
	}

	/**
	 * Mock function. The original checks for the download folder permissions. Here we do nothing.
	 *
	 * @return void
	 */
	public function check_download_folder_protection() {
	}

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-settings.php#L71
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Replace `public static function` with `public function`;
	 *   * Replace "woocommerce" with "$this->id". I.e. "'woocommerce_settings_'" with "$this->id . '_settings_'":
	 *   * Replace `self::` with `$this->`;
	 *   * Replace `WC()` with `$this`;
	 *   * Comment `global` line;
	 *   * Replace `$current_tab` with `self::$current_tab`;
	 *   * Remove/comment lines with `this->query`;
	 *
	 * ------------------------------------------
	 *
	 * Save the settings.
	 */
	public function save() {
		// global $current_tab;

		check_admin_referer( $this->id . '-settings' );

		// Trigger actions.
		do_action( $this->id . '_settings_save_' . self::$current_tab );
		do_action( $this->id . '_update_options_' . self::$current_tab );
		do_action( $this->id . '_update_options' );

		$this->add_message( __( 'Your settings have been saved.', $this->id ) );
		$this->check_download_folder_protection();

		// Clear any unwanted data and flush rules.
		update_option( $this->id . '_queue_flush_rewrite_rules', 'yes' );
		// $this->query->init_query_vars();
		// $this->query->add_endpoints();

		do_action( $this->id . '_settings_saved' );
	}

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-settings.php#L97
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Replace `public static function` with `public function`;
	 *   * Replace `self::$` with `$this->`;
	 *
	 * ------------------------------------------
	 *
	 * Add a message.
	 *
	 * @param string $text Message.
	 */
	public function add_message( $text ) {
		$this->messages[] = $text;
	}

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-settings.php#L106
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Replace `public static function` with `public function`;
	 *   * Replace `self::$` with `$this->`;
	 *
	 * ------------------------------------------
	 *
	 * Add an error.
	 *
	 * @param string $text Message.
	 */
	public function add_error( $text ) {
		$this->errors[] = $text;
	}

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-settings.php#L113
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Replace `public static function` with `public function`;
	 *   * Replace `self::$` with `$this->`;
	 *
	 * ------------------------------------------
	 *
	 * Output messages + errors.
	 */
	public function show_messages() {
		if ( count( $this->errors ) > 0 ) {
			foreach ( $this->errors as $error ) {
				echo '<div id="message" class="error inline"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		} elseif ( count( $this->messages ) > 0 ) {
			foreach ( $this->messages as $message ) {
				echo '<div id="message" class="updated inline"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-menus.php#L114
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Remove or comment global line;
	 *   * Replace `$current_tab` with `self::$current_tab`;
	 *   * Replace `$current_section` with `self::$current_section`;
	 *   * Replace `'wc-settings'` with `$this->id`;
	 *   * Replace `woocommerce` with `$this->id`. I.e. `woocommerce_settings_'` with `$this->id . '_settings_'`;
	 *   * Replace `WC_Admin_Settings::` with `$this->`;
	 *   * Call `$this->fix_default_tab();` after `self::$current_tab = `
	 *
	 * ------------------------------------------
	 *
	 * Handle saving of settings.
	 *
	 * @return void
	 */
	public function save_settings() {
		// global $current_tab, $current_section;

		// We should only save on the settings page.
		if ( ! is_admin() || ! isset( $_GET['page'] ) || $this->id !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// Include settings pages.
		$this->get_settings_pages();

		// Get current tab/section.
		self::$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( wp_unslash( $_GET['tab'] ) ); // WPCS: input var okay, CSRF ok.
		self::$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( wp_unslash( $_REQUEST['section'] ) ); // WPCS: input var okay, CSRF ok.
		$this->fix_default_tab();

		// Save settings if data has been posted.
		if ( '' !== self::$current_section && apply_filters( $this->id . '_save_settings_' . self::$current_tab . '_' . self::$current_section, ! empty( $_POST['save'] ) ) ) { // WPCS: input var okay, CSRF ok.
			$this->save();
		} elseif ( '' === self::$current_section && apply_filters( $this->id . '_save_settings_' . self::$current_tab, ! empty( $_POST['save'] ) ) ) { // WPCS: input var okay, CSRF ok.
			$this->save();
		}
	}

	/**
	 * Bugfix: by default, WC shows the "general" tab if none is defined.
	 * For this library, the default should be the first user-defined tab in the tabs list.
	 */
	public function fix_default_tab() {
		if ( 'general' === self::$current_tab ) {
			$tabs = apply_filters( $this->id . '_settings_tabs_array', array() );
			if ( ! array_key_exists( self::$current_tab, $tabs ) ) {
				self::$current_tab = count( $tabs ) ? array_keys( $tabs )[0] : null;
			}
		}
	}

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-settings.php#L693
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Change method signature from "function" to "public function";
	 *   * Replace "wc_" calls with "$this->wc_", i.e. "$this->wc_help_tip";
	 *
	 * ------------------------------------------
	 *
	 * Helper function to get the formatted description and tip HTML for a
	 * given form field. Plugins can call this when implementing their own custom
	 * settings types.
	 *
	 * @param  array $value The form field value array.
	 * @return array The description and tip as a 2 element array.
	 */
	public function get_field_description( $value ) {
		$description  = '';
		$tooltip_html = '';

		if ( true === $value['desc_tip'] ) {
			$tooltip_html = $value['desc'];
		} elseif ( ! empty( $value['desc_tip'] ) ) {
			$description  = $value['desc'];
			$tooltip_html = $value['desc_tip'];
		} elseif ( ! empty( $value['desc'] ) ) {
			$description = $value['desc'];
		}

		if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ), true ) ) {
			$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
		} elseif ( $description && in_array( $value['type'], array( 'checkbox' ), true ) ) {
			$description = wp_kses_post( $description );
		} elseif ( $description ) {
			$description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
		}

		if ( $tooltip_html && in_array( $value['type'], array( 'checkbox' ), true ) ) {
			$tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
		} elseif ( $tooltip_html ) {
			$tooltip_html = $this->wc_help_tip( $tooltip_html );
		}

		return array(
			'description'  => $description,
			'tooltip_html' => $tooltip_html,
		);
	}

	/**
	 * Copied from WC:
	 *   https://docs.woocommerce.com/wc-apidocs/source-function-wc_help_tip.html#1397-1414
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Change method signature from "function" to "public function";
	 *   * Replace "wc_" calls with "$this->wc_", i.e. "$this->wc_sanitize_tooltip";
	 *   * Replace `woocommerce` HTML references with `$this->id`;
	 *
	 * ------------------------------------------
	 *
	 * Display a WooCommerce help tip.
	 *
	 * @since  2.5.0
	 *
	 * @param  string $tip        Help tip text.
	 * @param  bool   $allow_html Allow sanitized HTML if true or escape.
	 * @return string
	 */
	public function wc_help_tip( $tip, $allow_html = false ) {
		if ( $allow_html ) {
			$tip = $this->wc_sanitize_tooltip( $tip );
		} else {
			$tip = esc_attr( $tip );
		}

		return '<span class="' . $this->id . '-help-tip" data-tip="' . $tip . '"></span>';
	}

	/**
	 * Copied from WC:
	 *   https://docs.woocommerce.com/wc-apidocs/source-function-wc_sanitize_tooltip.html#419-443
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Change method signature from "function" to "public function";
	 *
	 * ------------------------------------------
	 *
	 * Sanitize a string destined to be a tooltip.
	 *
	 * @since  2.3.10 Tooltips are encoded with htmlspecialchars to prevent XSS. Should not be used in conjunction with esc_attr()
	 * @param  string $var Data to sanitize.
	 * @return string
	 */
	public function wc_sanitize_tooltip( $var ) {
		return htmlspecialchars(
			wp_kses(
				html_entity_decode( $var ),
				array(
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'small'  => array(),
					'span'   => array(),
					'ul'     => array(),
					'li'     => array(),
					'ol'     => array(),
					'p'      => array(),
				)
			)
		);
	}

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-settings.php#L163
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Change method signature from "public static function" to "public function";
	 *
	 * ------------------------------------------
	 *
	 * Get a setting from the settings API.
	 *
	 * @param string $option_name Option name.
	 * @param mixed  $default     Default value.
	 * @return mixed
	 */
	public function get_option( $option_name, $default = '' ) {
		if ( ! $option_name ) {
			return $default;
		}

		// Array value.
		if ( strstr( $option_name, '[' ) ) {

			parse_str( $option_name, $option_array );

			// Option name is first key.
			$option_name = current( array_keys( $option_array ) );

			// Get value.
			$option_values = get_option( $option_name, '' );

			$key = key( $option_array[ $option_name ] );

			if ( isset( $option_values[ $key ] ) ) {
				$option_value = $option_values[ $key ];
			} else {
				$option_value = null;
			}
		} else {
			// Single value.
			$option_value = get_option( $option_name, null );
		}

		if ( is_array( $option_value ) ) {
			$option_value = wp_unslash( $option_value );
		} elseif ( ! is_null( $option_value ) ) {
			$option_value = stripslashes( $option_value );
		}

		return ( null === $option_value ) ? $default : $option_value;
	}



	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-settings.php#L207
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Change method signature from "public static function" to "public function";
	 *   * Replace "self::" with "$this->". I.e. "self::get_option" with "$this->get_option";
	 *   * Replace "WC()->" with "$this->";
	 *   * Replace "wc_" calls with "$this->wc_". I.e. "wc_get_image_size" with "$this->wc_get_image_size";
	 *   * Replace "woocommerce" with "$this->id". I.e. "'woocommerce_settings_'" with "$this->id . '_settings_'":
	 *     * Search and replace: 'woocommerce' -> $this->id
	 *     * Search and replace: "woocommerce" -> $this->id
	 *     * Search and replace: 'woocommerce -> $this->id . '
	 *     * Search and replace: "woocommerce -> $this->id . "
	 *
	 * ------------------------------------------
	 *
	 * Output admin fields.
	 *
	 * Loops though the woocommerce options array and outputs each field.
	 *
	 * @param array[] $options Opens array to output.
	 */
	public function output_fields( $options ) {
		foreach ( $options as $value ) {
			if ( ! isset( $value['type'] ) ) {
				continue;
			}
			if ( ! isset( $value['id'] ) ) {
				$value['id'] = '';
			}
			if ( ! isset( $value['title'] ) ) {
				$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
			}
			if ( ! isset( $value['class'] ) ) {
				$value['class'] = '';
			}
			if ( ! isset( $value['css'] ) ) {
				$value['css'] = '';
			}
			if ( ! isset( $value['default'] ) ) {
				$value['default'] = '';
			}
			if ( ! isset( $value['desc'] ) ) {
				$value['desc'] = '';
			}
			if ( ! isset( $value['desc_tip'] ) ) {
				$value['desc_tip'] = false;
			}
			if ( ! isset( $value['placeholder'] ) ) {
				$value['placeholder'] = '';
			}
			if ( ! isset( $value['suffix'] ) ) {
				$value['suffix'] = '';
			}
			if ( ! isset( $value['value'] ) ) {
				$value['value'] = $this->get_option( $value['id'], $value['default'] );
			}

			// Custom attribute handling.
			$custom_attributes = array();

			if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
				foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			// Description handling.
			$field_description = $this->get_field_description( $value );
			$description       = $field_description['description'];
			$tooltip_html      = $field_description['tooltip_html'];

			// Switch based on type.
			switch ( $value['type'] ) {

				// Section Titles.
				case 'title':
					if ( ! empty( $value['title'] ) ) {
						echo '<h2>' . esc_html( $value['title'] ) . '</h2>';
					}
					if ( ! empty( $value['desc'] ) ) {
						echo '<div id="' . esc_attr( sanitize_title( $value['id'] ) ) . '-description">';
						echo wp_kses_post( wpautop( wptexturize( $value['desc'] ) ) );
						echo '</div>';
					}
					echo '<table class="form-table">' . "\n\n";
					if ( ! empty( $value['id'] ) ) {
						do_action( $this->id . '_settings_' . sanitize_title( $value['id'] ) );
					}
					break;

				// Section Ends.
				case 'sectionend':
					if ( ! empty( $value['id'] ) ) {
						do_action( $this->id . '_settings_' . sanitize_title( $value['id'] ) . '_end' );
					}
					echo '</table>';
					if ( ! empty( $value['id'] ) ) {
						do_action( $this->id . '_settings_' . sanitize_title( $value['id'] ) . '_after' );
					}
					break;

				// Standard text inputs and subtypes like 'number'.
				case 'text':
				case 'password':
				case 'datetime':
				case 'datetime-local':
				case 'date':
				case 'month':
				case 'time':
				case 'week':
				case 'number':
				case 'email':
				case 'url':
				case 'tel':
					$option_value = $value['value'];

					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
						</th>
						<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="<?php echo esc_attr( $value['type'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( $option_value ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
								/><?php echo esc_html( $value['suffix'] ); ?> <?php echo $description; // WPCS: XSS ok. ?>
						</td>
					</tr>
					<?php
					break;

				// Color picker.
				case 'color':
					$option_value = $value['value'];

					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
						</th>
						<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">&lrm;
							<span class="colorpickpreview" style="background: <?php echo esc_attr( $option_value ); ?>">&nbsp;</span>
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="text"
								dir="ltr"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( $option_value ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>colorpick"
								placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
								/>&lrm; <?php echo $description; // WPCS: XSS ok. ?>
								<div id="colorPickerDiv_<?php echo esc_attr( $value['id'] ); ?>" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>
						</td>
					</tr>
					<?php
					break;

				// Textarea.
				case 'textarea':
					$option_value = $value['value'];

					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
						</th>
						<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
							<?php echo $description; // WPCS: XSS ok. ?>

							<textarea
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
								><?php echo esc_textarea( $option_value ); // WPCS: XSS ok. ?></textarea>
						</td>
					</tr>
					<?php
					break;

				// Select boxes.
				case 'select':
				case 'multiselect':
					$option_value = $value['value'];

					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
						</th>
						<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
							<select
								name="<?php echo esc_attr( $value['id'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
								<?php echo 'multiselect' === $value['type'] ? 'multiple="multiple"' : ''; ?>
								>
								<?php
								foreach ( $value['options'] as $key => $val ) {
									?>
									<option value="<?php echo esc_attr( $key ); ?>"
										<?php

										if ( is_array( $option_value ) ) {
											selected( in_array( (string) $key, $option_value, true ), true );
										} else {
											selected( $option_value, (string) $key );
										}

										?>
									><?php echo esc_html( $val ); ?></option>
									<?php
								}
								?>
							</select> <?php echo $description; // WPCS: XSS ok. ?>
						</td>
					</tr>
					<?php
					break;

				// Radio inputs.
				case 'radio':
					$option_value = $value['value'];

					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
						</th>
						<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
							<fieldset>
								<?php echo $description; // WPCS: XSS ok. ?>
								<ul>
								<?php
								foreach ( $value['options'] as $key => $val ) {
									?>
									<li>
										<label><input
											name="<?php echo esc_attr( $value['id'] ); ?>"
											value="<?php echo esc_attr( $key ); ?>"
											type="radio"
											style="<?php echo esc_attr( $value['css'] ); ?>"
											class="<?php echo esc_attr( $value['class'] ); ?>"
											<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
											<?php checked( $key, $option_value ); ?>
											/> <?php echo esc_html( $val ); ?></label>
									</li>
									<?php
								}
								?>
								</ul>
							</fieldset>
						</td>
					</tr>
					<?php
					break;

				// Checkbox input.
				case 'checkbox':
					$option_value     = $value['value'];
					$visibility_class = array();

					if ( ! isset( $value['hide_if_checked'] ) ) {
						$value['hide_if_checked'] = false;
					}
					if ( ! isset( $value['show_if_checked'] ) ) {
						$value['show_if_checked'] = false;
					}
					if ( 'yes' === $value['hide_if_checked'] || 'yes' === $value['show_if_checked'] ) {
						$visibility_class[] = 'hidden_option';
					}
					if ( 'option' === $value['hide_if_checked'] ) {
						$visibility_class[] = 'hide_options_if_checked';
					}
					if ( 'option' === $value['show_if_checked'] ) {
						$visibility_class[] = 'show_options_if_checked';
					}

					if ( ! isset( $value['checkboxgroup'] ) || 'start' === $value['checkboxgroup'] ) {
						?>
							<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
								<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?></th>
								<td class="forminp forminp-checkbox">
									<fieldset>
						<?php
					} else {
						?>
							<fieldset class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
						<?php
					}

					if ( ! empty( $value['title'] ) ) {
						?>
							<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ); ?></span></legend>
						<?php
					}

					?>
						<label for="<?php echo esc_attr( $value['id'] ); ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="checkbox"
								class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
								value="1"
								<?php checked( $option_value, 'yes' ); ?>
								<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
							/> <?php echo $description; // WPCS: XSS ok. ?>
						</label> <?php echo $tooltip_html; // WPCS: XSS ok. ?>
					<?php

					if ( ! isset( $value['checkboxgroup'] ) || 'end' === $value['checkboxgroup'] ) {
						?>
									</fieldset>
								</td>
							</tr>
						<?php
					} else {
						?>
							</fieldset>
						<?php
					}
					break;

				// Image width settings. @todo deprecate and remove in 4.0. No longer needed by core.
				case 'image_width':
					$image_size       = str_replace( '_image_size', '', $value['id'] );
					$size             = $this->wc_get_image_size( $image_size );
					$width            = isset( $size['width'] ) ? $size['width'] : $value['default']['width'];
					$height           = isset( $size['height'] ) ? $size['height'] : $value['default']['height'];
					$crop             = isset( $size['crop'] ) ? $size['crop'] : $value['default']['crop'];
					$disabled_attr    = '';
					$disabled_message = '';

					if ( has_filter( $this->id . '_get_image_size_' . $image_size ) ) {
						$disabled_attr    = 'disabled="disabled"';
						$disabled_message = '<p><small>' . esc_html__( 'The settings of this image size have been disabled because its values are being overwritten by a filter.', $this->id ) . '</small></p>';
					}

					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
						<label><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html . $disabled_message; // WPCS: XSS ok. ?></label>
					</th>
						<td class="forminp image_width_settings">

							<input name="<?php echo esc_attr( $value['id'] ); ?>[width]" <?php echo $disabled_attr; // WPCS: XSS ok. ?> id="<?php echo esc_attr( $value['id'] ); ?>-width" type="text" size="3" value="<?php echo esc_attr( $width ); ?>" /> &times; <input name="<?php echo esc_attr( $value['id'] ); ?>[height]" <?php echo $disabled_attr; // WPCS: XSS ok. ?> id="<?php echo esc_attr( $value['id'] ); ?>-height" type="text" size="3" value="<?php echo esc_attr( $height ); ?>" />px

							<label><input name="<?php echo esc_attr( $value['id'] ); ?>[crop]" <?php echo $disabled_attr; // WPCS: XSS ok. ?> id="<?php echo esc_attr( $value['id'] ); ?>-crop" type="checkbox" value="1" <?php checked( 1, $crop ); ?> /> <?php esc_html_e( 'Hard crop?', $this->id ); ?></label>

							</td>
					</tr>
					<?php
					break;

				// Single page selects.
				case 'single_select_page':
					$args = array(
						'name'             => $value['id'],
						'id'               => $value['id'],
						'sort_column'      => 'menu_order',
						'sort_order'       => 'ASC',
						'show_option_none' => ' ',
						'class'            => $value['class'],
						'echo'             => false,
						'selected'         => absint( $value['value'] ),
						'post_status'      => 'publish,private,draft',
					);

					if ( isset( $value['args'] ) ) {
						$args = wp_parse_args( $value['args'], $args );
					}

					?>
					<tr valign="top" class="single_select_page">
						<th scope="row" class="titledesc">
							<label><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
						</th>
						<td class="forminp">
							<?php echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', $this->id ) . "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) ); // WPCS: XSS ok. ?> <?php echo $description; // WPCS: XSS ok. ?>
						</td>
					</tr>
					<?php
					break;

				// Single country selects.
				case 'single_select_country':
					$country_setting = (string) $value['value'];

					if ( strstr( $country_setting, ':' ) ) {
						$country_setting = explode( ':', $country_setting );
						$country         = current( $country_setting );
						$state           = end( $country_setting );
					} else {
						$country = $country_setting;
						$state   = '*';
					}
					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
						</th>
						<td class="forminp"><select name="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" data-placeholder="<?php esc_attr_e( 'Choose a country / region&hellip;', $this->id ); ?>" aria-label="<?php esc_attr_e( 'Country / Region', $this->id ); ?>" class="wc-enhanced-select">
							<?php $this->countries->country_dropdown_options( $country, $state ); ?>
						</select> <?php echo $description; // WPCS: XSS ok. ?>
						</td>
					</tr>
					<?php
					break;

				// Country multiselects.
				case 'multi_select_countries':
					$selections = (array) $value['value'];

					if ( ! empty( $value['options'] ) ) {
						$countries = $value['options'];
					} else {
						$countries = $this->countries->countries;
					}

					asort( $countries );
					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
						</th>
						<td class="forminp">
							<select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" style="width:350px" data-placeholder="<?php esc_attr_e( 'Choose countries / regions&hellip;', $this->id ); ?>" aria-label="<?php esc_attr_e( 'Country / Region', $this->id ); ?>" class="wc-enhanced-select">
								<?php
								if ( ! empty( $countries ) ) {
									foreach ( $countries as $key => $val ) {
										echo '<option value="' . esc_attr( $key ) . '"' . $this->wc_selected( $key, $selections ) . '>' . esc_html( $val ) . '</option>'; // WPCS: XSS ok.
									}
								}
								?>
							</select> <?php echo ( $description ) ? $description : ''; // WPCS: XSS ok. ?> <br /><a class="select_all button" href="#"><?php esc_html_e( 'Select all', $this->id ); ?></a> <a class="select_none button" href="#"><?php esc_html_e( 'Select none', $this->id ); ?></a>
						</td>
					</tr>
					<?php
					break;

				// Days/months/years selector.
				case 'relative_date_selector':
					$periods      = array(
						'days'   => __( 'Day(s)', $this->id ),
						'weeks'  => __( 'Week(s)', $this->id ),
						'months' => __( 'Month(s)', $this->id ),
						'years'  => __( 'Year(s)', $this->id ),
					);
					$option_value = $this->wc_parse_relative_date_option( $value['value'] );
					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
						</th>
						<td class="forminp">
						<input
								name="<?php echo esc_attr( $value['id'] ); ?>[number]"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="number"
								style="width: 80px;"
								value="<?php echo esc_attr( $option_value['number'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								step="1"
								min="1"
								<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
							/>&nbsp;
							<select name="<?php echo esc_attr( $value['id'] ); ?>[unit]" style="width: auto;">
								<?php
								foreach ( $periods as $value => $label ) {
									echo '<option value="' . esc_attr( $value ) . '"' . selected( $option_value['unit'], $value, false ) . '>' . esc_html( $label ) . '</option>';
								}
								?>
							</select> <?php echo ( $description ) ? $description : ''; // WPCS: XSS ok. ?>
						</td>
					</tr>
					<?php
					break;

				// Default: run an action.
				default:
					do_action( $this->id . '_admin_field_' . $value['type'], $value );
					break;
			}
		}
	}

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-settings.php#L130
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Change method signature from `public static function` to `public function`;
	 *   * Replace `self::` with `$this->`. I.e. `self::get_option` with `$this->get_option`;
	 *   * Replace `WC()->` with `$this->`;
	 *   * Replace `woocommerce` with `$this->id`. I.e. `'woocommerce_settings_'` with `$this->id . '_settings_'`;
	 *
	 * ------------------------------------------
	 *
	 * Settings page.
	 *
	 * Handles the display of the main woocommerce settings page in admin.
	 */
	public function output() {
		global $current_section, $current_tab;

		$suffix = Constants::is_true( 'SCRIPT_DEBUG' ) ? '' : '.min';

		do_action( $this->id . '_settings_start' );

		wp_enqueue_script( $this->id . '_settings', $this->plugin_url() . '/assets/js/admin/settings' . $suffix . '.js', array( 'jquery', 'wp-util', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris', 'selectWoo' ), $this->version, true );

		wp_localize_script(
			$this->id . '_settings',
			$this->id . '_settings_params',
			array(
				'i18n_nav_warning'                    => __( 'The changes you made will be lost if you navigate away from this page.', $this->id ),
				'i18n_moved_up'                       => __( 'Item moved up', $this->id ),
				'i18n_moved_down'                     => __( 'Item moved down', $this->id ),
				'i18n_no_specific_countries_selected' => __( 'Selecting no country / region to sell to prevents from completing the checkout. Continue anyway?', $this->id ),
			)
		);

		// Get tabs for the settings page.
		$tabs = apply_filters( $this->id . '_settings_tabs_array', array() );

		include dirname( __FILE__ ) . '/views/html-admin-settings.php';
	}


	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/a7d57a248ed03dac7bad119e71f83dd961f43cb3/includes/admin/class-wc-admin-settings.php#L733
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Change method signature from "public static function" to "public function";
	 *   * Replace "self::" with "$this->". I.e. "self::get_option" with "$this->get_option";
	 *   * Replace "WC()->" with "$this->";
	 *   * Replace "wc_" calls with "$this->wc_". I.e. "wc_get_image_size" with "$this->wc_get_image_size";
	 *   * Replace "woocommerce" with "$this->id". I.e. "'woocommerce_settings_'" with "$this->id . '_settings_'":
	 *   * Replace "array_map( 'wc_clean', ...)" reference with "array_map( array( $this, 'wc_clean' ), ...)";
	 *
	 * ------------------------------------------
	 *
	 * Save admin fields.
	 *
	 * Loops though the woocommerce options array and outputs each field.
	 *
	 * @param array $options Options array to output.
	 * @param array $data    Optional. Data to use for saving. Defaults to $_POST.
	 * @return bool
	 */
	public function save_fields( $options, $data = null ) {
		if ( is_null( $data ) ) {
			$data = $_POST; // WPCS: input var okay, CSRF ok.
		}
		if ( empty( $data ) ) {
			return false;
		}

		// Options to update will be stored here and saved later.
		$update_options   = array();
		$autoload_options = array();

		// Loop options and get values to save.
		foreach ( $options as $option ) {
			if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) || ( isset( $option['is_option'] ) && false === $option['is_option'] ) ) {
				continue;
			}

			// Get posted value.
			if ( strstr( $option['id'], '[' ) ) {
				parse_str( $option['id'], $option_name_array );
				$option_name  = current( array_keys( $option_name_array ) );
				$setting_name = key( $option_name_array[ $option_name ] );
				$raw_value    = isset( $data[ $option_name ][ $setting_name ] ) ? wp_unslash( $data[ $option_name ][ $setting_name ] ) : null;
			} else {
				$option_name  = $option['id'];
				$setting_name = '';
				$raw_value    = isset( $data[ $option['id'] ] ) ? wp_unslash( $data[ $option['id'] ] ) : null;
			}

			// Format the value based on option type.
			switch ( $option['type'] ) {
				case 'checkbox':
					$value = '1' === $raw_value || 'yes' === $raw_value ? 'yes' : 'no';
					break;
				case 'textarea':
					$value = wp_kses_post( trim( $raw_value ) );
					break;
				case 'multiselect':
				case 'multi_select_countries':
					$value = array_filter( array_map( array( $this, 'wc_clean' ), (array) $raw_value ) );
					break;
				case 'image_width':
					$value = array();
					if ( isset( $raw_value['width'] ) ) {
						$value['width']  = $this->wc_clean( $raw_value['width'] );
						$value['height'] = $this->wc_clean( $raw_value['height'] );
						$value['crop']   = isset( $raw_value['crop'] ) ? 1 : 0;
					} else {
						$value['width']  = $option['default']['width'];
						$value['height'] = $option['default']['height'];
						$value['crop']   = $option['default']['crop'];
					}
					break;
				case 'select':
					$allowed_values = empty( $option['options'] ) ? array() : array_map( 'strval', array_keys( $option['options'] ) );
					if ( empty( $option['default'] ) && empty( $allowed_values ) ) {
						$value = null;
						break;
					}
					$default = ( empty( $option['default'] ) ? $allowed_values[0] : $option['default'] );
					$value   = in_array( $raw_value, $allowed_values, true ) ? $raw_value : $default;
					break;
				case 'relative_date_selector':
					$value = $this->wc_parse_relative_date_option( $raw_value );
					break;
				default:
					$value = $this->wc_clean( $raw_value );
					break;
			}

			/**
			 * Fire an action when a certain 'type' of field is being saved.
			 *
			 * @deprecated 2.4.0 - doesn't allow manipulation of values!
			 */
			if ( has_action( $this->id . '_update_option_' . sanitize_title( $option['type'] ) ) ) {
				$this->wc_deprecated_function( 'The ' . $this->id . '_update_option_X action', '2.4.0', $this->id . '_admin_settings_sanitize_option filter' );
				do_action( $this->id . '_update_option_' . sanitize_title( $option['type'] ), $option );
				continue;
			}

			/**
			 * Sanitize the value of an option.
			 *
			 * @since 2.4.0
			 */
			$value = apply_filters( $this->id . '_admin_settings_sanitize_option', $value, $option, $raw_value );

			/**
			 * Sanitize the value of an option by option name.
			 *
			 * @since 2.4.0
			 */
			$value = apply_filters( $this->id . "_admin_settings_sanitize_option_$option_name", $value, $option, $raw_value );

			if ( is_null( $value ) ) {
				continue;
			}

			// Check if option is an array and handle that differently to single values.
			if ( $option_name && $setting_name ) {
				if ( ! isset( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = get_option( $option_name, array() );
				}
				if ( ! is_array( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = array();
				}
				$update_options[ $option_name ][ $setting_name ] = $value;
			} else {
				$update_options[ $option_name ] = $value;
			}

			$autoload_options[ $option_name ] = isset( $option['autoload'] ) ? (bool) $option['autoload'] : true;

			/**
			 * Fire an action before saved.
			 *
			 * @deprecated 2.4.0 - doesn't allow manipulation of values!
			 */
			do_action( $this->id . '_update_option', $option );
		}

		// Save all options in our array.
		foreach ( $update_options as $name => $value ) {
			update_option( $name, $value, $autoload_options[ $name ] ? 'yes' : 'no' );
		}

		return true;
	}

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/wc-formatting-functions.php#L386
	 *
	 * To update:
	 *   * Copy and paste original code;
	 *   * Replace `function` with `public function`;
	 *   * Replace `'wp_clean'` reference with `array( $this, 'wc_clean' )`
	 *
	 * ------------------------------------------
	 *
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 *
	 * @param string|array $var Data to sanitize.
	 * @return string|array
	 */
	public function wc_clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( array( $this, 'wc_clean' ), $var );
		}
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}

	/**
	 * Copied from WC:
	 *   https://docs.woocommerce.com/wp-content/images/wc-apidocs/source-function-wc_parse_relative_date_option.html#1416-1446
	 *
	 * To update:
	 *   * Copy and paste original code;
	 *   * Replace `function` with `public function`;
	 *
	 * ------------------------------------------
	 *
	 * Parse a relative date option from the settings API into a standard format.
	 *
	 * @since 3.4.0
	 * @param mixed $raw_value Value stored in DB.
	 * @return array Nicely formatted array with number and unit values.
	 */
	public function wc_parse_relative_date_option( $raw_value ) {
		$periods = array(
			'days'   => __( 'Day(s)', $this->id ),
			'weeks'  => __( 'Week(s)', $this->id ),
			'months' => __( 'Month(s)', $this->id ),
			'years'  => __( 'Year(s)', $this->id ),
		);

		$value = wp_parse_args(
			(array) $raw_value,
			array(
				'number' => '',
				'unit'   => 'days',
			)
		);

		$value['number'] = ! empty( $value['number'] ) ? absint( $value['number'] ) : '';

		if ( ! in_array( $value['unit'], array_keys( $periods ), true ) ) {
			$value['unit'] = 'days';
		}

		return $value;
	}

	/**
	 * Copied from WC:
	 *   https://github.com/woocommerce/woocommerce/blob/master/includes/wc-conditional-functions.php#L266
	 *
	 * To update:
	 *   * Copy and paste original code;
	 *   * Replace `function` with `public function`;
	 *
	 * ------------------------------------------
	 *
	 * Is_ajax - Returns true when the page is loaded via ajax.
	 *
	 * @return bool
	 */
	public function is_ajax() {
		return function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' );
	}

	/**
	 * Copied from WC:
	 *   https://docs.woocommerce.com/wc-apidocs/source-function-wc_deprecated_function.html#36-55
	 *
	 * To update:
	 *   * Copy original code;
	 *   * Change method signature from "public static function" to "public function";
	 *   * Replace "WC()->" with "$this->";
	 *   * Replace "is_ajax" with "$this->is_ajax";
	 *
	 * ------------------------------------------
	 *
	 * Wrapper for deprecated functions so we can apply some extra logic.
	 *
	 * @since 3.0.0
	 * @param string $function Function used.
	 * @param string $version Version the message was added in.
	 * @param string $replacement Replacement for the called function.
	 */
	public function wc_deprecated_function( $function, $version, $replacement = null ) {
		// @codingStandardsIgnoreStart
		if ( $this->is_ajax() || $this->is_rest_api_request() ) {
			do_action( 'deprecated_function_run', $function, $replacement, $version );
			$log_string  = "The {$function} function is deprecated since version {$version}.";
			$log_string .= $replacement ? " Replace with {$replacement}." : '';
			error_log( $log_string );
		} else {
			_deprecated_function( $function, $version, $replacement );
		}
		// @codingStandardsIgnoreEnd
	}

}
