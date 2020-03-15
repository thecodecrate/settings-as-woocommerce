# Settings as Woocommerce
Create settings pages for your WordPress plugin on WooCommerce style.

This library is a wrapper to actual WooCommerce code. It is not my code, but WooCommerce code, packed in a way that you can use to build your own plugins.

Right now this library is manually synced with WooCommerce code from time to time. But in the future it will be automatically updated, whenever WooCommerce pushes a new version to their repo.

## Settings page structure
Settings pages are structured on WordPress as: `Menu` > `Submenu` > `Tabs` > `Sections [optional]`.

The most basic element on this library is the **Tab**.  Each settings page here is a tab.

### Sections or Field Sections?
* `Section`: The menu below tabs;
* `Field Section`: A group of fields on each page;


## Install
```bash
composer require loureirorg/settings-as-woocommerce
```

## Usage I - Adding sub-menu to the "Users" menu
```php
$label = 'My Submenu';
$slug  = 'my_submenu';
$menu  = 'users';

$my_submenu = new Submenu( $label, $slug, $menu );
$my_submenu
  ->add_tab( new MyTab1() )
  ->add_tab( new MyTab2() );
```
## Usage II - Creating a custom menu with no sub-menus
```php
$my_menu = new Menu( 'My Menu', 'my_menu' );
$my_menu
  ->add_tab( new MyTab1() )
  ->add_tab( new MyTab2() );
```
## Usage III - Creating a custom menu with a sub-menu
```php
$my_menu    = new Menu( 'My Menu', 'my_menu' );
$my_submenu = new Submenu( 'My Submenu', 'my_submenu', 'my_menu' );
$my_submenu
  ->add_tab( new MyTab1() )
  ->add_tab( new MyTab2() );
```

## Usage - The Tab definition
  * Create a class extending `Tab`;
  * Define a constructor and set `id` and `label` properties;
  * At the end of the constructor, call the parent constructor;
  * Define methods: `get_fields` and `save`;
  * [OPTIONAL] Define methods `get_sections`, `get_assets`, `get_inline_css`, `get_inline_js`, `get_inline_css`, `get_inline_js`;

**Simple Example:**
```php
class MyTab1 extends Tab {

  public function __construct() {
    $this->id    = 'my_tab1';
    $this->label = 'My First Tab!';
    parent::__construct();
  }

  public function get_settings() {
    $settings = array(
      array( 'type' => 'title', 'name' => 'My Field Group' ),
      array( 'type' => 'checkbox', 'name' => 'My checkbox 1', 'id' => 'my-checkbox-1' ),
      array( 'type' => 'checkbox', 'name' => 'My checkbox 2', 'id' => 'my-checkbox-2' ),
      array( 'type' => 'sectionend', 'id' => 'my-field-group-1-end' ),
    );
    return $settings;
  }

  public function save() {
    $this->update_options( $this->get_settings() );
  }

}
```


**Detailed Example:**
```php
class MyTab2 extends Tab {

  public function __construct() {
    $this->id    = 'my_tab2';
    $this->label = 'My Second Tab!';
    parent::__construct();
  }

  public function get_sections() {
    $sections = array(
      ''         => 'Section 1',
      'section2' => 'Section 2',
      'section3' => 'Section 3',
    );
    return $sections;
  }

  public function get_assets( $current_section = '' ) {
    if ( $current_section == 'section2' ) {
      return array( 'index.js', 'index.css' );
    }
    return array();
  }

  public function get_inline_css( $current_section = '' ) {
    if ( $current_section == 'section2' ) {
      return 'body { background-color: green }';
    }
    return null;
  }

  public function get_inline_js( $current_section = '' ) {
    if ( $current_section == 'section2' ) {
      return 'alert(1)';
    }
    return null;
  }

  public function get_settings( $current_section = '' ) {
    $settings = array();
    if ( '' === $current_section ) {
      $settings = array(
        array( 'type' => 'title', 'name' => 'My Section 1A' ),
        array( 'type' => 'checkbox', 'name' => 'My checkbox 1A', 'id' => 'my-checkbox-1a' ),
        array( 'type' => 'checkbox', 'name' => 'My checkbox 2A', 'id' => 'my-checkbox-2a' ),
        array( 'type' => 'sectionend', 'id' => 'my-section-1-end' ),
      );
    } else {
      $settings = array(
        array( 'type' => 'title', 'name' => 'My Section 1B' ),
        array( 'type' => 'checkbox', 'name' => 'My checkbox 1B', 'id' => 'my-checkbox-1b' ),
        array( 'type' => 'checkbox', 'name' => 'My checkbox 2B', 'id' => 'my-checkbox-2b' ),
        array( 'type' => 'sectionend', 'id' => 'my-section-1b-end' ),
      );
    }
    return $settings;
  }

  public function save() {
    $this->update_options( $this->get_settings() );
  }

}
```

## Using WooCommerce Actions and Filters
You can use any standard WC filter/action. Just replace the `woocommerce` prefix with your **submenu slug**.

**Example:**
```php
/** `woocommerce_settings_tabs_array` becomes `mymenu_settings_tabs_array`. */
add_action( 'mymenu_settings_tabs_array', 'add_settings_tab_callback' );
```

## Optional Library Actions
  * `{menu}_load_page`: Triggered when the menu/submenu page is opened. Example: `my_submenu_load_page`;
  * `{menu}_load_tab_{tab}`: Triggered when the tab page is opened. Example: `my_submenu_load_tab_my_tab`;

## Optional Menu/Submenu Settings
  * `->set_page_title( 'Title on browser tab' )`
  * `->set_capability( 'list_users' )`
  * `->set_position( 90 )`
  * `->set_icon_url( '/assets/icon.png' )`
  * `->force_show_tabs()`
  * `->add_asset( 'index.js' )->add_asset( 'index.css' )`
  * `->set_inline_css( 'body { background-color: red }' )`
  * `->set_inline_js( 'alert(1)' )`

## Optional Tab helpers
  * `->get_current_section()`
  * `->update_options( $options )`

## Usage - Long version: You can use the WC code directly, without the Menu/Submenu/Tab classes.
```php
$wc = new Woocommerce\WC( 'mymenu' );
add_action( 'admin_menu', 'admin_menu_callback' )
...
add_action( 'mymenu_settings_tabs_array', 'add_settings_tab_callback' );
```

# Where to put all this code?
The **menu** definition can be put in your plugin constructor, on the same place where you would place a `add_action( 'admin_menu', 'admin_menu_callback' )`.

Each **tab** definition is a separated class definition. It is recommended to put each one on a separated file.

# To-do
- [ ] Create a script to auto-update the WooCommerce code:
- [ ] Unit Tests for classes `Tab`, `Menu`, `Submenu`, `Helpers`;
- [ ] Unit Tests for the auto-update code;
- [ ] Automate tests with GitHub Actions;
- [ ] Improve README file:
  * Show badges - tests, phpmd, phpcs, version;
  * Show 2 versions on it: (1) The main version: WC version (used on packagist); (2) Our wrapper code;
  * Full functional example on "Usage - Long Version":  `new WC()` + default WP code to create menus + default WC code to create a settings page;
- [ ] GitHub Actions - Auto-Update code whenever WooCommerce repo changes:
  * On each new WC version, we trigger the auto-update + run tests + deploy new version (if tests pass) + push to Packagist with new version;
  * On each new version of this repo code, we should run tests before accept the PR (Pull Request);
- [ ] Integration Tests:
  * Download WP (just like the unit tests for WP);
  * Auto-update WC code;
  * Auto-install a plugin that shows a settings page;
  * Matrix:
	* Try all PHP versions supported by WP (5.3 .. 7.*);
	* Try the most updated WP version, as well as old ones;
	* To test the auto-update resilience, we should try all WC code versions, since its 1.0;
- [ ] Functional Tests:
  * Make sure menu/submenu/tab/section/fields are visible the way they should be;
  * Use Pupeteer to check if settings pages are rendering properly? Selenium?;

# Bugs and Improvements
The goal of this project is to be a simple and **dumb wrapper** to the WooCommerce libraries. While we have some helpers like `set_inline_css()`, I am trying to make this code as little invasive as possible to the WooCommerce code in order to automate the code updates.

This means that if you find any issue that can be reproduced on WooCommerce, you should ask them to fix it. If the issue is a WooCommerce issue, it won't be fixed here. For example, let's say you found that some field is being printed without proper sanitization: ask WooCommerce team to fix it.

Also, to avoid this code becoming more than a simple wrapper, I will reject most improvements and PR's to it. Rule of thumb is that **improvements and bugs should be asked to the WooCommerce team**.

If you are not sure if your bug/improvement fits this project, if you are not sure you should report here or to the WooCommerce team,  feel free to open an issue asking me about it. I can't guarantee it will be fixed and/or implemented (mostly not), but I will always be happy to take a look at it :)
