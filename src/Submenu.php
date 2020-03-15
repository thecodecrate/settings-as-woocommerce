<?php
/**
 * Class Submenu.
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
 * Usage III - Add a sub-menu to "Users":
 * ```
 *   $my_submenu = new Submenu( 'My Submenu', 'my_submenu', 'users' );
 *   $my_submenu
 *     ->add_tab( new Tab1() )
 *     ->add_tab( new Tab2() );
 * ```

 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Submenu extends AbstractMenuItem {

	/**
	 * [REQUIRED] The parent menu of this submenu (slug).
	 * Set on the constructor or with `set_parent_menu()`.
	 *
	 * @var string
	 */
	protected $parent_slug;

	/**
	 * The parent menu of this submenu (object).
	 *
	 * @var Menu
	 */
	protected $menu;

	/**
	 * Constructor.
	 * Creates a new menu item.
	 *
	 * @param string      $label The menu label.
	 * @param string      $slug The menu slug - needs to be unique within the system.
	 * @param string|Menu $parent_menu The parent menu slug or object - ex.: 'users', 'my_menu', `$menu`.
	 */
	public function __construct( $label, $slug, $parent_menu ) {
		/** `$parent_menu` can be a slug or a Menu object. */
		if ( $parent_menu instanceof Menu ) {
			$this->menu        = $parent_menu;
			$this->parent_slug = $parent_menu->get_slug();
		} else {
			$this->parent_slug = $parent_menu;
			$this->menu        = $this->find_by_slug( $this->parent_slug, 'Menu' ); /** If not found, it is a core parent. */
		}

		/** Save params. */
		$this->label = $label;
		$this->slug  = $slug;

		/**
		 * Bugfix: WP has an odd default behavior for the 1st submenu of a custom menu.
		 * WP's standard behavior is to name the submenu the same as the menu.
		 * For the 1st submenu be renamed, it has to have its slug the same as the menu itself.
		 */
		if ( $this->menu ) {
			$this->menu->count_submenu += 1;
			if ( 1 === $this->menu->count_submenu ) {
				$this->slug = $this->parent_slug;
				$this->wc   = $this->menu->wc; /** Let's re-use the menu wc object, as they have the same slug/hooks.  */
			}
		}

		/** Call parent's code. */
		parent::__construct( $this->slug );
	}

	/**
	 * Add tab to the submenu.
	 * It can either be a submenu of a core menu, or a submenu of a custom menu.
	 *
	 * @param Tab $tab The tab.
	 */
	public function add_tab( $tab ) {
		/** Init WC: required unless is the 1st submenu of a custom menu. */
		if ( ! $this->wc ) {
			$this->wc = new WC();
			$this->wc->set_id( $this->slug );
		}

		/** Render the parent menu if 1st submenu of a custom menu. */
		if ( ( $this->menu ) && ( 1 === $this->menu->count_submenu ) ) {
			$this->menu->attach_hooks();
		}

		/** Start tab's render/saving hooks. */
		$tab->wc_id        = $this->slug;
		$tab->wc           = $this->wc;
		$tab->menu_submenu = $this;
		$tab->attach_hooks();

		/** Parent's code. */
		parent::add_tab( $tab );

		/** Allow chain commands. */
		return $this;
	}

	/**
	 * Show submenu.
	 *
	 * @return void
	 */
	public function admin_menu_callback() {
		/** Core menu, call `add_*_page(...)`. */
		$function_name = "add_{$this->parent_slug}_page";
		$is_core_menu  = function_exists( $function_name );
		if ( $is_core_menu ) {
			$args                   = array(
				$this->page_title ? $this->page_title : $this->label,
				$this->label,
				$this->capability ? $this->capability : 'manage_options',
				$this->slug,
				array( $this->wc, 'output' ),
				$this->position,
			);
			$this->page_hook_suffix = call_user_func_array( $function_name, $args );
		} else {
			/** Custom menu, call `add_submenu_page(...)`. */
			$this->page_hook_suffix = add_submenu_page(
				$this->parent_slug,
				$this->page_title ? $this->page_title : $this->label,
				$this->label,
				$this->capability ? $this->capability : 'manage_options',
				$this->slug,
				array( $this->wc, 'output' ),
				$this->position
			);
		}

		/** Allow custom code to hook here. */
		do_action( "menu_item_added_{$this->slug}", $this->page_hook_suffix );
	}
}
