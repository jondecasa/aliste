# Android (TWA) — Aliste.es

Proyecto Android generado con [Bubblewrap](https://github.com/GoogleChromeLabs/bubblewrap) que empaqueta la PWA de Aliste.es (`https://aliste.es`) como una Trusted Web Activity (TWA) para Google Play. Paquete: `es.aliste.app`.

Esta carpeta contiene el **código fuente y la configuración** del proyecto, no los artefactos generados (`build/`, `.aab`, `.apk`) ni el `local.properties` (específico de cada máquina) — todo eso se regenera.

## Requisitos para compilar en una máquina nueva

- Node.js y `@bubblewrap/cli` instalado globalmente: `npm install -g @bubblewrap/cli`
- JDK 17 de **64 bits** (el JDK embebido en `.bubblewrap/jdk` de algunas instalaciones es de 32 bits y falla al reservar el heap de Gradle — usar uno de 64 bits, p. ej. Eclipse Temurin, y apuntarlo con `org.gradle.java.home` en `gradle.properties` si hace falta).
- Android SDK con la plataforma 36 instalada (Android Studio → SDK Manager).
- Crear `local.properties` en esta carpeta con:
  ```
  sdk.dir=C:\\ruta\\a\\Android\\Sdk
  ```

## Cómo compilar

```bash
./gradlew bundleRelease   # genera app/build/outputs/bundle/release/app-release.aab (sin firmar)
```

Para generar el `.aab`/`.apk` firmados y listos para subir a Play Console, usar Bubblewrap directamente desde esta carpeta:

```bash
bubblewrap build
```

Pedirá la contraseña del keystore de firma.

## Keystore de firma (`android.keystore`)

**No está en esta carpeta ni en el repositorio, a propósito** — es la clave privada con la que Play Store identifica las actualizaciones de la app; si se sube a git queda expuesta en el historial para siempre, incluso si luego se borra. `twa-manifest.json` espera encontrarla en `C:\Users\Batu157\android.keystore` (campo `signingKey.path`); si esa ruta cambia, hay que actualizarla ahí.

**Debe hacerse una copia de seguridad de `android.keystore` por separado**, fuera de git (gestor de contraseñas, almacenamiento cifrado, etc.). Si se pierde sin backup, no se podrá volver a publicar actualizaciones de esta app en Play Store con el mismo paquete — habría que publicarla como app nueva.

## Notificaciones push mostradas como "Chrome" en vez de como la app

Aunque la verificación de Digital Asset Links funcione (sin barra de navegador), las notificaciones push pueden seguir mostrándose como "vía Chrome" en lugar de con el icono/nombre de la app — esto depende de la **delegación de notificaciones** de `androidbrowserhelper` (`DelegationService`), que en la versión 2.6.2 tiene un [problema abierto sin resolver](https://github.com/GoogleChrome/android-browser-helper/issues/563) con exactamente este síntoma en `targetSdkVersion`/Android recientes: `Notification.requestPermission()` no se intercepta para delegar al diálogo nativo de Android. No es un fallo de configuración de este proyecto.

Se actualizó la librería a la versión 2.7.2 (la más reciente disponible) como intento de solución, lo que obligó a subir `minSdkVersion` de 21 a 23 (Android 6.0), ya que 2.7.2 no soporta versiones anteriores. No hay confirmación de que esto resuelva el problema — si sigue sin funcionar tras probarlo, es una limitación de la librería de Google, no de este código.

## Si esta carpeta se pierde

1. `npm install -g @bubblewrap/cli`
2. `bubblewrap init --manifest https://aliste.es/manifest.json` (o restaurar `twa-manifest.json` desde este repo y ejecutar `bubblewrap update` en su carpeta)
3. Restaurar `android.keystore` desde su copia de seguridad y actualizar la ruta en `twa-manifest.json` si es necesario.
4. Restaurar el `versionCode`/`versionName` actuales desde `twa-manifest.json` (ya están aquí) antes de generar la siguiente versión, para no repetir un número ya publicado en Play Console.
