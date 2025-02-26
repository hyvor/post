import type { Subscriber } from "../../types";
import consoleApi from "../consoleApi";

export function createSubscriber(email: string, list_ids: number[]) {
    return consoleApi.post<Subscriber>({
        endpoint: 'subscribers',
        data: {
            email,
            list_ids,
        },
    });
}
