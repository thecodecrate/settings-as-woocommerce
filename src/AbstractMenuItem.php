<?php
/**
 * Class AbstractMenuItem.
 *
 * TODO:
 *   * [ ] To update versions on packagist: `git tag v0.0.1` then `git push origin v0.0.1`
 *
 */

/** Our namespace */
namespace SettingsAsWoocommerce;

/**
 * Class AbstractMenuItem.
 * This class is intented to be inherited by Menu and Submenu classes.
 * Both Menu and Submenu classes share most properties and methods.
 *
 * The settings page structure:
 *   * Menu > Submenu > Tab > Section (optional)
 *
 * Usage I - Adding a sub-menu to the "Users" menu:
 * ```
 *   $my_submenu = new Submenu( 'My Submenu', 'my_submenu', 'users' );
 *   $my_submenu
 *     ->add_tab( new MyTab1() )
 *     ->add_tab( new MyTab2() );
 * ```
 *
 * Usage II - Creating a custom menu with no sub-menus:
 * ```
 *   $my_menu = new Menu( 'My Menu', 'my_menu' );
 *   $my_menu
 *     ->add_tab( new MyTab1() )
 *     ->add_tab( new MyTab2() );
 * ```
 *
 * Usage III - Creating a custom menu with a sub-menu:
 * ```
 *   $my_menu    = new Menu( 'My Menu', 'my_menu' );
 *   $my_submenu = new Submenu( 'My Submenu', 'my_submenu', 'my_menu' );
 *   $my_submenu
 *     ->add_tab( new MyTab1() )
 *     ->add_tab( new MyTab2() );
 * ```
 *
 * Defining a tab:
 *   * Create a class extending `Tab`;
 *   * Define a constructor and set `id` and `label` properties;
 *   * At the end of the constructor, call the parent constructor;
 *   * Define methods: `get_fields` and `save`;
 *   * [OPTIONAL] Define methods `get_sections`, `get_assets`, `get_inline_css`, `get_inline_js`, `get_inline_css`, `get_inline_js`;
 * ```
 * class MyTab1 extends Tab {
 *   public function __construct() {
 *     $this->id    = 'my_tab1';
 *     $this->label = 'My Awesome Tab!';
 *
 *     parent::__construct();
 *   }
 *
 *   public function get_sections() {
 *     $sections = array(
 *       ''         => 'Section 1',
 *       'section2' => 'Section 2',
 *       'section3' => 'Section 3',
 *     );
 *
 *     return $sections;
 *   }
 *
 *   public function get_assets( $current_section = '' ) {
 *     if ( $current_section == 'section2' ) {
 *       return array( 'index.js', 'index.css' );
 *     }
 *     return array();
 *   }
 *
 *   public function get_inline_css( $current_section = '' ) {
 *     if ( $current_section == 'section2' ) {
 *       return 'body { background-color: green }';
 *     }
 *     return null;
 *   }
 *
 *   public function get_inline_js( $current_section = '' ) {
 *     if ( $current_section == 'section2' ) {
 *       return 'alert(1)';
 *     }
 *     return null;
 *   }
 *
 *   public function get_settings( $current_section = '' ) {
 *     $settings = array();
 *
 *     if ( '' === $current_section ) {
 *       $settings = array(
 *         array( 'type' => 'title', 'name' => 'My Section 1A' ),
 *         array( 'type' => 'checkbox', 'name' => 'My checkbox 1', 'id' => 'my-checkbox-1' ),
 *         array( 'type' => 'checkbox', 'name' => 'My checkbox 2', 'id' => 'my-checkbox-2' ),
 *         array( 'type' => 'sectionend', 'id' => 'my-section-1-end' ),
 *       );
 *     } else {
 *       $settings = array(
 *         array( 'type' => 'title', 'name' => 'My Section 1B' ),
 *         array( 'type' => 'checkbox', 'name' => 'My checkbox 1B', 'id' => 'my-checkbox-1b' ),
 *         array( 'type' => 'checkbox', 'name' => 'My checkbox 2B', 'id' => 'my-checkbox-2b' ),
 *         array( 'type' => 'sectionend', 'id' => 'my-section-1b-end' ),
 *       );
 *     }
 *
 *     return $settings;
 *   }
 *
 *   public function save() {
 *     $this->update_options( $this->get_settings() );
 *   }
 *
 * }
 * ```
 *
 * Actions:
 *   * `{menu}_load_page`: Triggered when the menu/submenu page is opened. Example: `my_submenu_load_page`;
 *   * `{menu}_load_tab_{tab}`: Triggered when the tab page is opened. Example: `my_submenu_load_tab_my_tab`;
 *
 * Menu/Submenu settings:
 *   * ->set_page_title( 'Title on browser tab' )
 *   * ->set_capability( 'list_users' )
 *   * ->set_position( 90 )
 *   * ->set_icon_url( '/assets/icon.png' )
 *   * ->force_show_tabs()
 *   * ->add_asset( 'index.js' )->add_asset( 'index.css' )
 *   * ->set_inline_css( 'body { background-color: red }' )
 *   * ->set_inline_js( 'alert(1)' )
 *
 * Tab methods:
 *   * ->get_current_section()
 *   * ->update_options( $options )
 *
 * Usage - Long version: You can use the WC code directly, without the Menu/Submenu/Tab classes.
 * ---
 * ```
 * $wc = new Woocommerce\WC( 'mymenu' );
 * add_action( 'admin_menu', 'admin_menu_callback' )
 * ...
 * add_action( 'mymenu_settings_tabs_array', 'add_settings_tab_callback' );
 * ```
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class AbstractMenuItem {

	/**
	 * [REQUIRED] Menu/submenu slug (needs to be globally unique).
	 *
	 * Example: "my_plugin_my_settings1".
	 *
	 * If your plugin has n submenus, you have to create n instances of this class, each one with its own
	 * unique slug.
	 *
	 * Valid characters are alphanumeric and underscore only. No whitespaces allowed.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * [REQUIRED] Menu/Submenu label.
	 * Set on the constructor or with `set_label()`.
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * [OPTIONAL] Capability - who can see/access this menu/submenu item.
	 * Set on the constructor or with `set_capability()`.
	 * See list on https://wordpress.org/support/article/roles-and-capabilities/
	 *
	 * @var string
	 */
	protected $capability;

	/**
	 * [OPTIONAL] The position in the menu order this item should appear.
	 * Set with `set_position()`.
	 *
	 * @var int
	 */
	protected $position;

	/**
	 * [OPTIONAL] Page title (displayed on the browser's tab).
	 * By default, the page title is the submenu title.
	 * Set with `set_page_title()`.
	 *
	 * @var string
	 */
	protected $page_title;

	/**
	 * An instance of WC class. Allow to run code from the WC project.
	 *
	 * @var WC
	 */
	public $wc;

	/**
	 * The resulting page's hook_suffix when creating the menu/submenu item.
	 *
	 * @var string
	 */
	public $page_hook_suffix;

	/**
	 * The menu/submenu creation happens on the first `add_tab`.
	 * We don't immediately create the menu/submenu on the constructor, because some properties can be defined later.
	 * Ex.: `(new Class())->set_title('Something')->set_capability('users')->add_tab(new Tab1())`.
	 *
	 * If `set_title()` is called after the menu/submenu has been created, it returns an "you are doing it wrong" warn.
	 *
	 * @var boolean
	 */
	protected $has_been_hooked = false;

	/**
	 * We keep track of all objects and their slugs, so we can reference them by their slugs instead of the object.
	 *
	 * @var array
	 */
	protected static $instances = [];

	/**
	 * Hide tabs (using CSS).
	 * Default behavior is to auto-hide if there's only one tab.
	 * We start with this set to true and once we get a second `add_tab` we set it to false.
	 * Set with `force_show_tabs()`.
	 *
	 * @var bool
	 */
	public $hide_tabs = true;

	/**
	 * Tabs count. Used on "auto-hide tabs" feature.
	 *
	 * @var int
	 */
	public $count_tabs = 0;

	/**
	 * Assets to be loaded when the menu/submenu is open.
	 * Set with `add_asset( $file )`.
	 *
	 * @var array
	 */
	public $assets = array();

	/**
	 * Custom inline CSS code.
	 *
	 * @var string
	 */
	public $inline_css;

	/**
	 * Custom inline JS code.
	 *
	 * @var string
	 */
	public $inline_js;

	/**
	 * Helper methods.
	 *
	 * @var Helpers.
	 */
	public $helpers;

	/**
	 * Constructor.
	 *
	 * @param string  $slug The menu/submenu slug.
	 * @param Helpers $helpers [OPTIONAL] Dependecy injection.
	 */
	public function __construct( $slug, $helpers = null ) {
		/** Track all menu/submenu items and their slugs. */
		$klass = static::class;
		if ( ! isset( static::$instances[ $klass ] ) ) {
			static::$instances[ $klass ] = array();
		}
		static::$instances[ $klass ][ $slug ] = $this;

		/** Set the slug. */
		$this->slug = $slug;

		/**
		 * Re-uses object to decrease resource consumption, but if none is defined, it creates a new one.
		 * There's no side-effects on creating a new object as all helper methods are stateless, pure-functions.
		 * The only problem is an unecessary use of memory/cpu resources.
		*/
		$this->helpers = $helpers ? $helpers : new Helpers();
	}

	/**
	 * Find an instance by its slug.
	 *
	 * @param string $slug The slug to search for.
	 * @param string $klass The class to search for.
	 */
	public function find_by_slug( $slug, $klass ) {
		$klass = join( '\\', array( __NAMESPACE__, $klass ) );
		if ( isset( static::$instances[ $klass ] ) && isset( static::$instances[ $klass ][ $slug ] ) ) {
			return static::$instances[ $klass ][ $slug ];
		}
		return null; /** Not found. */
	}

	/**
	 * Set the menu/submenu slug.
	 *
	 * @param string $slug The slug.
	 *
	 * @return AbstractMenuItem
	 */
	public function set_slug( $slug ) {
		$this->slug = $slug;

		return $this;
	}

	/**
	 * Get slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Set the menu/submenu label.
	 *
	 * @param string $label The menu/submenu label.
	 *
	 * @return AbstractMenuItem
	 */
	public function set_label( $label ) {
		$this->label = $label;

		return $this;
	}

	/**
	 * Set the position.
	 *
	 * @param string $position The position in the menu.
	 *
	 * @return AbstractMenuItem
	 */
	public function set_position( $position ) {
		$this->position = $position;

		return $this;
	}

	/**
	 * Set the capability.
	 *
	 * @param string $capability The capability.
	 *
	 * @return AbstractMenuItem
	 */
	public function set_capability( $capability ) {
		$this->capability = $capability;

		return $this;
	}

	/**
	 * Set the page title displayed on the browser's tab.
	 *
	 * @param string $page_title The page title.
	 *
	 * @return AbstractMenuItem
	 */
	public function set_page_title( $page_title ) {
		$this->page_title = $page_title;

		return $this;
	}

	/**
	 * Execute the submenu creation, by adding hooks.
	 *
	 * @return void
	 */
	public function attach_hooks() {
		/** Do not allow to re-enter not to set menu properties anymore. */
		if ( $this->has_been_hooked ) {
			// _doing_it_wrong( __METHOD__, "You can't call attach_hooks twice on the menu/submenu {$this->slug}.", '1.0.0' );
			return;
		}
		$this->has_been_hooked = true;

		/** Create a dynamic hook to execute code when the page is loaded. */
		add_action( "menu_item_added_{$this->slug}", array( $this, 'menu_added_callback' ) );

		/** Add menu item: Step 1/2 - Listen to the event. */
		add_action( 'admin_menu', array( $this, 'admin_menu_callback' ) );

		/** Handle saving settings earlier than load-{page} hook to avoid race conditions in conditional menus. */
		add_action( 'wp_loaded', array( $this, 'save_settings_callback' ) );

		/** Load assets only when the menu/submenu's admin page is loaded. */
		add_action( $this->slug . '_load_page', array( $this, 'load_assets_callback' ) );
	}

	/**
	 * Find the name of the dynamic hook "load-*".
	 * With this hook name, we add an action to be triggered once the page is rendered.
	 *
	 * @param string $page_hook The name of the dynamic hook "load-*".
	 */
	public function menu_added_callback( $page_hook ) {
		add_action( "load-{$page_hook}", array( $this, 'load_page_hook_callback' ) );
	}

	/**
	 * Called when our menu/submenu page is loaded.
	 * Useful for loading JS/CSS only for an specific admin page.
	 */
	public function load_page_hook_callback() {
		do_action( $this->slug . '_load_page' );
	}

	/**
	 * Add menu item: Step 2/2 - Call "add_xxxx_page()".
	 *
	 * @return void
	 */
	public function admin_menu_callback() {
	}

	/**
	 * A wrapper to WC's `save_settings()` callback.
	 *
	 * This callback is called after all plugins have been loaded (i.e. their constructors have been executed).
	 * It intercepts the request, checking if it is a "save" request.
	 * It also sets "current_tab" and "current_section".
	 */
	public function save_settings_callback() {
		$this->wc->save_settings();
	}

	/**
	 * Add tab to the menu/submenu.
	 *
	 * @param Tab $tab The tab with settings fields.
	 */
	public function add_tab( $tab ) {
		/** Start hooks to render this menu/submenu. */
		$this->attach_hooks();

		/** Tabs counter. */
		$this->count_tabs += 1;

		/** Auto-hide feature. */
		if ( $this->count_tabs >= 2 ) {
			$this->hide_tabs = false;
		}
	}

	/**
	 * Disable auto-hide.
	 *
	 * @return AbstractMenuItem
	 */
	public function force_show_tabs() {
		/**
		 * The CSS code to hide tabs is added on the "load-*" dynamic hook, which is executed only
		 * when the specific page is opened and not on all pages.
		 */
		$this->hide_tabs = false;

		return $this;
	}

	/**
	 * Add asset to be loaded on the page display (CSS or JS)
	 * Asset type is inferred by its file extension.
	 *
	 * @param string $file_path_relative The asset filename (relative path).
	 *
	 * @return AbstractMenuItem
	 */
	public function add_asset( $file_path_relative ) {
		$this->assets[] = $file_path_relative;

		return $this;
	}

	/**
	 * Add inline CSS code.
	 *
	 * @param string $inline_css The CSS code.
	 *
	 * @return AbstractMenuItem
	 */
	public function set_inline_css( $inline_css ) {
		/**
		 * We add the code in the "load-*" dynamic hook, which is executed only
		 * when the specific page is opened and not on all pages.
		 */
		$this->inline_css = $inline_css;

		return $this;
	}

	/**
	 * Add inline JS code.
	 *
	 * @param string $inline_js The JS code.
	 *
	 * @return AbstractMenuItem
	 */
	public function set_inline_js( $inline_js ) {
		/**
		 * We add the code in the "load-*" dynamic hook, which is executed only
		 * when the specific page is opened and not on all pages.
		 */
		$this->inline_js = $inline_js;

		return $this;
	}

	/**
	 * Called when the menu/submenu is opened.
	 */
	public function load_assets_callback() {
		/**
		 * We can't just call "wp_add_inline_style" to add an inline CSS code.
		 * Instead, we need to attach it to a registered WP's style.
		 * To bypass this limitation, we register a dummy, empty, style.
		*/
		$handle_css = null;
		if ( $this->hide_tabs || $this->inline_css ) {
			$handle_css = "{$this->slug}_dummy_handle_css";
			wp_register_style( $handle_css, false );
			wp_enqueue_style( $handle_css );
		}
		$handle_js = null;
		if ( $this->inline_js ) {
			$handle_js = "{$this->slug}_dummy_handle_js";
			wp_register_script( $handle_js, false );
			wp_enqueue_script( $handle_js );
		}

		/**
		 * `hide_tabs()`:
		 */
		if ( $this->hide_tabs ) {
			$css_show_h1  = ".{$this->slug} .nav-tab-wrapper+h1 { display: block; position: relative; clip-path: none; height: auto; overflow: inherit; top: inherit; width: auto; }";
			$css_hide_tab = ".{$this->slug} .nav-tab-wrapper { display: none; }";
			wp_add_inline_style( $handle_css, "$css_show_h1 $css_hide_tab" );
		}

		/**
		 * `add_asset( $file )`:
		 * Now that the menu/submenu is opened, we can load the custom JS/CSS.
		 */
		foreach ( $this->assets as $asset_path ) {
			$this->helpers->load_relative_asset( $asset_path, $this->slug );
		}

		/** Inline CSS/JS. */
		if ( $this->inline_css ) {
			wp_add_inline_style( $handle_css, $this->inline_css );
		}
		if ( $this->inline_js ) {
			wp_add_inline_script( $handle_js, $this->inline_js );
		}
	}

	/**
	 * A wrapper to WC's `woocommerce_admin_fields()`.
	 *
	 * It's just a shorthand: `$obj->method` instead of `$obj->wc->method`.
	 *
	 * @param array $output A vector with the page fields.
	 */
	public function woocommerce_admin_fields( $output ) {
		$this->wc->woocommerce_admin_fields( $output );
	}

}
