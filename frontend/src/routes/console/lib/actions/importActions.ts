import consoleApi from "../consoleApi";
import type {Import, ImportField} from "../../types";

export const IMPORTS_PER_PAGE = 30;
export function uploadCsv(file: File | Blob) {
    const formData = new FormData();
    formData.append('file', file);

    return consoleApi.post<ImportField>({
        endpoint: "imports/upload",
        data: formData,
    })
}

export function subscriberImport(importId: number, mapping: Record<string, string | null>) {
    return consoleApi.post<Import>({
        endpoint: `imports/${importId}`,
        data: { mapping },
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
