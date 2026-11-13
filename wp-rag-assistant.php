<?php
/**
 * Plugin Name: WP RAG Assistant
 * Description: A retrieval-augmented generation assistant for WordPress.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: wp-rag-assistant
 *
 * @package WPRAGAssistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_RAG_ASSISTANT_VERSION', '1.0.0' );
define( 'WP_RAG_ASSISTANT_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_RAG_ASSISTANT_URL', plugin_dir_url( __FILE__ ) );

require_once WP_RAG_ASSISTANT_DIR . 'includes/class-indexer.php';
require_once WP_RAG_ASSISTANT_DIR . 'includes/class-vector-db.php';
require_once WP_RAG_ASSISTANT_DIR . 'includes/class-rag-engine.php';
require_once WP_RAG_ASSISTANT_DIR . 'includes/class-admin-settings.php';
require_once WP_RAG_ASSISTANT_DIR . 'includes/class-licensing.php';

function wp_rag_assistant_init() {
	$settings = new WP_RAG_Assistant_Admin_Settings();
	$vector_db = new WP_RAG_Assistant_Vector_DB();
	$indexer = new WP_RAG_Assistant_Indexer( $vector_db, $settings );
	$engine = new WP_RAG_Assistant_Engine( $vector_db, $settings );

	$settings->register_hooks();
	$indexer->register_hooks();
	$engine->register_hooks();

	add_shortcode( 'wp_rag_assistant', 'wp_rag_assistant_render_shortcode' );
}
add_action( 'plugins_loaded', 'wp_rag_assistant_init' );

function wp_rag_assistant_activate() {
	WP_RAG_Assistant_Vector_DB::create_table();
}
register_activation_hook( __FILE__, 'wp_rag_assistant_activate' );

function wp_rag_assistant_render_shortcode() {
	wp_enqueue_style( 'wp-rag-assistant-chat', WP_RAG_ASSISTANT_URL . 'assets/css/chat-widget.css', array(), WP_RAG_ASSISTANT_VERSION );
	wp_enqueue_script( 'wp-rag-assistant-chat', WP_RAG_ASSISTANT_URL . 'assets/js/chat-widget.js', array(), WP_RAG_ASSISTANT_VERSION, true );
	wp_localize_script(
		'wp-rag-assistant-chat',
		'wpRagAssistant',
		array(
			'endpoint' => esc_url_raw( rest_url( 'wp-rag-assistant/v1/chat' ) ),
			'strings'  => array(
				'error' => __( 'Sorry, I could not answer that question.', 'wp-rag-assistant' ),
			),
		)
	);

	ob_start();
	require WP_RAG_ASSISTANT_DIR . 'views/frontend-chat-markup.php';
	return ob_get_clean();
}
