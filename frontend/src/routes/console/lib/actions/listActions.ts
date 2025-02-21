import consoleApi from "../consoleApi";
import type { List } from "../../types";
import { projectStore } from "../stores/projectStore";
import { get } from "svelte/store";

export function createList(name: string) {
    return consoleApi.post<List>({
        endpoint: 'lists',
        userApi: true,
        data: {
            name
        },
        projectId: get(projectStore).id.toString()
    });
}
