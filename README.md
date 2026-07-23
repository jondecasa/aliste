# Aliste.es

Web de la comarca de Aliste (Zamora): pueblos, servicios, noticias, eventos, música y literatura tradicional, con un panel de administración para gestionar todo el contenido. Construida con Laravel 12 y Livewire/Volt.

> **Este README se mantiene vivo.** Cada vez que se añade, cambia o corrige algo relevante en el proyecto, este documento debe actualizarse en el mismo cambio (nueva sección, feature, comando, variable de entorno, nota de despliegue, etc.). Si haces un cambio y no sabes si toca actualizarlo, pregúntate: "¿esto cambia lo que alguien necesitaría saber para entender o mantener el proyecto?" Si la respuesta es sí, actualízalo.

## Índice

- [Stack tecnológico](#stack-tecnológico)
- [Funcionalidades](#funcionalidades)
  - [Sitio público](#sitio-público)
  - [Panel de administración](#panel-de-administración-admin)
  - [Roles y permisos](#roles-y-permisos)
  - [Automatización (tareas programadas)](#automatización-tareas-programadas)
  - [PWA y notificaciones push](#pwa-y-notificaciones-push)
- [Modelo de datos](#modelo-de-datos)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Puesta en marcha en local](#puesta-en-marcha-en-local)
- [Variables de entorno relevantes](#variables-de-entorno-relevantes)
- [Testing](#testing)
- [Despliegue en producción](#despliegue-en-producción)
- [Notas y gotchas conocidos](#notas-y-gotchas-conocidos)

## Stack tecnológico

- **Backend**: Laravel 12 (PHP 8.2+), Livewire 3 + Volt (componentes de página en un solo archivo `.blade.php`)
- **Base de datos**: MySQL/MariaDB
- **Frontend**: Tailwind CSS (colores/tema vía variables CSS en `resources/css/app.css`, soporte modo claro/oscuro con `darkMode: 'class'`), Alpine.js (incluido con Livewire)
- **Autenticación**: Laravel Breeze (adaptado a Livewire/Volt) + Google OAuth (Laravel Socialite)
- **Notificaciones push**: `laravel-notification-channels/webpush` + Service Worker propio (PWA)
- **Editor de contenido enriquecido**: TinyMCE (self-hosted, copiado a `public/vendor/tinymce` en el postinstall de npm)
- **Mapas**: Leaflet
- **Calendario**: FullCalendar (day grid + lista + interacción)
- **Optimización de imágenes**: Intervention Image, conversión automática a WebP
- **Testing**: PHPUnit + `Livewire\Volt\Volt::test()`, base de datos dedicada `aliste_testing`

## Funcionalidades

### Sitio público

- **Home** (`/`) — cabecera con banner editable, calendario de eventos de toda la comarca, pueblos destacados, servicios, últimas noticias.
- **Pueblos** (`/pueblos`, `/pueblos/{slug}`) — listado con búsqueda; ficha de cada pueblo con foto, página personalizada en HTML enriquecido, mapa de puntos de interés (Leaflet, con foto en el popup si existe).
  - **Calendario del pueblo** (`/pueblos/{slug}/calendario`) — FullCalendar con los eventos de ese pueblo. Los eventos que empiezan antes de las 5:00 de la madrugada se ordenan como el **último** evento de su propio día (no el primero), aunque cronológicamente sean la hora más temprana — pensado para conciertos/verbenas que se alargan pasada la medianoche.
  - **Gente del pueblo** (`/pueblos/{slug}/gente`) — usuarios registrados que tienen ese pueblo asignado.
- **Servicios** (`/servicios`) — directorio de negocios y servicios locales, filtro por categoría, buscador.
- **Noticias** (`/noticias`, `/noticias/{slug}`) — listado con destacada + filtro por categoría, ficha de noticia. Alimentado en parte por scraping automático (ver [automatización](#automatización-tareas-programadas)).
- **Música** (`/musica`, `/musica/{slug}`) — listado con buscador y filtro por categoría; ficha de cada canción con:
  - uno o varios archivos de audio reales (reproducibles, con título propio cada uno),
  - portada (imagen subida y optimizada),
  - descripción en HTML enriquecido,
  - letra de la canción, mostrada con un formato tipográfico distintivo (serif, cursiva, centrada).
  - No tiene enlace en el menú principal (de momento); se llega desde el buscador general o accediendo directamente a `/musica`.
- **Buscador unificado** (`/buscar`) — busca a la vez en pueblos, servicios, noticias, eventos y canciones. También hay una caja de búsqueda compacta en la cabecera (escritorio y móvil).
- **Contacto** (`/contacto`) — formulario con reCAPTCHA v2 + honeypot + rate limit (2 envíos por IP y hora).
- **Páginas legales**: política de cookies (`/politica-cookies`) y de privacidad (`/politica-privacidad`).
- **Páginas de error personalizadas**, con el diseño propio del sitio y mensajes en el habla local:
  - 403 — "¡Rapá, nun pues pasar!"
  - 404 — "¡Per más que busqué, nun salió!"
  - 419 — "La página ha caducado"
  - 429 — "Demasiadas peticiones"
  - 500 / 5xx — "¡Algo se estropió!"
  - 503 — "¡Algo se estropió!" (modo mantenimiento)
- **Modo claro/oscuro** — preferencia guardada por usuario autenticado (`users.tema`).
- **PWA instalable** — `manifest.json` + Service Worker (`public/sw.js`), con notificaciones push suscribibles desde el perfil.
- **TWA (Trusted Web Activity)** — la web está publicada también como app en Google Play a través de una envoltura TWA (ver `public/.well-known/assetlinks.json`).

### Panel de administración (`/admin`)

Todo el contenido del sitio se gestiona desde aquí, con permisos según el rol del usuario (ver [Roles y permisos](#roles-y-permisos)):

| Sección | Ruta | Quién puede acceder |
|---|---|---|
| Dashboard | `/admin` | redactor, administrador |
| Noticias | `/admin/noticias` | redactor, administrador |
| Eventos | `/admin/eventos` | redactor (solo su pueblo), administrador |
| Puntos de interés | `/admin/puntos-interes` | redactor (solo su pueblo), administrador |
| Banner de la home | `/admin/banner` | solo administrador |
| Pueblos | `/admin/pueblos` | solo administrador |
| Categorías | `/admin/categorias` | solo administrador |
| Servicios | `/admin/servicios` | solo administrador |
| Música | `/admin/canciones` | solo administrador |
| Literatura (obras literarias) | `/admin/obras-literarias` | solo administrador |
| Usuarios (cambio de rol) | `/admin/usuarios` | solo administrador |

Características comunes del panel:
- Tablas con buscador, paginación y scroll horizontal en móvil; la columna de acciones queda fija (`sticky`) a la derecha con botones de icono (lápiz / papelera) para ahorrar espacio.
- Editor de contenido enriquecido (TinyMCE) reutilizado en pueblos, canciones y banner de la home, con subida de imágenes integrada (`POST /admin/editor/imagenes`) y ciclo de vida controlado: se crea una instancia nueva cada vez que se abre el modal y se destruye al cerrarlo (por cualquier vía: Cancelar, click fuera, Escape o guardado), para evitar errores al reabrir.
- Subida de imágenes con optimización automática a WebP (`App\Support\OptimizadorImagenes`), usada en pueblos, eventos, puntos de interés, canciones (portada) y avatar de usuario.
- Canciones admite subir **varios archivos de audio a la vez o en tandas sucesivas** (se van acumulando en vez de sustituirse), cada uno con su propio título editable y opción de borrado individual.
- Eventos: un redactor puede editar/eliminar cualquier evento de su propio pueblo (no solo los que él creó) mientras la fecha sea hoy o futura; un administrador puede editar cualquier evento sin restricción de fecha.

### Roles y permisos

Tres roles (`users.rol`): `administrador`, `redactor`, `invitado`. Definidos como Gates en `App\Providers\AppServiceProvider`:

- `administrar` — solo administrador. Acceso total al panel.
- `redactar-noticias` — administrador o redactor. Acceso a noticias.
- `gestionar-contenido-pueblo` — administrador, o redactor con un pueblo asignado (`pueblo_id` no nulo). Acceso a eventos y puntos de interés, restringido a su propio pueblo si es redactor.

Los usuarios `invitado` no tienen acceso al panel; solo pueden usar el sitio público, elegir "su pueblo" en el perfil (para ver el enlace "Mi pueblo" y activar el filtro de notificaciones) y suscribirse a notificaciones push.

### Automatización (tareas programadas)

Definidas en `routes/console.php`, todas envían un email a `jonapweb@gmail.com` si fallan (`emailOutputOnFailure`):

| Comando | Frecuencia | Qué hace |
|---|---|---|
| `notificaciones:eventos-del-dia` | diario 10:00 | Envía notificación push con los eventos del día (de su pueblo y/o principales de otros pueblos, según preferencia del usuario) |
| `noticias:scrapear` | 2x/día (14:00 y 22:00) | Scraping de noticias recientes de la comarca desde ZA49 (máx. 2 por ejecución, publicadas en las últimas 6h) |
| `sitemap:generar` | diario 03:00 | Regenera `public/sitemap.xml` con todas las URLs públicas |
| `backup:base-datos` | cada 3 días a las 04:00 | Vuelca la base de datos comprimida en `storage/app/backups`, conservando los 10 backups más recientes |
| `servicios:importar` | manual | Importa/actualiza el listado de servicios publicado en aliste.info (ejecuta el `ServicioSeeder`) |

Todas las horas se evalúan en la zona horaria configurada (`Europe/Madrid`, ver [Notas y gotchas](#notas-y-gotchas-conocidos)).

### PWA y notificaciones push

- Los usuarios autenticados pueden suscribirse/desuscribirse a notificaciones push desde su perfil (`POST/DELETE /push/suscribirse`, `/push/desuscribirse`).
- Dos tipos de notificación (`App\Notifications`): `EventosMiPueblo` (eventos de su propio pueblo) y `EventosPrincipalesOtrosPueblos` (eventos marcados como "principal" en otros pueblos), configurables por el usuario (`notif_eventos_mi_pueblo`, `notif_eventos_otros_pueblos`).
- Claves VAPID configurables por entorno (ver variables de entorno).

## Modelo de datos

Tablas principales (ver `database/migrations` para el detalle completo, 35 migraciones):

- `users` — con `rol`, `pueblo_id`, `avatar`, `tema` (claro/oscuro), preferencias de notificación, `google_id` (login social)
- `pueblos` — nombre, slug, coordenadas, descripción, `contenido_html` (página propia), portada, población, altitud, `es_cabecera`
- `categorias` — nombre, slug, `grupo` (noticia/servicio/punto_interes/cancion/obra_literaria/evento), color (solo eventos); compartidas entre todos los tipos de contenido vía tablas pivote (`categoria_noticia`, `categoria_servicio`, `categoria_punto_interes`, `categoria_cancion`, `categoria_obra`)
- `servicios` — negocio/servicio local, con prioridad de orden, contacto, geolocalización
- `noticias` — con extracto, cuerpo, fuente externa (si viene de scraping) o propia
- `eventos` — con `pueblo_id`, `categoria_id`, `created_by`, fechas de inicio/fin, `es_principal` (marca el evento destacado del día en su pueblo; solo puede haber uno por día)
- `puntos_interes` — lugares de interés por pueblo, con foto y categorías
- `canciones` — título, artista, álbum, año, duración, portada, descripción (HTML), letra (texto plano con formato propio en la vista)
- `audios_cancion` — uno o varios archivos de audio por canción, con título y orden
- `obras_literarias` — literatura por pueblo/autor
- `banners` — contenido HTML único mostrado en la home (`Banner::obtener()` hace `firstOrCreate`)
- `push_subscriptions` — gestionada por el paquete de WebPush

## Estructura del proyecto

```
app/
  Console/Commands/        Comandos artisan (scraping, backups, sitemap, notificaciones)
  Http/Controllers/        Controladores clásicos: auth Google, subida de imágenes del editor, push
  Models/                  Eloquent
  Notifications/           Notificaciones push
  Providers/               Gates de autorización
  Support/                 Helpers (OptimizadorImagenes)
resources/
  views/livewire/public/   Páginas públicas (componentes Volt de una sola clase)
  views/livewire/admin/    CRUDs del panel de administración
  views/livewire/pages/auth/  Login, registro, recuperación de contraseña (Breeze + Volt)
  views/layouts/           Layouts público y de admin, layout de auth (split con vídeo de fondo)
  views/errors/            Páginas de error personalizadas
  css/app.css              Design tokens (colores, tipografías) y modo oscuro
database/
  migrations/              Esquema
  seeders/                 Datos base + contenido de ejemplo (incluye ficheros reales de audio/imagen en seeders/data/)
routes/
  web.php, admin.php, auth.php, console.php
tests/
  Feature/, Unit/          PHPUnit + Volt::test()
```

## Puesta en marcha en local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configurar en `.env` al menos `DB_*` (MySQL/MariaDB local) y `APP_URL`. Después:

```bash
php artisan migrate --seed
php artisan storage:link
composer run dev   # levanta server, queue listener, logs (pail) y vite a la vez
```

`composer run dev` es el comando recomendado para desarrollo (usa `concurrently` para levantar `php artisan serve`, `queue:listen`, `pail` y `npm run dev` en paralelo). Alternativa manual: `php artisan serve` + `npm run dev` en terminales separadas.

## Variables de entorno relevantes

Además de las estándar de Laravel:

| Variable | Para qué |
|---|---|
| `APP_TIMEZONE` (config, no env) | Fijada en `config/app.php` a `Europe/Madrid` — **no** usar UTC, ver [gotchas](#notas-y-gotchas-conocidos) |
| `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` | Login con Google (Socialite) |
| `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY` | reCAPTCHA v2 en registro y contacto (vacíos en testing) |
| `VAPID_SUBJECT`, `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY` | Notificaciones push (WebPush) |
| `MAIL_CONTACT_TO` | Destino del formulario de contacto |
| `MAIL_FROM_ADDRESS` | Debe coincidir con el buzón autenticado en el SMTP en uso |

## Testing

```bash
php artisan test
```

Usa una base de datos MySQL dedicada `aliste_testing` (configurada en `phpunit.xml`, no SQLite). Antes de correr los tests por primera vez, crea esa base de datos vacía; las migraciones se aplican solas vía `RefreshDatabase`.

## Despliegue en producción

Servidor Plesk (AlmaLinux) en `/var/www/vhosts/aliste.es/httpdocs`. Tras cada `git pull`:

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan config:clear   # si se cambió algún config/*.php
```

Comprobar además:
- Que `public/storage` sigue siendo un enlace simbólico a `storage/app/public` (`php artisan storage:link` si no existe).
- Que los ficheros subidos por Livewire son propiedad del usuario del hosting (`chown`/`chmod`, ver gotcha de permisos más abajo) — especialmente tras ejecutar cualquier `artisan` como `root`.

## Notas y gotchas conocidos

- **Zona horaria: `Europe/Madrid`, no UTC.** La app estuvo en UTC hasta que se detectó que los eventos del calendario se mostraban desplazados ~2h (un evento a las 22:30 aparecía a las 00:30 del día siguiente), porque las horas se etiquetaban internamente como UTC y el navegador las reconvertía a su hora local al exportarlas a FullCalendar. Cambiar `config('app.timezone')` lo corrige de raíz sin tocar los datos ya guardados; además corrige de paso los horarios reales de ejecución de las tareas programadas.
- **Nunca ejecutar `artisan` como `root` en producción sin corregir permisos después.** Los ficheros/carpetas que se crean así quedan con dueño `root:root`, y el usuario con el que corre PHP-FPM en Plesk (`aliste.es_xg8umgdiae:psacln`, o el que corresponda a la subscripción) no puede escribir ni sobrescribir ahí después. Síntoma típico: subir una imagen desde el panel "no da error" pero el enlace sale roto tanto en público como en el propio panel, porque el fichero nunca llega a escribirse aunque la ruta se guarde en la base de datos. Solución tras ejecutar algo como root:
  ```bash
  chown -R aliste.es_xg8umgdiae:psacln /var/www/vhosts/aliste.es/httpdocs/storage/app/public
  chmod -R 775 /var/www/vhosts/aliste.es/httpdocs/storage/app/public
  ```
- **Livewire inyecta comentarios HTML** (`<!--[if BLOCK]><![endif]-->`) alrededor de `@if`/`@foreach` en cualquier componente Livewire/Volt. Es intencionado (mejora la fiabilidad del "morphing" del DOM tras cada actualización) y invisible para cualquier visitante; no se debe intentar eliminar salvo que se asuma perder esa fiabilidad (hay un toggle oficial, `inject_morph_markers` en `config/livewire.php`, pero se decidió no usarlo).
- **Los inputs `<input type="file" multiple>` sustituyen su selección en cada uso**, no la acumulan. El formulario de canciones usa una propiedad "de paso" (`nuevaSeleccionAudios`) que se vuelca a un array persistente (`nuevosAudios`) en cada cambio, precisamente para poder subir varios audios en tandas sucesivas sin perder los ya seleccionados.
- **Los eventos de madrugada (antes de las 5:00) no cambian de día**, solo se reordenan como el último evento de su propio día real — tanto en la rejilla del calendario (`eventOrder` personalizado en FullCalendar) como en el panel lateral de "eventos del día" (que tiene su propio ordenado en JS, independiente del de FullCalendar).
- Tras cualquier cambio que añada una clase de Tailwind que no se usara ya en ningún otro sitio del proyecto (p. ej. `dark:prose-invert`, `whitespace-pre-line`), hace falta `npm run build` en producción — Tailwind solo genera en el CSS final las clases que detecta escaneando el código en el momento del build.
