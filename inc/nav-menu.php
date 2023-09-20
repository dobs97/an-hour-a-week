<?php

function wp_nav_menu_objects($objects, $args)
{
  /* If any of the menu items have been configured to show as a button, update
   * their classes as such */
  foreach($objects as $key => $item) 
  {
    /* Can only show as a button at the top level of the menu */
    if($objects[$key]->post_parent == 0)
    {
      $menu_item_button = get_post_meta( $objects[$key]->ID, '_menu_item_button', true );

      if( $menu_item_button == 1 )
      {
        $objects[$key]->classes[] = 'menu-item-display-btn';
      }
    }
  }
  return $objects;
}

add_filter('wp_nav_menu_objects', 'wp_nav_menu_objects', 10, 2);
?>

