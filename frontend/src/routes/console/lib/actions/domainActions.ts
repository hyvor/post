import consoleApi from "../consoleApi";
import type { Domain } from "../../types";

type VerifyDomainResponse = {
    domain: Domain;
    data: {
        debug: Record<string, string>;
    }
};

export function createDomain(domain: string) {
    return consoleApi.post<Domain>({
        endpoint: 'domains',
        data: {
            domain,
        },
    });
}

export function getDomains() {
    return consoleApi.get<Domain[]>({
        endpoint: 'domains',
    });
}

export function deleteDomain(id: number) {
    return consoleApi.delete({
        endpoint: `domains/${id}`,
    });
}

export function verifyDomain(id: number) {
    return consoleApi.post<VerifyDomainResponse>({
        endpoint: `domains/verify/${id}`,
    });
} 
