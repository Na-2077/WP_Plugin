<?php
/**
 * Admin settings page template.
 *
 * @package WPRAGAssistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'WP RAG Assistant', 'wp-rag-assistant' ); ?></h1>
	<?php settings_errors( 'wp_rag_assistant' ); ?>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'wp_rag_assistant' );
		do_settings_sections( 'wp-rag-assistant' );
		submit_button();
		?>
	</form>
	<hr>
	<h2><?php esc_html_e( 'Index content', 'wp-rag-assistant' ); ?></h2>
	<p><?php esc_html_e( 'Run this after changing indexed content types or provider settings.', 'wp-rag-assistant' ); ?></p>
	<form method="post">
		<?php wp_nonce_field( 'wp_rag_reindex' ); ?>
		<input type="hidden" name="wp_rag_reindex" value="1">
		<?php submit_button( __( 'Reindex all published content', 'wp-rag-assistant' ), 'secondary', 'submit', false ); ?>
	</form>
</div>
