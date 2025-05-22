import { get } from "svelte/store";
import { projectStore } from "./stores/newsletterStore";


export function consoleUrl(path: string) {

    path = path.replace(/^\//, '');

    return '/console/' 
        + path;
}


export function consoleUrlWithProject(path: string) {
    const projectId = get(projectStore).id;
    path = path.replace(/^\//, '');
    return consoleUrl(`${projectId}/${path}`)
}