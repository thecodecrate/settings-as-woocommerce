<?php
/**
 * Copied from WC:
 *   https://github.com/woocommerce/woocommerce/blob/master/includes/admin/views/html-admin-settings.php
 *
 * To update:
 *   * Copy original code;
 *   * Replace `$current_tab` with `self::$current_tab`;
 *   * Replace `$current_section` with `$this->current_section`;
 *   * Replace `admin_url` with `$this->admin_url`;
 *   * Replace `self::` with `$this->`. I.e. `self::get_option` with `$this->get_option`;
 *   * Replace `woocommerce` references on HTML with <?php echo $this->id ?>
 *   * Replace `woocommerce` with `$this->id`. I.e. `$this->id . '_settings_'` with `$this->id . '_settings_'`;
 *   * Remove `global` lines (or comment it);
 *
 * ------------------------------------------
 *
 * Admin View: Settings
 *
 * @package WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tab_exists        = isset( $tabs[ self::$current_tab ] ) || has_action( $this->id . '_sections_' . self::$current_tab ) || has_action( $this->id . '_settings_' . self::$current_tab ) || has_action( $this->id . '_settings_tabs_' . self::$current_tab );
$this->current_tab_label = isset( $tabs[ self::$current_tab ] ) ? $tabs[ self::$current_tab ] : '';

if ( ! $tab_exists ) {
	// wp_safe_redirect( $this->admin_url( 'admin.php?page=wc-settings' ) );
	exit;
}
?>
<div class="wrap <?php echo $this->id ?>">
	<?php do_action( $this->id . '_before_settings_' . self::$current_tab ); ?>
	<form method="<?php echo esc_attr( apply_filters( $this->id . '_settings_form_method_tab_' . self::$current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
			<?php

			foreach ( $tabs as $slug => $label ) {
				echo '<a href="' . esc_html( $this->admin_url( 'admin.php?page=wc-settings&tab=' . esc_attr( $slug ) ) ) . '" class="nav-tab ' . ( self::$current_tab === $slug ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
			}

			do_action( $this->id . '_settings_tabs' );

			?>
		</nav>
		<h1 class="screen-reader-text"><?php echo esc_html( $this->current_tab_label ); ?></h1>
		<?php
			do_action( $this->id . '_sections_' . self::$current_tab );

			$this->show_messages();

			do_action( $this->id . '_settings_' . self::$current_tab );
			do_action( $this->id . '_settings_tabs_' . self::$current_tab ); // @deprecated hook. @todo remove in 4.0.
		?>
		<p class="submit">
			<?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
				<button name="save" class="button-primary <?php echo $this->id ?>-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', $this->id . '' ); ?>"><?php esc_html_e( 'Save changes', $this->id . '' ); ?></button>
			<?php endif; ?>
			<?php wp_nonce_field( $this->id . '-settings' ); ?>
		</p>
	</form>
	<?php do_action( $this->id . '_after_settings_' . self::$current_tab ); ?>
</div>
