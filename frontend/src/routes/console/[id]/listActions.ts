import consoleApi from "../../../lib/consoleApi";
import type { List } from "../types";

export function createList(name: string, project_id: number) {
    return consoleApi.post<List>({
        endpoint: '/lists',
        data: {
            name,
            project_id
        }
    });
}
