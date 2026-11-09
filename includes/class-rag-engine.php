<?php
/**
 * Retrieval-augmented generation scaffold.
 *
 * @package WPRAGAssistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Coordinates query embedding, retrieval, and model completion. */
class WP_RAG_Assistant_Engine {
	/**
	 * Answer a user query using retrieved context.
	 *
	 * @param string $query User question.
	 * @return WP_Error|array Not implemented yet.
	 */
	public function answer( $query ) {
		return new WP_Error( 'not_implemented', __( 'The RAG engine is not implemented yet.', 'wp-rag-assistant' ) );
	}
}
