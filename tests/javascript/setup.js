import { vi } from "vitest";

// Mock de Inertia
global.route = vi.fn((name, params) => {
    return `/${name}${
        params ? `?${new URLSearchParams(params).toString()}` : ""
    }`;
});

// Mock de window.location
delete window.location;
window.location = { href: "/" };

// Mock de localStorage
const localStorageMock = {
    getItem: vi.fn(),
    setItem: vi.fn(),
    removeItem: vi.fn(),
    clear: vi.fn(),
};
global.localStorage = localStorageMock;

// Mock de sessionStorage
const sessionStorageMock = {
    getItem: vi.fn(),
    setItem: vi.fn(),
    removeItem: vi.fn(),
    clear: vi.fn(),
};
global.sessionStorage = sessionStorageMock;
