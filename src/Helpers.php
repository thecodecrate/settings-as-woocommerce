<?php
/**
 * Class Helpers.
 * Contains a collection of helper methods.
 *
 * Usage: `$helpers = new Helpers(); $helpers->get_current_plugin()`;
 */

/** Our namespace. */
namespace SettingsAsWoocommerce;

/**
 * Class Helpers.
 */
class Helpers {
	/**
	 * Get current plugin folder's name. Ex.: "my-plugin".
	 *
	 * @return string
	 */
	public function get_current_plugin() {
		$folders = explode( '/', plugin_basename( __FILE__ ) );
		return array_shift( $folders );
	}

	/**
	 * Get the current plugin's base URL.
	 * Returns an absolute URL.
	 *
	 * @return string
	 */
	public function plugin_base_url() {
		return plugin_dir_url( '' ) . $this->get_current_plugin();
	}

	/**
	 * Get the current plugin's base path.
	 * Returns an absolute path.
	 *
	 * @return string
	 */
	public function plugin_base_path() {
		$file = __FILE__;
		$str  = plugin_basename( $file );
		return substr( $file, 0, strlen( $file ) - strlen( $str ) ) . $this->get_current_plugin();
	}

	/**
	 * Load an asset file (CSS/JS).
	 * Asset's type (CSS or JS) is inferred by its file extension.
	 * Path must be relative to the plugin's root.
	 *
	 * @param string $file_path_relative File path, relative to the plugin's root.
	 * @param string $handle_prefix Something to prefix the handle. Example: the menu/submenu's slug.
	 * @param string $handle [OPTIONAL] Passed to wp_register. If null, generates one based on the filename.
	 *
	 * @return void
	 */
	public function load_relative_asset( $file_path_relative, $handle_prefix, $handle = null ) {
		/** Get plugin's base URL/path. */
		$base_folder = $this->plugin_base_path();
		$base_url    = $this->plugin_base_url();

		/** Current's file absolute URL/path. */
		$url_absolute       = $base_url . '/' . $file_path_relative;
		$file_path_absolute = $base_folder . '/' . $file_path_relative;

		/** Check if file exists. */
		if ( ! file_exists( $file_path_absolute ) ) {
			// _doing_it_wrong( __METHOD__, "File doesn't exist - {$file_path_absolute}", '' );
			trigger_error( "File doesn't exist - {$file_path_absolute}", E_USER_WARNING );
			return;
		}

		$version = filemtime( $file_path_absolute ); /** Use the "updated_at" filesystem attribute to avoid cache issues. */
		$handle  = null === $handle ? basename( $file_path_absolute ) : $handle;

		/** Add file to WP's queue. Call the right function according to the file type CSS/JS. */
		$ext = pathinfo( $file_path_relative, PATHINFO_EXTENSION );
		if ( 'css' === $ext ) {
			wp_register_style( "{$handle_prefix}_{$handle}", $url_absolute, [], $version );
			wp_enqueue_style( "{$handle_prefix}_{$handle}" );
			return;
		}
		wp_register_script( "{$handle_prefix}_{$handle}", $url_absolute, [], $version, true );
		wp_enqueue_script( "{$handle_prefix}_{$handle}" );
	}

}
