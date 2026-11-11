<?php
/**
 * Content indexing.
 *
 * @package WPRAGAssistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Handles preparing WordPress content for indexing. */
class WP_RAG_Assistant_Indexer {
	private $vector_db;
	private $settings;

	public function __construct( $vector_db, $settings ) {
		$this->vector_db = $vector_db;
		$this->settings = $settings;
	}

	public function register_hooks() {
		add_action( 'save_post', array( $this, 'index_post' ), 20, 3 );
		add_action( 'before_delete_post', array( $this, 'delete_post' ) );
	}

	public function index_post( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || 'publish' !== $post->post_status || ! $this->is_enabled_post_type( $post->post_type ) ) {
			return;
		}
		$this->index( $post );
	}

	public function delete_post( $post_id ) {
		$this->vector_db->delete_post( $post_id );
	}

	public function reindex_all() {
		$post_types = $this->settings->get( 'post_types', array( 'post', 'page' ) );
		$posts = get_posts(
			array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			)
		);
		$count = 0;
		foreach ( $posts as $post ) {
			if ( ! is_wp_error( $this->index( $post ) ) ) {
				$count++;
			}
		}
		return $count;
	}

	private function index( $post ) {
		$content = trim( wp_strip_all_tags( apply_filters( 'the_content', $post->post_content ) ) );
		if ( '' === $content ) {
			return $this->vector_db->delete_post( $post->ID );
		}
		$chunks = array();
		$parts = preg_split( '/\s+/', $content );
		$size = 300;
		foreach ( array_chunk( $parts, $size ) as $part ) {
			$text = trim( implode( ' ', $part ) );
			$embedding = $this->embed( $text );
			if ( is_wp_error( $embedding ) ) {
				return $embedding;
			}
			$chunks[] = array(
				'title'     => get_the_title( $post ),
				'url'       => get_permalink( $post ),
				'content'   => $text,
				'embedding' => $embedding,
			);
		}
		return $this->vector_db->replace_post( $post->ID, $chunks );
	}

	private function embed( $text ) {
		$key = $this->settings->get( 'api_key' );
		if ( ! $key ) {
			return new WP_Error( 'missing_api_key', __( 'Configure an API key before indexing content.', 'wp-rag-assistant' ) );
		}
		$response = wp_remote_post(
			'https://api.openai.com/v1/embeddings',
			array(
				'timeout' => 30,
				'headers' => array( 'Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array( 'model' => $this->settings->get( 'embedding_model', 'text-embedding-3-small' ), 'input' => $text ) ),
			)
		);
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return ! empty( $data['data'][0]['embedding'] ) ? $data['data'][0]['embedding'] : new WP_Error( 'embedding_failed', __( 'The embedding provider returned an invalid response.', 'wp-rag-assistant' ) );
	}

	private function is_enabled_post_type( $post_type ) {
		return in_array( $post_type, (array) $this->settings->get( 'post_types', array( 'post', 'page' ) ), true );
	}
}
