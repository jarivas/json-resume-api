
# JSON Resume API

**Resumen:** API para gestionar entidades de un curriculum (JSON Resume). Este documento resume las entidades principales, sus relaciones y cómo ver la documentación del proyecto.

**Entidades**
- **Basic:** entidad principal que representa un currículum/resumen. Archivo: [app/Models/Basic.php](app/Models/Basic.php)
- **Work:** experiencia laboral. Pertenece a muchos `Basic` mediante `basic_works`. Archivo: [app/Models/Work.php](app/Models/Work.php)
- **Volunteer:** voluntariados. Pertenece a muchos `Basic` mediante `basic_volunteers`. Archivo: [app/Models/Volunteer.php](app/Models/Volunteer.php)
- **Education:** formaciones académicas. Pertenece a muchos `Basic` mediante `basic_educations`. Archivo: [app/Models/Education.php](app/Models/Education.php)
- **Award:** reconocimientos. Pertenece a muchos `Basic` mediante `basic_awards`. Archivo: [app/Models/Award.php](app/Models/Award.php)
- **Certificate:** certificaciones. Pertenece a muchos `Basic` mediante `basic_certificates`. Archivo: [app/Models/Certificate.php](app/Models/Certificate.php)
- **Publication:** publicaciones. Pertenece a muchos `Basic` mediante `basic_publications`. Archivo: [app/Models/Publication.php](app/Models/Publication.php)
- **Skill:** habilidades. Pertenece a muchos `Basic` mediante `basic_skills`. Archivo: [app/Models/Skill.php](app/Models/Skill.php)
- **Language:** idiomas. Pertenece a muchos `Basic` mediante `basic_languages`. Archivo: [app/Models/Language.php](app/Models/Language.php)
- **Interest:** intereses. Pertenece a muchos `Basic` mediante `basic_interests`. Archivo: [app/Models/Interest.php](app/Models/Interest.php)
- **Reference:** referencias. Pertenece a muchos `Basic` mediante `basic_references`. Archivo: [app/Models/Reference.php](app/Models/Reference.php)
- **Project:** proyectos. Pertenece a muchos `Basic` mediante `basic_projects`. Archivo: [app/Models/Project.php](app/Models/Project.php)

**Relaciones**
- La mayoría de las entidades tienen una relación many-to-many con `Basic`.
- Convención de pivotes: `basic_{entity_plural}` (por ejemplo `basic_works`, `basic_awards`).
- En los modelos se usan `BelongsTo(...)` y, en los controladores, las acciones de crear/actualizar usan `attach`/`sync` sobre la relación `basics`.

**Dónde ver la documentación de la API**
- Archivo estático generado: `public/docs` — puedes abrir directamente [public/docs/index.html](public/docs/index.html) si el servidor web sirve `public/`.
- Desde la aplicación en desarrollo:

  1. Con dependencias instaladas localmente:

     - `composer install`
     - `cp .env.example .env` y configura variables si hace falta
     - `php artisan key:generate`
     - `php artisan migrate --seed` (si necesitas datos)
     - `php artisan serve`

     Luego abre `http://localhost:8000/docs` o `http://localhost:8000/docs/index.html`.

  2. Usando el contenedor de desarrollo incluido (recomendado si usas Docker):

     - `docker compose up -d`
     - Ejecuta los comandos dentro del contenedor de dev si necesitas migrar/tests: `docker exec -it json-resume-api-dev-1 php artisan migrate --seed`
     - Para ejecutar tests: `docker exec json-resume-api-dev-1 php artisan test --filter=Feature\\Name` o ejecutar archivos sueltos `docker exec json-resume-api-dev-1 php artisan test tests/Feature/Work/CreateTest.php`

     Abre `http://localhost:8000/docs` en el navegador (o la URL/puerto que esté mapeado en tu `docker-compose.yml`).

**Notas técnicas**
- Los controladores devuelven ahora la relación `basics` cargada explícitamente cuando corresponde (se usa `load('basics')` o `with('basics')` en listados).
- Para evitar eager-loading por defecto en los modelos relacionados, los modelos que referencian `Basic` contienen `protected $with = [];`.
- Se añadió `.vscode/settings.json` configurando `php.validate.executablePath` a `/usr/local/bin/php` para apuntar al PHP del contenedor de desarrollo.

Si quieres, puedo:
- Hacer commit y push de este README (ahora está creado localmente).
- Generar una versión corta de la guía para desplegar en producción.

---
Archivo creado automáticamente por el equipo de desarrollo.
