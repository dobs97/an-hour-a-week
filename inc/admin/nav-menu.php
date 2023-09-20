<?php
/**
 * Nav menu configuration hooks
 *
 * Applies to nav-menu admin page only, see customizer.php for the customizer
 * menu editor (because why would they be the same thing, that would be too easy)
 *
 * @package an_hour_a_week
 */

/* 
 * Registers a checkbox on the menu item configuration box to allow the menu item
 * to be displayed as a button 
 */
function menu_item_button( $item_id, $item ) {
  $is_checked = get_post_meta( $item_id, '_menu_item_button', true );
?>
  <p class="field-menu-item-button description description-wide">
      <label for="menu-item-button-<?php echo esc_attr($item_id); ?>">
          <input type="checkbox" id="menu-item-button-<?php echo esc_attr($item_id); ?>" class="widefat code menu-item-button-checkbox" name="menu-item-button[<?php echo esc_attr($item_id); ?>]" value="1" <?php checked($is_checked, 1); ?> />
          <?php _e('Display as button', 'text-domain'); ?>
      </label>
  </p>
<?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'menu_item_button', 10, 2 );

/*
 * Save 'Display as button' checkbox
 */
function save_menu_item_button($menu_id, $menu_item_db_id, $menu_item_args) {
    if (isset($_POST['menu-item-button'][$menu_item_db_id])) {
        update_post_meta($menu_item_db_id, '_menu_item_button', 1);
    } else {
        delete_post_meta($menu_item_db_id, '_menu_item_button');
    }
}
add_action('wp_update_nav_menu_item', 'save_menu_item_button', 10, 3);

/*
 * Register some front end code to dynamically show and hide the checkbox based on
 * whether the menu item is at the top level or not
 */
function enqueue_custom_script() {
  if (get_current_screen()->id === 'nav-menus')
  {
    wp_enqueue_script('an-hour-a-week-nav-menu-script', get_template_directory_uri() . '/js/admin/nav-menu.js', array('jquery'), '1.0', true);
  }
}

add_action('admin_enqueue_scripts', 'enqueue_custom_script');
