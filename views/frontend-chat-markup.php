<?php
/**
 * Frontend chat markup template.
 *
 * @package WPRAGAssistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="wp-rag-assistant" class="wp-rag-assistant" aria-live="polite">
	<div class="wp-rag-assistant__messages"></div>
	<form class="wp-rag-assistant__form">
		<label class="screen-reader-text" for="wp-rag-assistant-query"><?php esc_html_e( 'Ask a question', 'wp-rag-assistant' ); ?></label>
		<input id="wp-rag-assistant-query" class="wp-rag-assistant__input" type="text" maxlength="1000" required placeholder="<?php esc_attr_e( 'Ask a question…', 'wp-rag-assistant' ); ?>">
		<button type="submit"><?php esc_html_e( 'Ask', 'wp-rag-assistant' ); ?></button>
	</form>
</div>
