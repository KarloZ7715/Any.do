import js from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import vueParser from "vue-eslint-parser";

export default [
    // Archivos a ignorar
    {
        ignores: [
            "vendor/**",
            "node_modules/**",
            "public/build/**",
            "public/hot",
            "storage/**",
            "bootstrap/cache/**",
            "resources/js/auto-imports.d.ts",
            "resources/js/components.d.ts",
        ],
    },

    // Configuración recomendada de ESLint
    js.configs.recommended,

    // Configuración para archivos JavaScript
    {
        files: ["**/*.js", "**/*.mjs"],
        languageOptions: {
            ecmaVersion: "latest",
            sourceType: "module",
            globals: {
                // Globales de Node.js
                process: "readonly",
                __dirname: "readonly",
                __filename: "readonly",
                module: "readonly",
                require: "readonly",
                // Globales de navegador
                window: "readonly",
                document: "readonly",
                navigator: "readonly",
                console: "readonly",
                setTimeout: "readonly",
                setInterval: "readonly",
                clearTimeout: "readonly",
                clearInterval: "readonly",
                confirm: "readonly",
                alert: "readonly",
                prompt: "readonly",
                // Globales de Vite
                import: "readonly",
                // Globales de Laravel + Ziggy
                route: "readonly",
            },
        },
        rules: {
            "no-console": ["warn", { allow: ["warn", "error"] }],
            "no-debugger": "warn",
            "no-unused-vars": [
                "error",
                {
                    argsIgnorePattern: "^_",
                    varsIgnorePattern: "^_",
                },
            ],
            "prefer-const": "error",
            "no-var": "error",
            "object-shorthand": "error",
            "prefer-template": "warn",
            "prefer-arrow-callback": "warn",
            "arrow-spacing": "error",
            "comma-dangle": ["warn", "always-multiline"],
            quotes: ["warn", "single", { avoidEscape: true }],
            semi: ["warn", "never"],
            indent: "off", // Desactivado para ser flexible con Breeze
            "space-before-function-paren": [
                "warn",
                {
                    anonymous: "always",
                    named: "never",
                    asyncArrow: "always",
                },
            ],
        },
    },

    // Configuración para archivos Vue (recomendada de Vue 3)
    ...pluginVue.configs["flat/recommended"],

    // Configuración específica para archivos Vue
    {
        files: ["**/*.vue"],
        languageOptions: {
            parser: vueParser,
            ecmaVersion: "latest",
            sourceType: "module",
            parserOptions: {
                ecmaFeatures: {
                    jsx: true,
                },
            },
            globals: {
                // Auto-imports de unplugin-auto-import
                ref: "readonly",
                computed: "readonly",
                watch: "readonly",
                watchEffect: "readonly",
                onMounted: "readonly",
                onUnmounted: "readonly",
                onBeforeMount: "readonly",
                onBeforeUnmount: "readonly",
                reactive: "readonly",
                toRefs: "readonly",
                toRef: "readonly",
                // Inertia
                useForm: "readonly",
                usePage: "readonly",
                router: "readonly",
                // Stores
                usarStoreTareas: "readonly",
                usarStoreUsuario: "readonly",
            },
        },
        rules: {
            // Reglas de Vue
            "vue/multi-word-component-names": "off",
            "vue/no-v-html": "warn",
            "vue/require-default-prop": "off", // Desactivado para ser flexible con Breeze
            "vue/require-prop-types": "warn",
            "vue/component-name-in-template-casing": [
                "warn",
                "PascalCase",
                {
                    registeredComponentsOnly: false,
                },
            ],
            "vue/custom-event-name-casing": ["warn", "camelCase"],
            "vue/define-macros-order": [
                "warn",
                {
                    order: ["defineProps", "defineEmits"],
                },
            ],
            "vue/html-indent": "off", // Desactivado para ser flexible con Breeze
            "vue/max-attributes-per-line": "off",
            "vue/first-attribute-linebreak": "off",
            "vue/html-closing-bracket-newline": "off",
            "vue/multiline-html-element-content-newline": "off",
            "vue/singleline-html-element-content-newline": "off",
            "vue/attribute-hyphenation": ["warn", "always"],
            "vue/v-on-event-hyphenation": ["warn", "always"],
            "vue/html-self-closing": "off",
            "vue/attributes-order": "off",

            // Reglas de JavaScript dentro de Vue
            "no-console": ["warn", { allow: ["warn", "error"] }],
            "no-debugger": "warn",
            "no-unused-vars": "off", // Desactivado porque los auto-imports pueden parecer no usados
            "no-undef": "off", // Desactivado porque los auto-imports no están declarados
            "prefer-const": "error",
            "no-var": "error",
            "comma-dangle": ["warn", "always-multiline"],
            quotes: ["warn", "single", { avoidEscape: true }],
            semi: ["warn", "never"],
            indent: "off", // Desactivado para ser flexible
        },
    },

    // Configuración para archivos de configuración
    {
        files: ["*.config.js", "*.config.mjs"],
        languageOptions: {
            globals: {
                process: "readonly",
                __dirname: "readonly",
                __filename: "readonly",
                URL: "readonly",
            },
        },
        rules: {
            "no-console": "off",
        },
    },

    // Configuración para archivos de testing
    {
        files: ["tests/**/*.{js,ts}", "**/*.test.{js,ts}", "**/*.spec.{js,ts}"],
        languageOptions: {
            globals: {
                // Vitest globals
                describe: "readonly",
                it: "readonly",
                test: "readonly",
                expect: "readonly",
                beforeEach: "readonly",
                afterEach: "readonly",
                beforeAll: "readonly",
                afterAll: "readonly",
                vi: "readonly",
                // Node globals
                global: "readonly",
                // Browser globals
                window: "readonly",
                document: "readonly",
                // Custom globals
                route: "readonly",
                URLSearchParams: "readonly",
            },
        },
        rules: {
            "no-console": "off",
            "no-undef": "off",
        },
    },
];
