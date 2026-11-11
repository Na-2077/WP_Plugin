<?php
/**
 * Plugin Name: WP RAG Assistant
 * Description: A starter scaffold for a retrieval-augmented generation assistant in WordPress.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: wp-rag-assistant
 *
 * @package WPRAGAssistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_RAG_ASSISTANT_VERSION', '0.1.0' );
define( 'WP_RAG_ASSISTANT_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_RAG_ASSISTANT_URL', plugin_dir_url( __FILE__ ) );

require_once WP_RAG_ASSISTANT_DIR . 'includes/class-indexer.php';
require_once WP_RAG_ASSISTANT_DIR . 'includes/class-vector-db.php';
require_once WP_RAG_ASSISTANT_DIR . 'includes/class-rag-engine.php';
require_once WP_RAG_ASSISTANT_DIR . 'includes/class-admin-settings.php';
require_once WP_RAG_ASSISTANT_DIR . 'includes/class-licensing.php';

/**
 * Initialize the plugin scaffold.
 *
 * Feature implementations and hook registration will be added in follow-up work.
 *
 * @return void
 */
function wp_rag_assistant_init() {
	// TODO: Instantiate plugin components and register WordPress hooks.
}
add_action( 'plugins_loaded', 'wp_rag_assistant_init' );
