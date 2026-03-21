Copilot / Assistant instructions (project-specific)

- This repository exposes a JSON Resume and a chat agent limited to CV questions.
- Resume fragments are stored in the `resume_embeddings` table with columns: `vector` (JSON array), `vector_length` (int), `embedding_model` (string).
- Embeddings are L2-normalized at write time. Similarity ranking uses dot-product on normalized vectors.
- When writing code that touches embeddings:
  - Avoid unnecessary writes if the normalized vector is unchanged (use small epsilon comparison).
  - Use batching for embedding provider calls when possible.
  - Do not modify files under `/vendor`.

- Tests to run locally after changes:
  - `php artisan migrate` (if migrations changed)
  - `php artisan test --filter EmbeddingServiceTest` (unit)
  - `php artisan test --filter ResumeQATest` (feature)

- For chat/semantic context injection, use `App\Services\Ai\EmbeddingService::findMostSimilar()` and `App\Ai\Agents\ResumeAgent::semanticContext()`.
