import consoleApi from "../consoleApi";
import type { Export } from "../../types";

export function createExport() {
    return consoleApi.post<Export>({
        endpoint: 'subscribers/export'
    });
}

export function listExports() {
    return consoleApi.get<Export[]>({
        endpoint: 'subscribers/export'
    });
} 