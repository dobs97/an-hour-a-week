<?php
/**
 * an hour a week Theme Customizer
 *
 * @package an_hour_a_week
 */

/*
 * Sanitizer for checkbox inputs
 */
function sanitize_checkbox($input)
{
  return (isset($input) && true === $input) ? true : false;
}

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function an_hour_a_week_customize_register( $wp_customize )
{
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'an_hour_a_week_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'an_hour_a_week_customize_partial_blogdescription',
			)
		);
  }

  /* There has to be a better way to pass this data about, but I can't see anything
   * on the WP_Customize_Manager object that gives information about the menu items
   */
  if ( $wp_customize->settings_previewed() ) 
  {
    $post_data = $wp_customize->unsanitized_post_values();

    if( $post_data != null )
    {
      foreach( $post_data as $item => $data )
      {
        /* Extract the id from 'nav_menu_item[id]' - ewwwww */
        if( preg_match( '/nav_menu_item\[(\d+)\]/', $item, $matches ) )
        {
          preview_nav_menu_setting_postmeta( $matches[1], $data );
        }
      }
    }
  }
}
add_action( 'customize_register', 'an_hour_a_week_customize_register' );

/**
* Add theme specific options to the customizer
* @param WP_Customize_Manager $wp_customize Theme Customizer object
 */
function an_hour_a_week_add_customizer_options( $wp_customize )
{
    $wp_customize->add_setting('display_header_fleur_de_lis', array(
        'default' => true, // Set the default value
        'sanitize_callback' => 'sanitize_checkbox',
    ));

    // Add a control for the checkbox
    $wp_customize->add_control('display_header_fleur_de_lis', array(
        'label' => 'Display Scouts Fleur De Lis after site title',
        'section' => 'title_tagline',
        'type' => 'checkbox',
    ));

}
add_action( 'customize_register', 'an_hour_a_week_add_customizer_options' );

/*
 * The below functions deal with custom options added to the nav menu items in
 * the customizer. It's based on a stackoverflow answer which seems to be the only
 * documentation on how to do this... very complicated just to add a checkbox.
 *
 * https://wordpress.stackexchange.com/questions/372493/add-settings-to-menu-items-in-the-customizer
 * https://gist.github.com/westonruter/7f2b9c18113f0576a72e0aca3ce3dbcb
 */

/**
 * Add custom fields to nav menu items
 */
function an_hour_a_week_add_nav_menu_options()
{
  ?>
    <# console.log(data); #>
    <# console.log(wp.customize.settings); #>
    <label for="menu-item-button">
    <span class="menu-item-button-title"><?php _e('Display as button', 'text-domain'); ?></span>
        <input type="checkbox" id="menu-item-button" class="menu-item-button-checkbox" name="menu-item-button[]" value="1" />
    </label>
  <?php
}
add_action('wp_nav_menu_item_custom_fields_customize_template', 'an_hour_a_week_add_nav_menu_options', 10, 3);

/**
* Adds value of custom field to nav menu item object
*/
function setup_nav_menu_item( $menu_item )
{
  if( is_object( $menu_item ) && isset( $menu_item->ID ) )
  {
    $menu_item->checked = get_post_meta( $menu_item->ID, '_menu_item_button', true );
  }

  return $menu_item;
}
add_filter( 'wp_setup_nav_menu_item', 'setup_nav_menu_item' );

/*
 * Gets and sanitizes the data from the customizer for the menu item config
 */
function get_sanitized_nav_menu_post_data( WP_Customize_Nav_Menu_Item_Setting $setting )
{
  $post_values = $setting->manager->unsanitized_post_values();

  $unsanitized_post_value = $setting->manager->unsanitized_post_values()[ $setting->id ][ "checked" ];

  if( isset($unsanitized_post_value) )
  {
    return sanitize_checkbox($unsanitized_post_value);
  }

  return null;
}

/**
 * Preview changes to the custom config against the menu items
 *
 * Note the unimplemented to-do in the doc block for the setting's preview method.
 *
 * There has to be a better way of accessing the data than this, but I can't see
 * anything broadly related to the nav menu item in the settings array of the
 * customizer object
 *
 * @see WP_Customize_Nav_Menu_Item_Setting::preview()
 *
 * @param array data raw unsanitized post data for a nav menu item
 */
function preview_nav_menu_setting_postmeta( $id, array $data )
{
  if( !is_numeric( $id ) )
  {
    return;
  }

  if( !isset($data["checked"]) )
  {
    return;
  }

  $setting_val = sanitize_checkbox( $data["checked"] );

  if ( null === $setting_val )
  {
		return;
  }

	add_filter(
		'get_post_metadata',
    static function ( $value, $object_id, $meta_key ) use ( $setting_val, $id ) 
    {
      if ( $object_id == $id && '_menu_item_button' === $meta_key )
      {
				return $setting_val;
			}
			return $value;
		},
		10,
		3
  );

}

/*
 * Handles saving the custom config against the menu items
 */
function save_nav_menu_setting_postmeta( WP_Customize_Nav_Menu_Item_Setting $setting )
{
  $value = get_sanitized_nav_menu_post_data( $setting );

  if( $value !== null )
  {
    update_post_meta($setting->post_id, '_menu_item_button', $value);
  }
}

/*
 * Hook run after the customizer is saved
 *
 * @param WP_Customize_Manager wp_customize Instance of WP_Customize_Manager
 */
function an_hour_a_week_customize_save_after( WP_Customize_Manager $wp_customize )
{
  foreach ( $wp_customize->settings() as $setting ) 
  {
    if ( $setting instanceof WP_Customize_Nav_Menu_Item_Setting &&
      $setting->check_capabilities() )
    {
      save_nav_menu_setting_postmeta( $setting );
    }
  }
}

add_action( 'customize_save_after', 'an_hour_a_week_customize_save_after' );

/*
 * Enqueue customizer controls customization
 * ...customizing the customizer :)
 */
function enqueue_customize_controls_script()
{
  wp_enqueue_script( 'an-hour-a-week-customizer-controls',
    get_template_directory_uri() . '/js/admin/customizer-controls.js',
    array( 'customize-nav-menus' ), _S_VERSION, true );
}

add_action( 'customize_controls_enqueue_scripts', 'enqueue_customize_controls_script');

/* End of chaotic nav menu item code - phew */

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function an_hour_a_week_customize_partial_blogname()
{
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function an_hour_a_week_customize_partial_blogdescription()
{
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function an_hour_a_week_customize_preview_js()
{
	wp_enqueue_script( 'an-hour-a-week-customizer', get_template_directory_uri() . '/js/admin/customizer.js', array( 'customize-preview' ), _S_VERSION, true );
}

add_action( 'customize_preview_init', 'an_hour_a_week_customize_preview_js' );
