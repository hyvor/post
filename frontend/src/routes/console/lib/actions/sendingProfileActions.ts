import consoleApi from "../consoleApi";
import type { SendingProfile } from "../../types";

export function getSendingProfilees() {
    return consoleApi.get<SendingProfile[]>({
        endpoint: 'sending-profiles',
    });
}

export function createSendingProfile(
    from_email: string,
    from_name?: string | null,
    reply_to_email?: string | null,
    brand_name?: string | null,
    brand_logo?: string | null
): Promise<SendingProfile> {
    return consoleApi.post<SendingProfile>({
        endpoint: 'sending-profiles',
        data: {
            from_email,
            from_name,
            reply_to_email,
            brand_name,
            brand_logo
        }
    });
}

export interface UpdateSendingProfileData {
    from_email?: string;
    from_name?: string;
    reply_to_email?: string;
    brand_name?: string;
    brand_logo?: string;
    is_default?: boolean;
}
export function updateSendingProfile(
    id: number,
    params: UpdateSendingProfileData
): Promise<SendingProfile> {
    return consoleApi.patch<SendingProfile>({
        endpoint: `sending-profiles/${id}`,
        data: params,
    });
}

export function deleteSendingProfile(id: number) {
    return consoleApi.delete<SendingProfile[]>({
        endpoint: `sending-profiles/${id}`,
    });
}
