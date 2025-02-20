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
