import consoleApi from "../../../../console/lib/consoleApi";
import type { TemplateResponse } from "../../types";

export function getDefaultTemplate() {
    return consoleApi.get<TemplateResponse>({
        endpoint: 'template/default',
        publicApi: true,
    });
}
