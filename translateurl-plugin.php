<?php 
/*
Plugin Name: Translate URL
Description: This plugin is specified for translating urls via polylang in using domain
Version: 1.0.0
Author: TritonIT
*/

function sidebar_plugin_register_scripts() {
    wp_register_script(
        'translate-js',
        plugins_url( 'translate.js', __FILE__ ),
        array( 'wp-plugins', 'wp-edit-post', 'wp-element' )
    );
}
add_action( 'init', 'sidebar_plugin_register_scripts' );

function sidebar_plugin_script_enqueue() {
    wp_enqueue_script( 'translate-js' );
    if (function_exists('pll_languages_list')) {
      $languages = pll_languages_list(array('fields' => array()));
      $URLToApi = plugins_url();
      wp_localize_script('translate-js', 'vars', array(
        'languages' => $languages,
        'URLToApi' => $URLToApi,
      )
      );
    }
    
}
add_action( 'enqueue_block_editor_assets', 'sidebar_plugin_script_enqueue' );

function sidebar_plugin_styles_enqueue() {
  $plugin_url = plugin_dir_url( __FILE__ );
  wp_enqueue_style( 'style',  $plugin_url . "/style.css");
}
add_action( 'enqueue_block_editor_assets', 'sidebar_plugin_styles_enqueue' );



?>