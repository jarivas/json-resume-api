# JSON Resume API + Chat

## Resumen

API Laravel para exponer datos de un currículum (JSON Resume) y un servicio de chat que responde exclusivamente sobre el CV. Proporciona:

- MCP servers (lectura) para entidades del CV: `Basic`, `Work`, `Education`, `Skills`, `Projects`, etc.
- Endpoint `POST /api/chat` que usa un `Agent` (laravel/ai) para responder preguntas relacionadas con el CV.
- Registro de llamadas a la IA (`ai_requests`) y protección de acceso a las rutas MCP (IP whitelist / header).

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
- `MCP_ALLOWED_IPS` — lista separada por comas con IPs/CIDR/wildcards permitidas (ej. `203.0.113.5,192.168.0.*,10.0.0.0/8`).

## Rutas y uso

- MCP read endpoints (GET): `/mcp/basic`, `/mcp/work`, `/mcp/education`, ...
  - Protegidos por middleware `AllowMcpProvider`.
  - Autorización: si `MCP_ALLOWED_IPS` no está vacío, las solicitudes se permiten solo si la IP remota coincide con la whitelist; si la whitelist está vacía, se requiere la cabecera `X-MCP-PROVIDER` igual a `config('ai.default')`.

Ejemplo: llamada MCP con cabecera

```bash
curl -H "X-MCP-PROVIDER: openai" https://your.app/mcp/basic
```

- Chat API (POST): `/api/chat`
  - Payload: `{ "message": "¿Qué experiencia tiene Test User?" }`
  - Respuesta: `{ "reply": "...", "sources": [], "session_id": null }`

## Seguridad y recomendaciones

- La implementación actual es idiomática y segura para entornos controlados, pero en producción se recomiendan mejoras:
  - Aplicar bloqueo por IP/ACL en la capa de red (firewall / nginx / LB) en lugar de depender solo de la aplicación.
  - Usar secretos o tokens firmados en vez de nombres simples en cabeceras (env var con secreto compartido, API tokens con Sanctum, mTLS, etc.).
  - Añadir soporte IPv6 si lo necesitas.

## AI / Agent

- Archivo principal: `app/Ai/Agents/ResumeAgent.php` — el agent está restringido para responder solo sobre el CV y tiene una heurística `allowsQuery()` para bloquear preguntas fuera de contexto.
- Registro: `ai_requests` guarda `prompt`, `message`, `reply` y `metadata` (previo enmascarado de emails/URLs/phone numbers).
- Memoria conversacional: `laravel/ai` provee `DatabaseConversationStore` y migraciones para `agent_conversations` y `agent_conversation_messages`. Para activar memoria automática añade `use Laravel\\Ai\\Concerns\\RemembersConversations;` en el Agent.

## Testing

Ejecuta tests unitarios/funcionales:

```bash
php artisan test --compact
php artisan test --filter ChatTest --compact
```

## Arquitectura y archivos clave

- `app/Mcp/Servers/*` — MCP servers (usan `ReadServerTrait` para index/show y filtros avanzados).
- `app/Mcp/Servers/Traits/ReadServerTrait.php` — filtros `field__gte`, `field__like`, `relacion.field__op`, etc.
- `app/Ai/Agents/ResumeAgent.php` — agente con instrucciones estrictas "resume-only".
- `app/Services/Chat/ChatService.php` — orquesta prompt, enmascara y crea registros en `ai_requests`.
- `app/Http/Middleware/AllowMcpProvider.php` — middleware con whitelist IP (exacta/wildcard/CIDR) y fallback por cabecera.
- `config/mcp.php` — lee `MCP_ALLOWED_IPS`.

## Operaciones recomendadas en producción

1. Restringe MCP en el borde (firewall / load balancer) y no solo en la app.
2. Reemplaza la cabecera por un token secreto o API token.
3. Habilita monitorización/alertas para accesos denegados y uso del endpoint IA.

## Contribuir / próximos pasos

- Añadir tests de integración para la whitelist (exact, wildcard, CIDR) y fallback de cabecera.
- Añadir soporte IPv6 en el middleware si se requiere.
- Implementar RAG (embeddings + vector store) para respuestas con evidencias referenciadas.

¿Quieres que añada tests para la whitelist o que convierta la cabecera en un secreto env var? Si sí, dime cuál prefieres y lo implemento.
