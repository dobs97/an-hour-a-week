jQuery(document).ready(function($) {
    /* Hide the checkbox for menu items with parents (not top level items) */
    function hideCheckboxForMenuItemsWithParents()
    {
        $('.menu-item-settings').each(function() {
            var $menuItem = $(this);
            
            /* Does the menu item have a parent? */
            if ($menuItem.find('.menu-item-data-parent-id').val() !== '0') {
                /* If so, hide the checkbox - we only show menu items as buttons
                   on the top level */
                $menuItem.find('.field-menu-item-button').hide();
            }
            else
            {
              /* No parent, so must be a top level. Show the checkbox. */
              $menuItem.find('.field-menu-item-button').show();
            }
        });
    }

    /* Initial check */
    hideCheckboxForMenuItemsWithParents();

    /* Listen for changes in menu item positions */
    $(document).on('mouseup', '.menu-item-handle, .menus-move', function()
    {
        /* Wait for the position to be updated before we check */
        setTimeout(hideCheckboxForMenuItemsWithParents, 1000);
    });
});
