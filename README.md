# JSON Resume API + Chat

## Resumen

API REST en Laravel para exponer datos de un currículum (JSON Resume) y un servicio de chat que responde exclusivamente sobre el CV.

La aplicación incluye:

- Endpoints REST para entidades del CV (`Basic`, `Work`, `Education`, `Skills`, `Projects`, etc.).
- Endpoints MCP de solo lectura para consultar información del CV.
- Endpoint de chat (`POST /api/chat`) que usa un `Agent` para responder preguntas sobre el currículum.
- Registro de solicitudes a la IA en `ai_requests`.

## Entorno de ejecución

Este proyecto se ejecuta en contenedores Docker y está preparado para desarrollo en **Dev Container** (VS Code).

## Instalación rápida

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Variables relevantes en `.env`

- `AI_PROVIDER` — proveedor por defecto (ej. `openai`).
- `OPENAI_API_KEY` — clave API si usas OpenAI.
- `OPENAI_MODEL` — modelo por defecto (ej. `gpt-3.5-turbo`).

## Endpoints

La especificación/documentación de endpoints está en:

- `.scribe/endpoints/00.yaml`
- `README_API_REQUESTS.md` — documentación de los campos de las peticiones (validaciones y ejemplos)

Rutas principales:

- MCP (GET): `/mcp/basic`, `/mcp/work`, `/mcp/education`, ...
- Chat (POST): `/api/chat`

## Chat API

- Endpoint: `POST /api/chat`
- Payload de ejemplo:

```json
{ "message": "¿Qué experiencia tiene Test User?" }
```

- Respuesta de ejemplo:

```json
{ "reply": "...", "sources": [], "session_id": null }
```

## MCP

Los endpoints MCP exponen datos del CV en modo lectura.

## Arquitectura y archivos clave

- `app/Mcp/Servers/*` — MCP servers.
- `app/Mcp/Servers/Traits/ReadServerTrait.php` — lógica de lectura y filtros.
- `app/Ai/Agents/ResumeAgent.php` — agente restringido a consultas del CV.
- `app/Services/Chat/ChatService.php` — orquestación de chat y registro de solicitudes.

## Testing

```bash
php artisan test --compact
php artisan test --filter ChatTest --compact
```

## Embeddings and Semantic Search

- The app persists resume fragments into `resume_embeddings` and stores vectors (JSON) plus metadata.
- Embeddings are generated synchronously on model `saved` via `ResumeModelObserver` and on import.
- Vectors are L2-normalized at write-time; similarity ranking uses dot-product on normalized vectors.

To run only embedding-related tests:

```bash
php artisan test --filter EmbeddingServiceTest --compact
php artisan test --filter ResumeQATest --compact
```
