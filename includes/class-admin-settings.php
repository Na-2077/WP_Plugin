<?php
/**
 * WordPress admin settings.
 *
 * @package WPRAGAssistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Registers the plugin settings interface. */
class WP_RAG_Assistant_Admin_Settings {
	private $option_name = 'wp_rag_assistant_settings';

	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_menu() {
		add_options_page( __( 'WP RAG Assistant', 'wp-rag-assistant' ), __( 'WP RAG Assistant', 'wp-rag-assistant' ), 'manage_options', 'wp-rag-assistant', array( $this, 'render_page' ) );
	}

	public function register_settings() {
		register_setting( 'wp_rag_assistant', $this->option_name, array( $this, 'sanitize' ) );
		add_settings_section( 'wp_rag_assistant_main', __( 'Provider settings', 'wp-rag-assistant' ), '__return_false', 'wp-rag-assistant' );
		add_settings_field( 'api_key', __( 'OpenAI API key', 'wp-rag-assistant' ), array( $this, 'field' ), 'wp-rag-assistant', 'wp_rag_assistant_main', array( 'key' => 'api_key', 'type' => 'password', 'description' => __( 'Stored in WordPress and used for embeddings and chat.', 'wp-rag-assistant' ) ) );
		add_settings_field( 'chat_model', __( 'Chat model', 'wp-rag-assistant' ), array( $this, 'field' ), 'wp-rag-assistant', 'wp_rag_assistant_main', array( 'key' => 'chat_model', 'description' => 'Default: gpt-4o-mini' ) );
		add_settings_field( 'embedding_model', __( 'Embedding model', 'wp-rag-assistant' ), array( $this, 'field' ), 'wp-rag-assistant', 'wp_rag_assistant_main', array( 'key' => 'embedding_model', 'description' => 'Default: text-embedding-3-small' ) );
		add_settings_field( 'post_types', __( 'Indexed post types', 'wp-rag-assistant' ), array( $this, 'post_types_field' ), 'wp-rag-assistant', 'wp_rag_assistant_main' );
	}

	public function sanitize( $input ) {
		$current = $this->get_all();
		$output = array(
			'api_key'         => ! empty( $input['api_key'] ) ? sanitize_text_field( $input['api_key'] ) : $current['api_key'],
			'chat_model'      => sanitize_text_field( $input['chat_model'] ?? 'gpt-4o-mini' ),
			'embedding_model' => sanitize_text_field( $input['embedding_model'] ?? 'text-embedding-3-small' ),
			'post_types'      => array_map( 'sanitize_key', (array) ( $input['post_types'] ?? array( 'post', 'page' ) ) ),
		);
		return $output;
	}

	public function field( $args ) {
		$value = $this->get( $args['key'], '' );
		printf( '<input class="regular-text" type="%s" name="%s[%s]" value="%s" />', esc_attr( $args['type'] ?? 'text' ), esc_attr( $this->option_name ), esc_attr( $args['key'] ), esc_attr( $value ) );
		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	public function post_types_field() {
		$selected = (array) $this->get( 'post_types', array( 'post', 'page' ) );
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $post_type ) {
			printf( '<label><input type="checkbox" name="%s[post_types][]" value="%s" %s /> %s</label><br>', esc_attr( $this->option_name ), esc_attr( $post_type->name ), checked( in_array( $post_type->name, $selected, true ), true, false ), esc_html( $post_type->labels->name ) );
		}
	}

	public function render_page() {
		if ( isset( $_POST['wp_rag_reindex'] ) && check_admin_referer( 'wp_rag_reindex' ) ) {
			$indexer = new WP_RAG_Assistant_Indexer( new WP_RAG_Assistant_Vector_DB(), $this );
			$count = $indexer->reindex_all();
			add_settings_error( 'wp_rag_assistant', 'reindexed', sprintf( __( '%d posts reindexed.', 'wp-rag-assistant' ), $count ), 'updated' );
		}
		require WP_RAG_ASSISTANT_DIR . 'views/admin-settings-page.php';
	}

	public function get( $key, $default = null ) {
		$settings = $this->get_all();
		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
	}

	private function get_all() {
		return wp_parse_args( (array) get_option( $this->option_name, array() ), array( 'api_key' => '', 'chat_model' => 'gpt-4o-mini', 'embedding_model' => 'text-embedding-3-small', 'post_types' => array( 'post', 'page' ) ) );
	}
}
