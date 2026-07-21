import { cpSync, existsSync, rmSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const __dirname = dirname(fileURLToPath(import.meta.url));
const source = join(__dirname, '..', 'node_modules', 'tinymce');
const destination = join(__dirname, '..', 'public', 'vendor', 'tinymce');

if (!existsSync(source)) {
    console.error('tinymce no está instalado en node_modules, ejecuta npm install primero.');
    process.exit(1);
}

if (existsSync(destination)) {
    rmSync(destination, { recursive: true, force: true });
}

cpSync(source, destination, { recursive: true });

console.log('TinyMCE copiado a public/vendor/tinymce');
