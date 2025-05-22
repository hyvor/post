import consoleApi from "../consoleApi";
import type { Newsletter } from "../../types";

export function createNewsletter(name: string) {
    return consoleApi.post<Newsletter>({
        endpoint: 'newsletter',
        userApi: true,
        data: {
            name,
        }
    });
}

export function updateNewsletter(
    newsletter: Omit<Newsletter, 'id' | 'created_at'>
) {
    return consoleApi.patch<Newsletter>({
        endpoint: 'newsletter',
        data: newsletter,
    });
}

export function deleteNewsletter(newsletter: Newsletter) {
    return consoleApi.delete<Newsletter>({
        endpoint: 'newsletter'
    });
}
