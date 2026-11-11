<?php
/**
 * Local vector-store integration.
 *
 * @package WPRAGAssistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Stores embeddings in a WordPress table and performs cosine-similarity search. */
class WP_RAG_Assistant_Vector_DB {
	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'wp_rag_assistant_vectors';
	}

	public static function create_table() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		$sql = 'CREATE TABLE ' . self::table_name() . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			post_id bigint(20) unsigned NOT NULL,
			chunk_index int(10) unsigned NOT NULL,
			title text NOT NULL,
			url text NOT NULL,
			content longtext NOT NULL,
			embedding longtext NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY post_chunk (post_id, chunk_index),
			KEY post_id (post_id)
		) $charset;";
		dbDelta( $sql );
	}

	public function replace_post( $post_id, $chunks ) {
		global $wpdb;
		$table = self::table_name();
		$wpdb->delete( $table, array( 'post_id' => $post_id ), array( '%d' ) );
		foreach ( $chunks as $index => $chunk ) {
			$wpdb->insert(
				$table,
				array(
					'post_id'     => $post_id,
					'chunk_index' => $index,
					'title'       => $chunk['title'],
					'url'         => $chunk['url'],
					'content'     => $chunk['content'],
					'embedding'   => wp_json_encode( $chunk['embedding'] ),
					'created_at'  => current_time( 'mysql', true ),
				),
				array( '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
			);
		}
		return true;
	}

	public function delete_post( $post_id ) {
		global $wpdb;
		return false !== $wpdb->delete( self::table_name(), array( 'post_id' => $post_id ), array( '%d' ) );
	}

	public function search( $embedding, $limit = 5 ) {
		global $wpdb;
		$rows = $wpdb->get_results( 'SELECT title, url, content, embedding FROM ' . self::table_name(), ARRAY_A );
		$matches = array();
		foreach ( $rows as $row ) {
			$vector = json_decode( $row['embedding'], true );
			$score = self::cosine_similarity( $embedding, $vector );
			if ( $score > 0 ) {
				$row['score'] = $score;
				unset( $row['embedding'] );
				$matches[] = $row;
			}
		}
		usort( $matches, function ( $a, $b ) {
			return $a['score'] === $b['score'] ? 0 : ( $a['score'] < $b['score'] ? 1 : -1 );
		} );
		return array_slice( $matches, 0, absint( $limit ) );
	}

	private static function cosine_similarity( $left, $right ) {
		if ( ! is_array( $left ) || ! is_array( $right ) || count( $left ) !== count( $right ) ) {
			return 0;
		}
		$dot = $left_norm = $right_norm = 0;
		foreach ( $left as $index => $value ) {
			$other = (float) $right[ $index ];
			$dot += (float) $value * $other;
			$left_norm += (float) $value * (float) $value;
			$right_norm += $other * $other;
		}
		return $left_norm && $right_norm ? $dot / ( sqrt( $left_norm ) * sqrt( $right_norm ) ) : 0;
	}
	/**
	 * Store or retrieve vector data.
	 *
	 * @param array $payload Provider-specific request data.
	 * @return WP_Error|array
	 */
	public function request( $payload ) {
		return new WP_Error( 'unsupported_request', __( 'Use the vector store methods instead.', 'wp-rag-assistant' ) );
	}
}
