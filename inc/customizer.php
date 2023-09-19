<?php
/**
 * an hour a week Theme Customizer
 *
 * @package an_hour_a_week
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function an_hour_a_week_customize_register( $wp_customize ) {
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
}
add_action( 'customize_register', 'an_hour_a_week_customize_register' );

/**
* Add theme specific options to the customizer
* @param WP_Customize_Manager $wp_customize Theme Customizer object
 */
function an_hour_a_week_add_customizer_options( $wp_customize ) {

    function sanitize_checkbox($input) {
      return (isset($input) && true === $input) ? true : false;
    }

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

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function an_hour_a_week_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function an_hour_a_week_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function an_hour_a_week_customize_preview_js() {
	wp_enqueue_script( 'an-hour-a-week-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), _S_VERSION, true );
}
add_action( 'customize_preview_init', 'an_hour_a_week_customize_preview_js' );
