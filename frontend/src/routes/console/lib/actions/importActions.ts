import consoleApi from "../consoleApi";
import type {Import, ImportLimits} from "../../types";

export const IMPORTS_PER_PAGE = 30;

export function uploadCsv(file: File | Blob, source: string) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('source', source)

    return consoleApi.post<Import>({
        endpoint: "imports/upload",
        data: formData,
    })
}

export function subscriberImport(importId: number, mapping: Record<string, string | null>) {
    return consoleApi.post<Import>({
        endpoint: `imports/${importId}`,
        data: {mapping},
    });
}

export function getImports(offset: number = 0) {
    return consoleApi.get<Import[]>({
        endpoint: "imports",
        data: {
            limit: IMPORTS_PER_PAGE,
            offset
        }
    });
}

export function getImportLimits() {
    return consoleApi.get<ImportLimits>({
        endpoint: "imports/limits",
    });
}
