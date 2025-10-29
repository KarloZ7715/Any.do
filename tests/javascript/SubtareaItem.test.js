import { describe, it, expect } from "vitest";
import { mount } from "@vue/test-utils";
import SubtareaItem from "@/Components/Subtareas/SubtareaItem.vue";

describe("SubtareaItem.vue", () => {
    /**
     * Test 1: Renderiza correctamente con subtarea pendiente
     */
    it("renderiza correctamente con subtarea pendiente", () => {
        const subtarea = {
            id: 1,
            texto: "Comprar leche",
            estado: "pendiente",
        };

        const wrapper = mount(SubtareaItem, {
            props: { subtarea },
        });

        expect(wrapper.text()).toContain("Comprar leche");
        expect(wrapper.find('button[type="button"]').classes()).not.toContain(
            "line-through"
        );
    });

    /**
     * Test 2: Renderiza correctamente con subtarea completada
     */
    it("renderiza correctamente con subtarea completada", () => {
        const subtarea = {
            id: 2,
            texto: "Hacer ejercicio",
            estado: "completada",
        };

        const wrapper = mount(SubtareaItem, {
            props: { subtarea },
        });

        expect(wrapper.text()).toContain("Hacer ejercicio");
        // El botón de texto debe tener line-through
        const textoButton = wrapper
            .findAll("button")
            .find((btn) => btn.text().includes("Hacer ejercicio"));
        expect(textoButton.classes()).toContain("line-through");
    });

    /**
     * Test 3: Emite evento toggle cuando se hace click en checkbox
     */
    it("emite evento toggle cuando se hace click en checkbox", async () => {
        const subtarea = {
            id: 3,
            texto: "Leer libro",
            estado: "pendiente",
        };

        const wrapper = mount(SubtareaItem, {
            props: { subtarea },
        });

        // Primer botón es el checkbox
        const checkbox = wrapper.findAll("button")[0];
        await checkbox.trigger("click");

        expect(wrapper.emitted("toggle")).toBeTruthy();
        expect(wrapper.emitted("toggle")[0]).toEqual([3]);
    });

    /**
     * Test 4: Muestra input al hacer click en el texto (solo si pendiente)
     */
    it("muestra input al hacer click en el texto para editar", async () => {
        const subtarea = {
            id: 4,
            texto: "Estudiar JavaScript",
            estado: "pendiente",
        };

        const wrapper = mount(SubtareaItem, {
            props: { subtarea },
        });

        // Buscar el botón con el texto
        const textoButton = wrapper
            .findAll("button")
            .find((btn) => btn.text().includes("Estudiar JavaScript"));
        expect(textoButton).toBeDefined();

        await textoButton.trigger("click");

        // Debe aparecer input
        const input = wrapper.find('input[type="text"]');
        expect(input.exists()).toBe(true);
        expect(input.element.value).toBe("Estudiar JavaScript");
    });

    /**
     * Test 5: No permite editar subtarea completada
     */
    it("no permite editar subtarea completada", async () => {
        const subtarea = {
            id: 5,
            texto: "Tarea completada",
            estado: "completada",
        };

        const wrapper = mount(SubtareaItem, {
            props: { subtarea },
        });

        const textoButton = wrapper
            .findAll("button")
            .find((btn) => btn.text().includes("Tarea completada"));
        await textoButton.trigger("click");

        // NO debe aparecer input
        const input = wrapper.find('input[type="text"]');
        expect(input.exists()).toBe(false);
    });

    /**
     * Test 6: Emite evento update al guardar edición
     */
    it("emite evento update al guardar edición con Enter", async () => {
        const subtarea = {
            id: 6,
            texto: "Texto original",
            estado: "pendiente",
        };

        const wrapper = mount(SubtareaItem, {
            props: { subtarea },
        });

        // Activar edición
        const textoButton = wrapper
            .findAll("button")
            .find((btn) => btn.text().includes("Texto original"));
        await textoButton.trigger("click");

        // Modificar texto
        const input = wrapper.find('input[type="text"]');
        await input.setValue("Texto modificado");

        // Simular Enter
        await input.trigger("keydown.enter");

        expect(wrapper.emitted("update")).toBeTruthy();
        expect(wrapper.emitted("update")[0]).toEqual([6, "Texto modificado"]);
    });

    /**
     * Test 7: Cancela edición con Escape sin emitir evento
     */
    it("cancela edición con Escape sin emitir evento", async () => {
        const subtarea = {
            id: 7,
            texto: "No modificar",
            estado: "pendiente",
        };

        const wrapper = mount(SubtareaItem, {
            props: { subtarea },
        });

        // Activar edición
        const textoButton = wrapper
            .findAll("button")
            .find((btn) => btn.text().includes("No modificar"));
        await textoButton.trigger("click");

        // Modificar texto
        const input = wrapper.find('input[type="text"]');
        await input.setValue("Texto temporal");

        // Presionar Escape
        await input.trigger("keydown.esc");

        // NO debe emitir update
        expect(wrapper.emitted("update")).toBeFalsy();

        // Input debe desaparecer
        expect(wrapper.find('input[type="text"]').exists()).toBe(false);
    });

    /**
     * Test 8: No emite update si el texto no cambió
     */
    it("no emite update si el texto no cambió", async () => {
        const subtarea = {
            id: 8,
            texto: "Sin cambios",
            estado: "pendiente",
        };

        const wrapper = mount(SubtareaItem, {
            props: { subtarea },
        });

        // Activar edición
        const textoButton = wrapper
            .findAll("button")
            .find((btn) => btn.text().includes("Sin cambios"));
        await textoButton.trigger("click");

        const input = wrapper.find('input[type="text"]');
        // No cambiar valor
        await input.trigger("keydown.enter");

        // NO debe emitir update
        expect(wrapper.emitted("update")).toBeFalsy();
    });

    /**
     * Test 9: No emite update si el texto está vacío
     */
    it("no emite update si el texto está vacío", async () => {
        const subtarea = {
            id: 9,
            texto: "Texto válido",
            estado: "pendiente",
        };

        const wrapper = mount(SubtareaItem, {
            props: { subtarea },
        });

        // Activar edición
        const textoButton = wrapper
            .findAll("button")
            .find((btn) => btn.text().includes("Texto válido"));
        await textoButton.trigger("click");

        const input = wrapper.find('input[type="text"]');
        await input.setValue("   "); // Solo espacios

        await input.trigger("keydown.enter");

        // NO debe emitir update
        expect(wrapper.emitted("update")).toBeFalsy();
    });

    /**
     * Test 10: Emite evento delete cuando se hace click en botón eliminar
     */
    it("emite evento delete cuando se hace click en botón eliminar", async () => {
        const subtarea = {
            id: 10,
            texto: "Para eliminar",
            estado: "pendiente",
        };

        const wrapper = mount(SubtareaItem, {
            props: { subtarea },
        });

        // Último botón es el de eliminar (X)
        const buttons = wrapper.findAll("button");
        const deleteButton = buttons[buttons.length - 1];

        await deleteButton.trigger("click");

        expect(wrapper.emitted("delete")).toBeTruthy();
        expect(wrapper.emitted("delete")[0]).toEqual([10]);
    });

    /**
     * Test 11: Muestra checkmark cuando subtarea está completada
     */
    it("muestra checkmark cuando subtarea está completada", () => {
        const subtarea = {
            id: 11,
            texto: "Completada con check",
            estado: "completada",
        };

        const wrapper = mount(SubtareaItem, {
            props: { subtarea },
        });

        // Debe haber un SVG dentro del primer botón (checkbox)
        const checkbox = wrapper.findAll("button")[0];
        const svg = checkbox.find("svg");
        expect(svg.exists()).toBe(true);
    });

    /**
     * Test 12: No muestra checkmark cuando subtarea está pendiente
     */
    it("no muestra checkmark cuando subtarea está pendiente", () => {
        const subtarea = {
            id: 12,
            texto: "Pendiente sin check",
            estado: "pendiente",
        };

        const wrapper = mount(SubtareaItem, {
            props: { subtarea },
        });

        // No debe haber SVG dentro del primer botón
        const checkbox = wrapper.findAll("button")[0];
        const svg = checkbox.find("svg");
        expect(svg.exists()).toBe(false);
    });
});
