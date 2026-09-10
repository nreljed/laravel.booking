import { defineConfig } from "vitest/config";
import path from "node:path";
import { realpathSync } from "node:fs";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const core = "./vendor/nreljed/dashboard-core/resources/js";

export default defineConfig({
    server: {
        fs: {
            allow: [realpathSync("./vendor/nreljed/dashboard-core")],
        },
    },
    test: {
        environment: "jsdom",
        globals: true,
        passWithNoTests: true,
        include: [
            "resources/js/**/*.test.{ts,tsx}",
        ],
        setupFiles: ["./resources/js/test-setup.ts"],
        coverage: {
            provider: "v8",
            reporter: ["text", "lcov"],
            include: [
                "resources/js/**/*.{ts,tsx}",
            ],
            exclude: [
                "resources/js/**/__tests__/**",
                "resources/js/**/*.test.*",
                "resources/js/Components/ui/**",
            ],
        },
    },
    resolve: {
        alias: {
            "@": path.resolve(__dirname, core),
            "@dashboard": path.resolve(__dirname, core),
            "@dashboard-posts": path.resolve(
                __dirname,
                "./vendor/nreljed/dashboard-core/src/Posts/Resources/js"
            ),
            "@dashboard-blog": path.resolve(
                __dirname,
                "./vendor/nreljed/dashboard-core/src/Blog/Resources/js"
            ),
            "@booking": path.resolve(
                __dirname,
                "./vendor/nreljed/booking-module/src/Booking/Resources/js"
            ),
            "@properties": path.resolve(
                __dirname,
                "./vendor/nreljed/booking-module/src/Properties/Resources/js"
            ),
        },
    },
});
