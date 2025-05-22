import consoleApi from "../consoleApi";
import type { Newsletter } from "../../types";

export function createProject(name: string) {
    return consoleApi.post<Newsletter>({
        endpoint: 'newsletter',
        userApi: true,
        data: {
            name,
        }
    });
}

export function updateProject(
    project: Omit<Newsletter, 'id' | 'created_at'>
) {
    return consoleApi.patch<Newsletter>({
        endpoint: 'newsletter',
        data: project,
    });
}

export function deleteProject(project: Newsletter) {
    return consoleApi.delete<Newsletter>({
        endpoint: 'newsletter'
    });
}
