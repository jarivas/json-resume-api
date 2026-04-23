 # API — Campos de las peticiones

 Este documento resume los endpoints principales del API y los campos esperados en las peticiones. Está pensado como referencia rápida; para reglas de validación exactas revisa las clases `FormRequest` correspondientes.

 Base URL: `/api`

 Encabezados comunes
 - `Accept: application/json`
 - `Content-Type: application/json` (cuando hay body JSON)
 - `Authorization: Bearer <token>` (para endpoints protegidos)

 Autenticación (resumen)

 - POST `/api/authentication/login` — Inicia sesión. Body: `email`, `password`. Respuesta: `{ "token": "...", "expiresAt": "..." }`.
 - POST `/api/authentication/recovery` — Solicita recuperación por email.
 - POST `/api/authentication/change-password` — Cambia contraseña (autenticado).
 - POST `/api/authentication/refresh-token` — Renueva token (autenticado).
 - POST `/api/authentication/logout` — Revoca tokens (autenticado).

 Nuevos endpoints añadidos

 - GET `/api/iso/country` — Lista países (controlador: `App\Http\Controllers\Iso\Country`).
 - GET `/api/iso/language` — Lista idiomas (controlador: `App\Http\Controllers\Iso\Language`).
 - GET `/api/iso/currency` — Lista monedas (controlador: `App\Http\Controllers\Iso\Currency`).

 - POST `/api/import/education` — Importación de educaciones (controlador: `App\Http\Controllers\Import\EducationImportController`).
 - POST `/api/import/certificate` — Importación de certificados (controlador: `App\Http\Controllers\Import\CertificateImportController`).

 - POST `/api/chat` — Enviar mensaje al endpoint de chat (controlador: `App\Http\Controllers\Chat\Chat`).

 Nota: ya existen otros endpoints CRUD para recursos como `basic`, `award`, `certificate`, `education`, `interest`, `language`, `project`, `publication`, `reference`, `skill`, `volunteer`, `work`, y rutas de `import/json` y `import/resume`.

 Ejemplos rápidos (curl)

 - ISO (lista de países):

 ```bash
 curl -X GET "http://localhost/api/iso/country" \
   -H "Accept: application/json"
 ```

 - Importar educaciones (multipart/form-data, autenticado):

 ```bash
 curl -X POST "http://localhost/api/import/education" \
   -H "Accept: application/json" \
   -H "Authorization: Bearer <token>" \
   -F "file=@/path/to/file.csv"
 ```

 - Chat (ejemplo):

 ```bash
 curl -X POST "http://localhost/api/chat" \
   -H "Accept: application/json" \
   -H "Content-Type: application/json" \
   -d '{"message":"Hola"}'
 ```

IA / Inteligencia Artificial

- Resumen: La API integra capacidades de IA para enriquecer y transformar currículums JSON. Permite generación y edición de texto, extracción estructurada, embeddings para búsqueda semántica y conversaciones asistidas.
- Endpoints y agentes: El endpoint `POST /api/chat` sirve como punto de entrada para interacciones conversacionales y puede delegar a agentes internos que usan modelos configurables.
- Capacidades principales: agentes de texto (generación/edición), `Embeddings` para búsqueda semántica y emparejamiento de habilidades, `HasStructuredOutput` para respuestas JSON validadas, transcripción/audio y generación de recursos enriquecidos.
- Herramientas y extensibilidad: Soporte para herramientas proveedor (`web search`, `web fetch`, `file search`) y la interfaz `HasTools` para ampliar funcionalidades con fuentes externas.
- Configuración: Variables en el entorno para proveedores y modelos — por ejemplo `AI_DEFAULT_PROVIDER`, `OLLAMA_URL`, `OLLAMA_DEPLOYMENT`, `OLLAMA_EMBEDDING_DEPLOYMENT` y `OLLAMA_TIMEOUT`.
- Rendimiento y seguridad: cache de embeddings, control de tokens/temperatura, colas/streaming para operaciones largas, y fakes para testing (`Agents::fake()`, `Embeddings::fake()`).

Dónde encontrar las reglas de validación

 - Las reglas para cada endpoint suelen estar en `FormRequest` dentro de `app/Http/Requests`.

 Generación automática de campos

 - El proyecto incluye una sección auto-generada (a partir de `FormRequest`) que sirve como referencia rápida por recurso (create/update) y muestra los endpoints REST asociados. Puedo regenerar y enlazar esa sección para incluir los endpoints nuevos si lo deseas.
