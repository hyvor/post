import consoleApi from "../consoleApi";
import type { SendingAddress } from "../../types";

export function getSendingAddresses() {
    return consoleApi.get<SendingAddress[]>({
        endpoint: 'sending-addresses',
    });
}

export function createSendingAddress(email: string) {
    return consoleApi.post<SendingAddress>({
        endpoint: 'sending-addresses',
        data: {
            email,
        },
    });
}

export function updateSendingAddress(id: number, email: string) {
    return consoleApi.patch<SendingAddress>({
        endpoint: `sending-addresses/${id}`,
        data: {
            email,
        },
    });
}

export function deleteSendingAddress(id: number) {
    return consoleApi.delete({
        endpoint: `sending-addresses/${id}`,
    });
}
