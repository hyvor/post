import consoleApi from "../consoleApi";
import type { Project, ProjectMeta } from "../../types";

export function createProject(name: string) {
    return consoleApi.post<Project>({
        endpoint: 'projects',
        userApi: true,
        data: {
            name,
        }
    });
}

export function updateProjectMeta(meta: ProjectMeta) {
    return consoleApi.patch<ProjectMeta>({
        endpoint: 'projects',
        data: meta,
    });
}
