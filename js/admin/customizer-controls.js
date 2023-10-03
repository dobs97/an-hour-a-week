/* global wp, jQuery */
/**
 * File customizer-controls.js.
 *
 * Extending theme customizer controls
 *
 * Contains handlers for theme specific extended controls in the customizer
 */

( function( $ ) {

  /* Extend nav menu control */
  wp.customize.control.bind( 'add', ( control ) => {
    if ( control.extended( wp.customize.Menus.MenuItemControl ) ) {
      control.deferred.embedded.done( () => {
        extendNavMenuControl( control );
      } );
    }
  } );

  /**
   * Extend the setting with our checkbox state
   *
   * @param {wp.customize.Setting} setting
   * @param {boolean} value to set
   */
  function setNavMenuSetting( setting, value )
  {
    setting.set(
      Object.assign(
        {},
        _.clone( setting() ),
        { checked: value }
      )
    );
  }

  /**
   *
   * Extend the nav menu control with our 'show as button' checkbox handling
   * @param {wp.customize.Menus.MenuItemControl} control
   */
  function extendNavMenuControl( control )
  {
    control.buttonCheckbox = control.container.find( '#menu-item-button' );

    /* Set the initial UI state. */
    updateNavMenuControlFields( control );
    hideCheckboxForMenuItemsWithParents();

    /* Update the UI state when the setting changes programmatically */
    control.setting.bind( () => {
      updateNavMenuControlFields( control );
    } );

    /* Update the setting when the checkbox is modified */
    control.buttonCheckbox.on( 'click', function () {
      setNavMenuSetting( control.setting, this.checked );
    } );

    $(document).on('mouseup', '.menu-item-handle, .menus-move', function()
    {
      setTimeout(hideCheckboxForMenuItemsWithParents, 1000);
    });
  }

  /*
   * Hide 'Display as button' checkbox on any menu items
   * that aren't at the top level
   */
  function hideCheckboxForMenuItemsWithParents()
  {
    $('.customize-control-nav_menu_item').each(function() {
      var menuItem = $(this);
      var checkBox = menuItem.find("#menu-item-button");

      if(!menuItem.hasClass('menu-item-depth-0'))
      {
        /* Hide the whole label object */
        checkBox.parent().hide();
      }
      else
      {
        checkBox.parent().show();
      }
    });
  }

  /**
   * Apply the setting value to the checkbox
   *
   * @param {wp.customize.Menus.MenuItemControl} control
   */
  function updateNavMenuControlFields( control )
  {
    const checked = control.setting().checked;
    const checkbox = control.buttonCheckbox;

    checkbox.prop("checked",(checked == true));
  }

}( jQuery ) );
