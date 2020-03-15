<?php
/**
 * Class Menu.
 *
 */

/** Our namespace */
namespace SettingsAsWoocommerce;

/** Aliases. */
use SettingsAsWoocommerce\Woocommerce\WC;

/**
 * Class Menu.
 *
 * Usage I - Custom menu with no sub-menus:
 * ```
 *   $my_menu = new Menu( 'My Menu', 'my_menu' );
 *   $my_menu
 *     ->add_tab( new Tab1() )
 *     ->add_tab( new Tab2() );
 * ```
 *
 * Usage II - Custom menu with a sub-menu:
 * ```
 *   $my_menu    = new Menu( 'My Menu', 'my_menu' );
 *   $my_submenu = new Submenu( 'My Submenu', 'my_submenu', 'my_menu' );
 *   $my_submenu
 *     ->add_tab( new Tab1() )
 *     ->add_tab( new Tab2() );
 * ```
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Menu extends AbstractMenuItem {

	/**
	 * [OPTIONAL] The URL to the icon to be used for this menu.
	 * Set with `set_icon_url()`.
	 *
	 * @var string
	 */
	protected $icon_url;

	/**
	 * Submenu count. Used to fix the "1st submenu item has the same name as the menu" WP's behavior.
	 *
	 * @var int
	 */
	public $count_submenu = 0;

	/**
	 * Set the icon URL.
	 *
	 * @param string $icon_url The icon url.
	 *
	 * @return AbstractMenuItem
	 */
	public function set_icon_url( $icon_url ) {
		$this->icon_url = $icon_url;

		return $this;
	}

	/**
	 * Constructor.
	 * Creates a new menu item.
	 *
	 * @param string $label The menu label.
	 * @param string $slug The menu slug - needs to be unique within the system.
	 */
	public function __construct( $label, $slug ) {
		$this->label = $label;
		$this->wc    = new WC();
		$this->wc->set_id( $slug );
		parent::__construct( $slug );
	}

	/**
	 * Add tab directly to the menu (no submenus).
	 *
	 * @param WC_Settings_Page $tab The tab.
	 */
	public function add_tab( $tab ) {
		/** Start tab's render/saving hooks. */
		$tab->wc_id = $this->slug;
		$tab->wc    = $this->wc;
		$tab->attach_hooks();

		/** Parent's code. */
		parent::add_tab( $tab );

		/** Allow chain commands. */
		return $this;
	}

	/**
	 * Show menu.
	 *
	 * @return void
	 */
	public function admin_menu_callback() {
		/** Call `add_menu_page()`. */
		$this->page_hook_suffix = add_menu_page(
			$this->page_title ? $this->page_title : $this->label,
			$this->label,
			$this->capability ? $this->capability : 'manage_options',
			$this->slug,
			array( $this->wc, 'output' ),
			$this->icon_url ? $this->icon_url : '',
			$this->position
		);

		/** Allow custom code to hook here. */
		do_action( "menu_item_added_{$this->slug}", $this->page_hook_suffix );
	}
}
