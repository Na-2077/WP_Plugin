<?php
/**
 * Vector-store integration scaffold.
 *
 * @package WPRAGAssistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Provides a future abstraction for vector-store providers. */
class WP_RAG_Assistant_Vector_DB {
	/**
	 * Store or retrieve vector data.
	 *
	 * @param array $payload Provider-specific request data.
	 * @return WP_Error|array Not implemented yet.
	 */
	public function request( $payload ) {
		return new WP_Error( 'not_implemented', __( 'Vector-store integration is not implemented yet.', 'wp-rag-assistant' ) );
	}
}
