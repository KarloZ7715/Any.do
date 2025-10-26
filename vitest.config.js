import { defineConfig } from "vitest/config";
import vue from "@vitejs/plugin-vue";
import { fileURLToPath } from "node:url";

export default defineConfig({
    plugins: [vue()],
    test: {
        globals: true,
        environment: "jsdom",
        setupFiles: ["./tests/javascript/setup.js"],
        include: ["tests/javascript/**/*.{test,spec}.{js,ts}"],
        coverage: {
            provider: "v8",
            reporter: ["text", "json", "html"],
            include: ["resources/js/**/*.{js,vue}"],
            exclude: [
                "resources/js/auto-imports.d.ts",
                "resources/js/components.d.ts",
                "resources/js/**/*.d.ts",
            ],
        },
    },
    resolve: {
        alias: {
            "@": fileURLToPath(new URL("./resources/js", import.meta.url)),
            "~": fileURLToPath(new URL("./resources", import.meta.url)),
        },
    },
});
