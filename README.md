# WP RAG Assistant

A WordPress plugin scaffold for building a retrieval-augmented generation (RAG) assistant. The initial structure separates indexing, vector-store access, retrieval and generation, admin settings, licensing, frontend assets, and view templates.

> **Status:** This repository currently contains a starter scaffold. The indexing, provider integrations, chat interactions, and license validation are not implemented yet.

## Project structure

```text
wp-rag-assistant/
├── wp-rag-assistant.php
├── includes/
│   ├── class-indexer.php
│   ├── class-vector-db.php
│   ├── class-rag-engine.php
│   ├── class-admin-settings.php
│   └── class-licensing.php
├── assets/
│   ├── js/
│   │   ├── admin.js
│   │   └── chat-widget.js
│   └── css/
│       └── chat-widget.css
└── views/
    ├── admin-settings-page.php
    └── frontend-chat-markup.php
```

The repository root is the plugin directory; the `wp-rag-assistant/` label above describes the plugin folder name when installed in WordPress.

## Installation

1. Copy or clone this repository into `wp-content/plugins/wp-rag-assistant/`.
2. In WordPress, open **Plugins** and activate **WP RAG Assistant**.
3. Implement and configure the provider integrations before using this as a production assistant.

## Planned responsibilities

- **Indexer:** Listen for post updates and deletions, prepare content chunks, and synchronize them with a vector store.
- **Vector DB:** Provide a provider abstraction for a vector database such as Pinecone, Qdrant, or a supported OpenAI vector-store API.
- **RAG engine:** Embed user queries, retrieve relevant content, and pass grounded context to a language model.
- **Admin settings:** Configure credentials, indexed post types, model/provider choices, and manual re-indexing.
- **Licensing:** Validate a Pro license, if a licensing service is added.
- **Chat widget:** Provide the frontend chat interface and its styling.

## Development notes

- Keep API credentials in protected WordPress settings; never commit secrets.
- Sanitize and validate settings on input, escape output, and use nonces and capability checks for administrative actions.
- Use WordPress HTTP APIs for remote requests and handle provider errors, rate limits, and timeouts.
- Test against the supported WordPress and PHP versions before release.

## License

No license has been selected for this repository yet. Add a license before distributing the plugin.
