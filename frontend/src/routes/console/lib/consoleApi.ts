import { get } from "svelte/store";
import { projectStore } from "./stores/projectStore";

export interface ConsoleApiOptions {
    endpoint: string,
    data?: Record<string, any> | FormData,
    userApi?: boolean,
    projectId?: string,
    signal?: AbortSignal,
}

interface CallOptions extends ConsoleApiOptions {
    method: 'get' | 'post' | 'patch' | 'delete' | 'put'
}

function getConsoleApi() {

    const baseUrl = "/api/console/";

    async function call<T>({ 
        endpoint,
        method,
        userApi = false,
        data = {},
        projectId,
        signal
    }: CallOptions) : Promise<T> {

        const url = baseUrl + endpoint;

        const headers = {} as Record<string, string>;

        if (!userApi) {
            const project = get(projectStore);
            headers['X-Project-Id'] = project.id.toString();
        }
        else if (projectId) {
            headers['X-Project-Id'] = projectId;
        }

        if (!(data instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
        }   

        const options = {
            cache: 'no-cache',
            credentials: 'same-origin',
            method: method.toUpperCase(),
            headers,
            signal
        } as RequestInit;

        if (method !== 'get') {
            options.body = data instanceof FormData ? data : JSON.stringify(data);
        }

        const response = await fetch(url, options)

        if (!response.ok) {
            const e = await response.json();
            const error = e && e.error ? e.error : 'Something went wrong';
            /* toast({type: 'error', message: error});
            throw error; */

            const toThrow = new Error(error) as any;
            toThrow.message = error;
            toThrow.code = e && e.code ? e.code : 500;

            throw toThrow;
        }

        const json = await response.json();
        return json as T;

    }

    return {
        call,
        get: async <T>(opt: ConsoleApiOptions) => call<T>({...opt, method: 'get'}),
        post: async <T>(opt: ConsoleApiOptions) => call<T>({...opt, method: 'post'}),
        patch: async <T>(opt: ConsoleApiOptions) => call<T>({...opt, method: 'patch'}),
        put: async <T>(opt: ConsoleApiOptions) => call<T>({...opt, method: 'put'}),
        delete: async <T>(opt: ConsoleApiOptions) => call<T>({...opt, method: 'delete'}),
    }

}

const consoleApi = getConsoleApi();
export default consoleApi;
