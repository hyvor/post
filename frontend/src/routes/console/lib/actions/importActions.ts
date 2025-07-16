import consoleApi from "../consoleApi";

export function uploadCsv(file: File | Blob) {
    const formData = new FormData();
    formData.append('file', file);

    return consoleApi.post<string[]>({
        endpoint: "subscribers/import/upload",
        data: formData,
    })
}
