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

## Inteligencia Artificial (IA)

- **Resumen:** La aplicación integra capacidades de IA para enriquecer, analizar y transformar currículums JSON (JSON Resume). Soporta generación y edición de texto (resúmenes, cartas), extracción estructurada, embeddings para búsqueda semántica y conversaciones asistidas mediante agentes.
- **Capacidades principales:** agentes de texto (clases en `app/Ai/Agents`), `Embeddings` y búsqueda semántica (tabla `resume_embeddings`), salida estructurada con `HasStructuredOutput`, transcripción/audio y generación de recursos enriquecidos.
- **Endpoints y flujo:** `POST /api/chat` es el punto de entrada conversacional; los agentes pueden orquestar embeddings, herramientas proveedor (`web search`, `web fetch`, `file search`) y devolver respuestas con fuentes y metadata.
- **Configuración (.env):** variables relevantes: `AI_DEFAULT_PROVIDER` / `AI_PROVIDER`, `OLLAMA_URL`, `OLLAMA_DEPLOYMENT`, `OLLAMA_EMBEDDING_DEPLOYMENT`, `OLLAMA_TIMEOUT`, así como `OPENAI_API_KEY` / `OPENAI_MODEL` si se usan proveedores externos.
- **Rendimiento y costes:** cache de embeddings, control de tokens/temperatura, atributos PHP para seleccionar modelos (`#[Temperature]`, `#[Provider]`), colas y streaming para operaciones largas y procesamiento asíncrono.
- **Testing y seguridad:** soporta fakes para tests (`Agents::fake()`, `Embeddings::fake()`), validación de salidas con esquemas JSON, registro/auditoría de solicitudes IA en `ai_requests` y control de acceso a herramientas externas.
- **Casos de uso:** generar un resumen optimizado para una oferta, extraer y normalizar competencias del CV, mapear candidatos a ofertas por similaridad semántica, o mantener conversaciones guiadas para mejorar contenido.

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
