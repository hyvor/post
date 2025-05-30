import consoleApi from "../consoleApi";
import type { SendingProfile } from "../../types";

export function getSendingProfilees() {
    return consoleApi.get<SendingProfile[]>({
        endpoint: 'sending-profiles',
    });
}

export function createSendingProfile(email: string) {
    return consoleApi.post<SendingProfile>({
        endpoint: 'sending-profiles',
        data: {
            email,
        },
    });
}

export function updateSendingProfile(id: number, email: string, is_default?: boolean) {
    return consoleApi.patch<SendingProfile>({
        endpoint: `sending-profiles/${id}`,
        data: {
            email,
            is_default,
        },
    });
}

export function deleteSendingProfile(id: number) {
    return consoleApi.delete<SendingProfile[]>({
        endpoint: `sending-profiles/${id}`,
    });
}
