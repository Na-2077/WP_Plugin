<?php
/**
 * Pro license validation scaffold.
 *
 * @package WPRAGAssistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Handles optional Pro license validation. */
class WP_RAG_Assistant_Licensing {
	/**
	 * Check whether a license is valid.
	 *
	 * @return WP_Error|bool Not implemented yet.
	 */
	public function is_valid() {
		return new WP_Error( 'not_implemented', __( 'License validation is not implemented yet.', 'wp-rag-assistant' ) );
	}
}
