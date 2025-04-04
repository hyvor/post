import consoleApi from "../../../../console/lib/consoleApi";
import type { TemplateResponse } from "../../types";

export function getDefaultTemplate() {
    return consoleApi.get<TemplateResponse>({
        endpoint: 'template/default',
        publicApi: true,
    });
}

export function previewTemplateFromVariable (template: string, variables: string) {
    return consoleApi.post<string>({
        endpoint: 'template/with',
        publicApi: true,
        data: {
           template,
            variables,
        }
    });
}
