# Resumen del proyecto

Breve descripción: aplicación Laravel que integra capacidades de IA para enriquecimiento semántico, búsqueda y flujos conversacionales mediante un servidor MCP (Model Context Protocol).

**Stack principal**
- Backend: Laravel
- Procesamiento asíncrono: colas y jobs
- Agentes y herramientas AI expuestas vía MCP

---

## Índice rápido
- Propósito
- Pipeline de embeddings
- MCP: arquitectura y rol
- Flujo de datos (alto nivel)
- Persistencia y versionado
- Seguridad y cumplimiento
- Observabilidad y pruebas

---

## Propósito
Esta aplicación orquesta modelos de IA (embeddings y LLMs) para ofrecer:
- Búsqueda semántica y recuperación basada en contenido (RAG).
- Sistemas de recomendación por similitud y clasificación semántica.
- Flujos conversacionales con contexto recuperado.

## Pipeline de embeddings
Descripción: los textos (documentos, resúmenes, campos libres) se normalizan y se transforman en vectores mediante un modelo de embeddings (proveedor configurable). Esos vectores se indexan en un datastore para búsquedas por similitud.

Puntos clave:
- Preprocesado: limpieza, normalización y chunking con overlap.
- Metadatos por fragmento: fuente, posición, timestamp, versión de modelo.
- Almacenamiento: tabla especializada o vector DB indexada.
- Optimización: batching, caché y reindexado incremental.

Ejemplo de flujo (alto nivel):

```
Texto fuente -> Normalización -> Chunking -> Embeddings (modelo) -> Indexación en VectorStore
```

## Búsqueda semántica / RAG
- Las consultas se vectorizan y se buscan vecinos más cercanos.
- Los documentos recuperados se usan como contexto para prompts que alimentan a un LLM.
- Resultado: respuestas más relevantes, ancladas en contenido de dominio.

Casos de uso típicos:
- Búsqueda de currículums o contenido
- Extracción de insights
- Recomendaciones basadas en similitud
- Soporte conversacional con contexto recuperado

## MCP (arquitectura y rol)
Qué es:
- Componente que expone herramientas (`tools`), recursos y prompts como APIs seguras y versionadas.

Funciones principales:
- Registro y versionado de herramientas.
- Validación de entrada/salida y control de acceso (tokens, scopes).
- Throttling, logging y trazabilidad de invocaciones y prompts.

Integración con agentes:
- Los agentes AI llaman a las herramientas MCP para ejecutar acciones (ej. `vectorize`, `search`, `generate`), permitiendo componer capacidades sin exponer la lógica interna al frontend.

## Flujo de datos (alto nivel)
- Usuario / Frontend
-→ API Laravel
-→ Controlador / Job
-→ MCP Tool (`vectorize`, `search`, `generate`)
-→ Proveedor AI (embeddings / LLM)
-→ Vector Store
-→ Resultado → Frontend

Operaciones pesadas (indexado, reindexado, embeddings masivos) se ejecutan en background jobs con monitoreo.

## Persistencia y modelos
- Se almacenan textos originales, embeddings y metadatos (timestamp, origen, versión de modelo) para reproducibilidad.
- Versionamiento de modelos y trazabilidad de prompts para auditoría y mejora continua.

## Seguridad, privacidad y cumplimiento
- Enmascarado / anonimización de datos sensibles antes de enviarlos a proveedores externos.
- Políticas de retención y control de acceso por roles.
- Logs y auditoría para trazabilidad de solicitudes AI y decisiones automatizadas.

## Observabilidad y pruebas
- Telemetría: latencia de embeddings/LLM, tasa de aciertos en recuperación, métricas de coste.
- Tests: suites unitarias e integración para pipelines críticos y validación de salidas estructuradas.

---

Si quieres, puedo:
- Añadir una tabla de términos (glosario).
- Generar ejemplos concretos de configuración (`.env`) o scripts para reindexado.
- Resumir en una versión aún más breve para la cabecera del README.

Fin del documento.