import consoleApi from "../consoleApi";
import type { Project } from "../../types";

export function createProject(name: string) {
    return consoleApi.post<Project>({
        endpoint: 'projects',
        userApi: true,
        data: {
            name,
        }
    });
}

export function updateProject(
    project: Omit<Project, 'id' | 'created_at'>
) {
    return consoleApi.patch<Project>({
        endpoint: 'projects',
        data: project,
    });
}

export function deleteProject(project: Project) {
    return consoleApi.delete<Project>({
        endpoint: 'projects'
    });
}
