<?php
/**
 * Copied from WC:
 *   https://github.com/woocommerce/woocommerce/blob/master/includes/admin/settings/class-wc-settings-page.php
 *
 * To update:
 *   * Copy original code;
 *   * We only need the `abstract class` definition: remove everything else (i.e. the `if`s around it);
 *   * Add `namespace SettingsAsWoocommerce\Woocommerce`;
 *   * Add `public $wc_id; public $wc; `;
 *   * On constructor, add `if ( ! $this->wc ) { $this->wc = new WC( $this->wc_id ); }`
 *   * Replace `woocommerce` with `$this->wc_id`. I.e. `'woocommerce_settings_'` with `$this->wc_id . '_settings_'`;
 *   * Replace `admin_url` with `$this->wc->admin_url`;
 *   * Replace `WC_Admin_Settings::` with `$this->wc->`;
 *   * Replace `$current_section` with `$this->wc->get_current_section()`;
 *   * Remove/comment `global` lines;
 *   * Rename `__construct` to `attach_hooks`;
 *   * Add a new constructor: `public function __construct() { if ($this->wc_id) { this->attach_hooks(); } }
 *   * Pass "$section" to get_settings
 *     * On calls to `get_settings`, pass `$this->wc->get_current_section()`;
 *
 * ------------------------------------------
 *
 * WC_Settings_Page.
 */
namespace SettingsAsWoocommerce\Woocommerce;

abstract class WC_Settings_Page {

	/**
	 * WC id.
	 *
	 * @var string
	 */
	public $wc_id;

	/**
	 * WC methods.
	 *
	 * @var WC
	 */
	public $wc;

	/**
	 * Setting page id.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Setting page label.
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		/**
		 * Pages can be defined standalone or through a "new Menu()" call.
		 * The latter, we set `wc_id` automatically using the calling object's slug.
		 */
		if ( $this->wc_id ) {
			$this->attach_hooks();
		}
	}

	/**
	 * Attach hooks to the WC library.
	 */
	public function attach_hooks() {
		if ( ! $this->wc ) {
			$this->wc = new WC( $this->wc_id );
		}
		add_filter( $this->wc_id . '_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( $this->wc_id . '_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( $this->wc_id . '_settings_' . $this->id, array( $this, 'output' ) );
		add_action( $this->wc_id . '_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings page ID.
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get settings page label.
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Add this page to settings.
	 *
	 * @param array $pages
	 *
	 * @return mixed
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		return apply_filters( $this->wc_id . '_get_settings_' . $this->id, array() );
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		return apply_filters( $this->wc_id . '_get_sections_' . $this->id, array() );
	}

	/**
	 * Output sections.
	 */
	public function output_sections() {
		// global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . $this->wc->admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $this->wc->get_current_section() == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		$settings = $this->get_settings( $this->wc->get_current_section() );

		$this->wc->output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		// global $current_section;

		$settings = $this->get_settings( $this->wc->get_current_section() );
		$this->wc->save_fields( $settings );

		if ( $this->wc->get_current_section() ) {
			do_action( $this->wc_id . '_update_options_' . $this->id . '_' . $this->wc->get_current_section() );
		}
	}
}
