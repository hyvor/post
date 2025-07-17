import consoleApi from "../consoleApi";
import type { ImportField } from "../../types";

export function uploadCsv(file: File | Blob) {
    const formData = new FormData();
    formData.append('file', file);

    return consoleApi.post<ImportField>({
        endpoint: "import/upload",
        data: formData,
    })
}

export function subscriberImport(importId: number, mapping: Record<string, string | null>) {
    return consoleApi.post<void>({
        endpoint: `import/${importId}`,
        data: { mapping },
    });
}
