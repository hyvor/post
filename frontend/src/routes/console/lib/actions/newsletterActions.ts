import consoleApi from "../consoleApi";
import type {Newsletter} from "../../types";

export function getSubdomainAvailability(subdomain: string) {
    return consoleApi.get<{ available: boolean }>({
        endpoint: 'newsletter/subdomain',
        data: {subdomain},
        userApi: true
    })
}

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

export function deleteNewsletter() {
    return consoleApi.delete<Newsletter>({
        endpoint: 'newsletter'
    });
}
