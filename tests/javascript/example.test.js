import { describe, it, expect } from "vitest";

describe("Configuración de Vitest", () => {
    it("debería ejecutar tests correctamente", () => {
        expect(true).toBe(true);
    });

    it("debería sumar números correctamente", () => {
        const resultado = 2 + 2;
        expect(resultado).toBe(4);
    });

    it("debería tener route mock disponible", () => {
        expect(route).toBeDefined();
        expect(typeof route).toBe("function");
    });
});
