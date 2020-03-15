<?php
/**
 * Class Tab.
 *
 * This class wraps "WC_Settings_Page" and rename it for naming consistency.
 * It also adds some extra functionality (ex. `get_assets()` ).
 *
 * To use it, create a new class extending this one. Define a `get_settings()` to show the
 * settings tab, and a `save()` to save the settings.
 *
 * @package SettingsAsWoocommerce
 */

/** Our namespace. */
namespace SettingsAsWoocommerce;

/**
 * Class Tab.
 */
abstract class Tab extends Woocommerce\WC_Settings_Page {

	/**
	 * The Menu/Submenu that holds this tab.
	 *
	 * @var Menu|Submenu
	 */
	public $menu_submenu;

	/**
	 * Helper methods.
	 *
	 * @var Helper
	 */
	public $helpers;

	/**
	 * This is a helper method to load JS and CSS.
	 * It loads assets only when the tab is opened.
	 * It doesn't load them if on a different tab or settings page.
	 * The paths are all relatives to the plugin root
	 *
	 * @param string $current_section The current section.
	 *
	 * @return array An array with relative paths. Ex.: `['assets/index.js', 'assets/index.css']`.
	 */
	public function get_assets() {
		return array();
	}

	/**
	 * Helper method to load inline CSS.
	 * It loads assets only when the tab is opened.
	 *
	 * @param string $current_section The current section.
	 *
	 * @return string Example 'body { background: red }'
	 */
	public function get_inline_css() {
		return null;
	}

	/**
	 * Helper method to load inline JS.
	 * It loads assets only when the tab is opened.
	 *
	 * @param string $current_section The current section.
	 *
	 * @return string Example 'alert(1)'
	 */
	public function get_inline_js() {
		return null;
	}

	/**
	 * Attach hooks to the WC library.
	 */
	public function attach_hooks() {
		/** Create WC object, hook actions/filters. */
		parent::attach_hooks();

		/** Create a dynamic hook for when the page is loaded. */
		add_action( "menu_item_added_{$this->wc_id}", array( $this, 'menu_added_calback' ) );

		/** Load assets when page is loaded. */
		add_action( "{$this->wc_id}_load_{$this->id}", array( $this, 'load_assets_callback' ) );
	}

	/**
	 * Find the name of the dynamic hook "load-*".
	 * With this hook name, we add an action to be triggered once the page is rendered.
	 *
	 * @param string $page_hook The name of the dynamic hook "load-*".
	 */
	public function menu_added_calback( $page_hook ) {
		/** Filter the right tab. */
		if ( $this->wc->get_current_tab() !== $this->id ) {
			return;
		}

		add_action( "load-{$page_hook}", array( $this, 'load_tab_callback' ) );
	}

	/**
	 * Called when our tab is opened.
	 * Useful for loading JS/CSS only for an specific tab.
	 */
	public function load_tab_callback() {
		do_action( $this->wc_id . '_load_' . $this->id );
	}

	/**
	 * Triggered when the current tab is opened.
	 *
	 * @return void
	 */
	public function load_assets_callback() {
		/**
		 * Load scripts/styles for the menu page.
		 *
		 * It is not possible to enqueue the scripts here - it is too early.
		 * Instead, we hook to the proper action to deal with it.
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_callback' ) );
	}

	/**
	 * Load custom assets (JS/CSS) returned by `get_assets()`.
	 * This method is triggered when the menu/submenu page is rendered.
	 * Not triggered on any other page.
	 */
	public function admin_enqueue_scripts_callback() {
		/** Get assets. */
		$assets = $this->get_assets( $this->get_current_section() );

		/** Load each asset (file paths are relative to the plugin's root). */
		if ( ! $this->helpers ) {
			$this->helpers = new Helpers();
		}
		foreach ( $assets as $asset_path ) {
			$this->helpers->load_relative_asset( $asset_path, $this->wc_id );
		}

		/**
		 * We can't just call "wp_add_inline_style" to add an inline CSS code.
		 * Instead, we need to attach it to a registered WP's style.
		 * To bypass this limitation, we register a dummy, empty, style.
		*/
		$inline_css = $this->get_inline_css( $this->get_current_section() );
		if ( $inline_css ) {
			$handle_css = "{$this->wc_id}_dummy_handle_tab_css";
			wp_register_style( $handle_css, false );
			wp_enqueue_style( $handle_css );
			wp_add_inline_style( $handle_css, $inline_css );
		}
		$inline_js = $this->get_inline_js( $this->get_current_section() );
		if ( $inline_js ) {
			$handle_js = "{$this->wc_id}_dummy_handle_tab_js";
			wp_register_script( $handle_js, false );
			wp_enqueue_script( $handle_js );
			wp_add_inline_script( $handle_js, $inline_js );
		}
	}

	/**
	 * A wrapper for `get_current_section`,
	 *
	 * @return string The current section.
	 */
	public function get_current_section() {
		return $this->wc->get_current_section();
	}

	/**
	 * A wrapper for `woocommerce_update_options`,
	 *
	 * @param array $options Option fields to save.
	 * @param array $data Passed data.
	 */
	public function update_options( $options, $data = null ) {
		// call_user_func_array( array( $this->wc, 'woocommerce_update_options' ), func_get_args() );
		$this->wc->woocommerce_update_options( $options, $data );
	}
}
