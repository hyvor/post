import consoleApi from "../consoleApi";
import type { SendingEmail } from "../../types";

export function getSendingEmails() {
    return consoleApi.get<SendingEmail[]>({
        endpoint: 'sending-emails',
    });
}

export function createSendingEmail(email: string) {
    return consoleApi.post<SendingEmail>({
        endpoint: 'sending-emails',
        data: {
            email,
        },
    });
}

export function updateSendingEmail(id: number, email: string) {
    return consoleApi.patch<SendingEmail>({
        endpoint: `sending-emails/${id}`,
        data: {
            email,
        },
    });
}

export function deleteSendingEmail(id: number) {
    return consoleApi.delete({
        endpoint: `sending-emails/${id}`,
    });
}
