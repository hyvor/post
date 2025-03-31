import consoleApi from "../consoleApi";
import type { List } from "../../types";
import { projectStore } from "../stores/projectStore";
import { get } from "svelte/store";

export function createList(name: string, description: string|null) {
    return consoleApi.post<List>({
        endpoint: 'lists',
        data: {
            name,
            description
        },
    });
}

export function updateList(id: number, name: string, description: string|null) {
    return consoleApi.patch<List>({
        endpoint: `lists/${id}`,
        data: {
            name,
            description
        },
    });
}
