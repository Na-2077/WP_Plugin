<?php
/**
 * Retrieval-augmented generation engine.
 *
 * @package WPRAGAssistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Coordinates query embedding, retrieval, and model completion. */
class WP_RAG_Assistant_Engine {
	private $vector_db;
	private $settings;

	public function __construct( $vector_db, $settings ) {
		$this->vector_db = $vector_db;
		$this->settings = $settings;
	}

	public function register_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			'wp-rag-assistant/v1',
			'/chat',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'rest_answer' ),
				'permission_callback' => '__return_true',
				'args'                => array( 'query' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ) ),
			)
		);
	}

	public function rest_answer( $request ) {
		$query = trim( (string) $request->get_param( 'query' ) );
		if ( '' === $query || strlen( $query ) > 1000 ) {
			return new WP_Error( 'invalid_query', __( 'Please enter a question of 1,000 characters or fewer.', 'wp-rag-assistant' ), array( 'status' => 400 ) );
		}
		$result = $this->answer( $query );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}
	/**
	 * Answer a user query using retrieved context.
	 *
	 * @param string $query User question.
	 * @return WP_Error|array
	 */
	public function answer( $query ) {
		$key = $this->settings->get( 'api_key' );
		if ( ! $key ) {
			return new WP_Error( 'missing_api_key', __( 'The assistant is not configured yet.', 'wp-rag-assistant' ), array( 'status' => 503 ) );
		}
		$embedding = wp_remote_post(
			'https://api.openai.com/v1/embeddings',
			array(
				'timeout' => 30,
				'headers' => array( 'Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array( 'model' => $this->settings->get( 'embedding_model', 'text-embedding-3-small' ), 'input' => $query ) ),
			)
		);
		if ( is_wp_error( $embedding ) ) {
			return $embedding;
		}
		$embedding_data = json_decode( wp_remote_retrieve_body( $embedding ), true );
		$query_embedding = isset( $embedding_data['data'][0]['embedding'] ) ? $embedding_data['data'][0]['embedding'] : array();
		$matches = $this->vector_db->search( $query_embedding, 5 );
		$context = '';
		foreach ( $matches as $match ) {
			$context .= "\n\n[" . $match['title'] . "](" . $match['url'] . ")\n" . $match['content'];
		}
		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			array(
				'timeout' => 60,
				'headers' => array( 'Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'model'    => $this->settings->get( 'chat_model', 'gpt-4o-mini' ),
						'messages' => array(
							array( 'role' => 'system', 'content' => __( 'Answer using only the supplied website context. If the context does not contain the answer, say you do not know. Cite relevant source links in Markdown.', 'wp-rag-assistant' ) . "\nContext:" . $context ),
							array( 'role' => 'user', 'content' => $query ),
						),
					)
				),
			)
		);
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data['choices'][0]['message']['content'] ) ) {
			return new WP_Error( 'completion_failed', __( 'The language model returned an invalid response.', 'wp-rag-assistant' ) );
		}
		return array( 'answer' => wp_kses_post( $data['choices'][0]['message']['content'] ), 'sources' => $matches );
	}
}
