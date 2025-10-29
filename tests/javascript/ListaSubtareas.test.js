import { describe, it, expect, vi, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import ListaSubtareas from "@/Components/Subtareas/ListaSubtareas.vue";
import SubtareaItem from "@/Components/Subtareas/SubtareaItem.vue";

// Mock del composable usarSubtareas
const mockCrearSubtarea = vi.fn();
const mockActualizarSubtarea = vi.fn();
const mockEliminarSubtarea = vi.fn();
const mockToggleEstado = vi.fn();

vi.mock("@/composables/usarSubtareas", () => ({
    usarSubtareas: () => ({
        crearSubtarea: mockCrearSubtarea,
        actualizarSubtarea: mockActualizarSubtarea,
        eliminarSubtarea: mockEliminarSubtarea,
        toggleEstado: mockToggleEstado,
    }),
}));

// Mock de window.alert
global.alert = vi.fn();

// Mock de window.confirm
global.confirm = vi.fn();

describe("ListaSubtareas.vue", () => {
    beforeEach(() => {
        // Limpiar mocks antes de cada test
        vi.clearAllMocks();
    });

    /**
     * Test 1: Renderiza correctamente con lista vacía
     */
    it("renderiza correctamente con lista vacía", () => {
        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 1,
                subtareas: [],
            },
        });

        expect(wrapper.text()).toContain("Subtareas");
        expect(wrapper.text()).toContain("Sin subtareas. Agrega una abajo.");
        expect(wrapper.text()).toContain("0/0 · Límite 0/30");
    });

    /**
     * Test 2: Renderiza lista de subtareas correctamente
     */
    it("renderiza lista de subtareas correctamente", () => {
        const subtareas = [
            { id: 1, texto: "Subtarea 1", estado: "pendiente" },
            { id: 2, texto: "Subtarea 2", estado: "completada" },
            { id: 3, texto: "Subtarea 3", estado: "pendiente" },
        ];

        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 1,
                subtareas,
            },
        });

        // Debe renderizar 3 SubtareaItem
        const items = wrapper.findAllComponents(SubtareaItem);
        expect(items).toHaveLength(3);

        // Verificar contador
        expect(wrapper.text()).toContain("1/3 · Límite 3/30");
    });

    /**
     * Test 3: Muestra contador correcto de completadas
     */
    it("muestra contador correcto de completadas", () => {
        const subtareas = [
            { id: 1, texto: "Tarea 1", estado: "completada" },
            { id: 2, texto: "Tarea 2", estado: "completada" },
            { id: 3, texto: "Tarea 3", estado: "pendiente" },
            { id: 4, texto: "Tarea 4", estado: "pendiente" },
        ];

        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 1,
                subtareas,
            },
        });

        expect(wrapper.text()).toContain("2/4 · Límite 4/30");
    });

    /**
     * Test 4: Permite agregar nueva subtarea
     */
    it("permite agregar nueva subtarea", async () => {
        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 1,
                subtareas: [],
            },
        });

        const input = wrapper.find('input[type="text"]');
        await input.setValue("Nueva subtarea");

        const addButton = wrapper.findAll("button").find((btn) => {
            const svg = btn.find("svg");
            return svg.exists();
        });
        expect(addButton).toBeDefined();

        await addButton.trigger("click");

        expect(mockCrearSubtarea).toHaveBeenCalledWith(1, "Nueva subtarea");
    });

    /**
     * Test 5: Permite agregar subtarea con Enter
     */
    it("permite agregar subtarea con Enter", async () => {
        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 2,
                subtareas: [],
            },
        });

        const input = wrapper.find('input[type="text"]');
        await input.setValue("Subtarea con Enter");

        // Simular evento keydown con key: 'Enter'
        await input.trigger("keydown", { key: "Enter" });

        expect(mockCrearSubtarea).toHaveBeenCalledWith(2, "Subtarea con Enter");
    });

    /**
     * Test 6: No permite agregar subtarea vacía
     */
    it("no permite agregar subtarea vacía", async () => {
        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 1,
                subtareas: [],
            },
        });

        const input = wrapper.find('input[type="text"]');
        await input.setValue("   "); // Solo espacios

        const addButton = wrapper.findAll("button").find((btn) => {
            const svg = btn.find("svg");
            return svg.exists();
        });
        await addButton.trigger("click");

        expect(mockCrearSubtarea).not.toHaveBeenCalled();
    });

    /**
     * Test 7: Deshabilita input cuando límite alcanzado
     */
    it("deshabilita input cuando límite de 30 alcanzado", () => {
        const subtareas = Array.from({ length: 30 }, (_, i) => ({
            id: i + 1,
            texto: `Subtarea ${i + 1}`,
            estado: "pendiente",
        }));

        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 1,
                subtareas,
            },
        });

        const input = wrapper.find('input[type="text"]');
        expect(input.attributes("disabled")).toBeDefined();

        // Debe mostrar advertencia
        expect(wrapper.text()).toContain("Límite de 30 subtareas alcanzado");
        expect(wrapper.text()).toContain("⚠️");
    });

    /**
     * Test 8: Muestra alerta al intentar agregar subtarea cuando límite alcanzado
     */
    it("muestra alerta al intentar agregar cuando límite alcanzado", async () => {
        const subtareas = Array.from({ length: 30 }, (_, i) => ({
            id: i + 1,
            texto: `Subtarea ${i + 1}`,
            estado: "pendiente",
        }));

        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 1,
                subtareas,
            },
        });

        // Forzar valor en input (aunque esté disabled)
        wrapper.vm.nuevoTexto = "Texto";
        await wrapper.vm.handleCrear();

        expect(global.alert).toHaveBeenCalledWith(
            "Máximo 30 subtareas por tarea"
        );
        expect(mockCrearSubtarea).not.toHaveBeenCalled();
    });

    /**
     * Test 9: Limpia input después de agregar subtarea
     */
    it("limpia input después de agregar subtarea", async () => {
        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 1,
                subtareas: [],
            },
        });

        const input = wrapper.find('input[type="text"]');
        await input.setValue("Nueva subtarea");

        const addButton = wrapper.findAll("button").find((btn) => {
            const svg = btn.find("svg");
            return svg.exists();
        });
        await addButton.trigger("click");

        // Input debe estar vacío
        expect(wrapper.vm.nuevoTexto).toBe("");
    });

    /**
     * Test 10: Maneja evento toggle de subtarea
     */
    it("maneja evento toggle de subtarea", async () => {
        const subtareas = [
            { id: 5, texto: "Para toggle", estado: "pendiente" },
        ];

        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 3,
                subtareas,
            },
        });

        const subtareaItem = wrapper.findComponent(SubtareaItem);
        await subtareaItem.vm.$emit("toggle");

        expect(mockToggleEstado).toHaveBeenCalledWith(3, 5);
    });

    /**
     * Test 11: Maneja evento update de subtarea
     */
    it("maneja evento update de subtarea", async () => {
        const subtareas = [
            { id: 6, texto: "Para actualizar", estado: "pendiente" },
        ];

        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 4,
                subtareas,
            },
        });

        const subtareaItem = wrapper.findComponent(SubtareaItem);
        await subtareaItem.vm.$emit("update", "Texto actualizado");

        expect(mockActualizarSubtarea).toHaveBeenCalledWith(
            4,
            6,
            "Texto actualizado"
        );
    });

    /**
     * Test 12: Maneja evento delete de subtarea
     */
    it("maneja evento delete de subtarea", async () => {
        const subtareas = [
            { id: 7, texto: "Para eliminar", estado: "pendiente" },
        ];

        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 5,
                subtareas,
            },
        });

        const subtareaItem = wrapper.findComponent(SubtareaItem);
        await subtareaItem.vm.$emit("delete");

        expect(mockEliminarSubtarea).toHaveBeenCalledWith(5, 7);
    });

    /**
     * Test 13: Muestra scroll cuando hay más de 5 subtareas
     */
    it("aplica max-height cuando hay muchas subtareas", () => {
        const subtareas = Array.from({ length: 10 }, (_, i) => ({
            id: i + 1,
            texto: `Subtarea ${i + 1}`,
            estado: "pendiente",
        }));

        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 1,
                subtareas,
            },
        });

        // Debe haber un contenedor con max-h-[180px]
        const container = wrapper.find(".max-h-\\[180px\\]");
        expect(container.exists()).toBe(true);
    });

    /**
     * Test 14: Resalta límite cuando se acerca a 30
     */
    it("resalta contador cuando límite alcanzado", () => {
        const subtareas = Array.from({ length: 30 }, (_, i) => ({
            id: i + 1,
            texto: `Subtarea ${i + 1}`,
            estado: "pendiente",
        }));

        const wrapper = mount(ListaSubtareas, {
            props: {
                tareaId: 1,
                subtareas,
            },
        });

        // El contador debe tener clase text-amber-600
        const contador = wrapper.find(".text-amber-600");
        expect(contador.exists()).toBe(true);
        expect(contador.text()).toContain("0/30 · Límite 30/30");
    });
});
