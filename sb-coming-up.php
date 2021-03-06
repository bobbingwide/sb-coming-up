<?php
/**
 * Plugin Name:     Coming up
 * Description:     Display future events
 * Version:         0.0.1
 * Author:          @bobbingwide
 * License:         GPL 3.0
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     sb-coming-up
 *
 * @package         sb-coming-up
 * @copyright       (C) Copyright Bobbing Wide 2021
 */

/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
function oik_sb_sb_coming_up_block_init() {
	$dir = __DIR__;

	$script_asset_path = "$dir/build/index.asset.php";
	if ( ! file_exists( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "oik-sb/sb-coming-up" block first.'
		);
	}
	$index_js     = 'build/index.js';
	$script_asset = require( $script_asset_path );
	wp_register_script(
		'oik-sb-sb-coming-up-block-editor',
		plugins_url( $index_js, __FILE__ ),
		$script_asset['dependencies'],
		$script_asset['version']
	);
	wp_set_script_translations( 'oik-sb-sb-coming-up-block-editor', 'sb-coming-up' );

	$editor_css = 'build/index.css';
	wp_register_style(
		'oik-sb-sb-coming-up-block-editor',
		plugins_url( $editor_css, __FILE__ ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	$style_css = 'build/style-index.css';
	wp_register_style(
		'oik-sb-sb-coming-up-block',
		plugins_url( $style_css, __FILE__ ),
		array(),
		filemtime( "$dir/$style_css" )
	);

	register_block_type(
		'oik-sb/sb-coming-up',
		array(
			'editor_script' => 'oik-sb-sb-coming-up-block-editor',
			'editor_style'  => 'oik-sb-sb-coming-up-block-editor',
			'style'         => 'oik-sb-sb-coming-up-block',
			'render_callback' => 'sb_coming_up_block_dynamic_block',
			'attributes' => [
				'postType' => ['type' => 'string'],
				'showDate' => [ 'type' => 'boolean'],
				'showTitle' => ['type' => 'boolean'],
				'showTitleAsLink' => ['type' => 'boolean'],
				'showExcerpt' => ['type' => 'boolean'],
				'className' => [ 'type' => 'string'],
			]
		)
	);
}


function sb_coming_up_block_dynamic_block( $attributes ) {
 	require_once __DIR__ . '/libs/class-sb-coming-up-block.php';
	$sb_coming_up_block = new SB_Coming_Up_Block();
	$html = $sb_coming_up_block->render( $attributes );
	return $html;

}

function sb_coming_up_loaded() {
	add_action( 'pre_get_posts', 'sb_coming_up_pre_get_posts' );
	add_action( 'init', 'oik_sb_sb_coming_up_block_init' );
}

/**
 * Implement "pre_get_posts" action
 *
 * Here we allow the main query to access the future posts as well as published.
 *
 * @TODO Check when this is not a good idea.
 *
 * Maybe it should only be done when the post_status is 'publish'
 * and the query is a get request.
 *
 * @param object $query Instance of WP_Query
 */
function sb_coming_up_pre_get_posts( $query ) {
	if ( $query->is_main_query() ) {
		$query->set( 'post_status', ['publish', 'future']);
	}
}

sb_coming_up_loaded();
