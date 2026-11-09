# WP_Plugin
wp-rag-assistant/
├── wp-rag-assistant.php          # Main entry file (plugin headers, initialization)
├── includes/
│   ├── class-indexer.php         # Hooks into post save/delete, handles chunking
│   ├── class-vector-db.php       # Handles API calls to Pinecone, Qdrant, or OpenAI Vector Stores
│   ├── class-rag-engine.php      # Orchestrates query embedding + retrieval + LLM prompt completion
│   ├── class-admin-settings.php  # Handles wp-admin settings page (API key, post types, model selection)
│   └── class-licensing.php       # Checks Pro license key validity
├── assets/
│   ├── js/
│   │   ├── admin.js              # Admin UI scripts (manual re-index button, stats)
│   │   └── chat-widget.js        # Light frontend chat widget (vanilla JS or React)
│   └── css/
│       └── chat-widget.css       # Styles for floating chat window
└── views/
    ├── admin-settings-page.php   # HTML markup for backend control panel
    └── frontend-chat-markup.php  # HTML container for the site widget
