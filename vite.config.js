import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import AutoImport from "unplugin-auto-import/vite";
import Components from "unplugin-vue-components/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: "resources/js/app.js",
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        AutoImport({
            imports: [
                "vue",
                "@vueuse/core",
                {
                    "@inertiajs/vue3": [
                        "router",
                        "useForm",
                        "usePage",
                        "useRemember",
                    ],
                },
            ],
            dts: "resources/js/auto-imports.d.ts",
            dirs: ["resources/js/composables", "resources/js/stores"],
            vueTemplate: true,
        }),
        Components({
            dirs: ["resources/js/components", "resources/js/layouts"],
            extensions: ["vue"],
            dts: "resources/js/components.d.ts",
            directoryAsNamespace: true,
        }),
    ],
    resolve: {
        alias: {
            "@": "/resources/js",
        },
    },
});
