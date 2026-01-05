# Руководство по настройке проекта (Laravel 12 + Vue 3 + Inertia + TypeScript)

Этот документ описывает, как создать новый проект с конфигурацией, идентичной текущему (`p61_mz`).

## 1. Инициализация Laravel проекта

Создайте новый проект Laravel. Поскольку используется версия 12 (dev/latest), убедитесь, что у вас свежий PHP (8.2+).

```bash
composer create-project laravel/laravel:^12.0 my-new-project
cd my-new-project
```

## 2. Backend зависимости (Composer)

Установите необходимые пакеты для бэкенда.

### Основные пакеты

```bash
composer require inertiajs/inertia-laravel \
    laravel/fortify \
    laravel/sanctum \
    laravel/scout \
    algolia/algoliasearch-client-php \
    meilisearch/meilisearch-php \
    http-interop/http-factory-guzzle \
    laravel/wayfinder
```

*Примечание: `laravel/wayfinder` может потребовать добавления репозитория, если он не опубликован в Packagist, или проверки его доступности.*

### Dev-зависимости

Убедитесь, что установлены следующие инструменты разработки (обычно идут по умолчанию, но стоит проверить):

```bash
composer require --dev \
    fakerphp/faker \
    laravel/pail \
    laravel/pint \
    laravel/sail \
    mockery/mockery \
    nunomaduro/collision \
    phpunit/phpunit
```

## 3. Frontend зависимости (NPM)

Проект использует **Vite 7**, **Vue 3**, **TypeScript** и **Tailwind CSS 4**.

### Установка зависимостей

```bash
# Фреймворк и основные библиотеки
npm install vue @inertiajs/vue3 @vueuse/core

# UI и стили (Tailwind 4, Icons, Utils)
npm install tailwindcss @tailwindcss/vite clsx tailwind-merge class-variance-authority lucide-vue-next reka-ui tw-animate-css vue-sonner

# Таблицы (если нужны)
npm install @tanstack/vue-table @tanstack/table-core

# Инструменты сборки и Typescript (DevDependencies)
npm install -D vite laravel-vite-plugin @vitejs/plugin-vue @laravel/vite-plugin-wayfinder
npm install -D typescript vue-tsc @types/node

# Линтинг и форматирование (ESLint 9 + Prettier)
npm install -D eslint prettier eslint-config-prettier eslint-plugin-vue @eslint/js @vue/eslint-config-typescript typescript-eslint prettier-plugin-organize-imports prettier-plugin-tailwindcss
```

## 4. Конфигурация Vite (`vite.config.ts`)

Создайте или обновите файл `vite.config.ts` в корне проекта. Обратите внимание на плагины `tailwindcss()` и `wayfinder()`.

```typescript
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devOrigin = env.VITE_DEV_ORIGIN || 'http://localhost:3000';
    const devHost = env.VITE_DEV_HOST || 'localhost';

    return {
        plugins: [
            laravel({
                input: ['resources/js/app.ts'],
                ssr: 'resources/js/ssr.ts',
                refresh: true,
            }),
            tailwindcss(),
            wayfinder({
                formVariants: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        server: {
            host: '0.0.0.0',
            port: parseInt(env.VITE_PORT) || 5183,
            cors: {
                origin: true,
                credentials: true,
            },
            strictPort: true,
            watch: {
                usePolling: true,
            },
            hmr: {
                host: devHost,
                port: parseInt(env.VITE_PORT) || 5183,
            },
        },
    };
});
```

## 5. Конфигурация TypeScript (`tsconfig.json`)

Создайте `tsconfig.json` для поддержки Vue и алиасов `@/*`.

```json
{
    "compilerOptions": {
        "target": "ESNext",
        "useDefineForClassFields": true,
        "lib": ["ESNext", "DOM", "DOM.Iterable"],
        "jsx": "preserve",
        "jsxImportSource": "vue",
        "module": "ESNext",
        "moduleResolution": "bundler",
        "paths": {
            "@/*": ["./resources/js/*"]
        },
        "types": ["vite/client", "./resources/js/types"],
        "resolveJsonModule": true,
        "allowJs": true,
        "sourceMap": true,
        "noEmit": true,
        "isolatedModules": true,
        "esModuleInterop": true,
        "forceConsistentCasingInFileNames": true,
        "strict": true,
        "skipLibCheck": true
    },
    "include": [
        "resources/js/**/*.ts",
        "resources/js/**/*.d.ts",
        "resources/js/**/*.tsx",
        "resources/js/**/*.vue"
    ]
}
```

## 6. Конфигурация ESLint (`eslint.config.js`)

Настройка для ESLint 9 (Flat Config) с поддержкой Vue и Prettier.

```javascript
import prettier from 'eslint-config-prettier/flat';
import vue from 'eslint-plugin-vue';
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';

export default defineConfigWithVueTs(
    vue.configs['flat/essential'],
    vueTsConfigs.recommended,
    {
        ignores: ['vendor', 'node_modules', 'public', 'bootstrap/ssr', 'tailwind.config.js', 'resources/js/components/ui/*'],
    },
    {
        rules: {
            'vue/multi-word-component-names': 'off',
            '@typescript-eslint/no-explicit-any': 'off',
        },
    },
    prettier,
);
```

## 7. Точка входа приложения (`resources/js/app.ts`)

Настройте инициализацию Inertia приложения.

```typescript
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
// Убедитесь, что у вас есть этот composable или удалите строчку
// import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
```

## 8. Tailwind CSS (`resources/css/app.css`)

Для Tailwind CSS v4 достаточно импортировать его в CSS файл:

```css
@import "tailwindcss";
```

## 9. Дополнительные настройки

1.  **package.json scripts:**
    Добавьте/обновите скрипты:
    ```json
    "scripts": {
        "build": "vite build",
        "build:ssr": "vite build && vite build --ssr",
        "dev": "vite",
        "format": "prettier --write resources/",
        "format:check": "prettier --check resources/",
        "lint": "eslint . --fix"
    }
    ```

2.  **Структура папок JS:**
    Убедитесь, что структура `resources/js` соответствует путям в конфигах (например, наличие папок `pages`, `types`).