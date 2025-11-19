import consoleApi from '../consoleApi';
import type { SendingProfile } from '../../types';

export function getSendingProfilees() {
	return consoleApi.get<SendingProfile[]>({
		endpoint: 'sending-profiles'
	});
}

export interface createSendingProfileParams {
	from_email: string;
	from_name: string | null;
	reply_to_email: string | null;
	brand_name: string | null;
	brand_logo: string | null;
}
export function createSendingProfile(params: createSendingProfileParams): Promise<SendingProfile> {
	return consoleApi.post<SendingProfile>({
		endpoint: 'sending-profiles',
		data: params
	});
}

export interface UpdateSendingProfileParams {
	from_email?: string;
	from_name?: string;
	reply_to_email?: string;
	brand_name?: string;
	brand_logo?: string;
	is_default?: boolean;
}
export function updateSendingProfile(
	id: number,
	params: UpdateSendingProfileParams
): Promise<SendingProfile> {
	return consoleApi.patch<SendingProfile>({
		endpoint: `sending-profiles/${id}`,
		data: params
	});
}

export function deleteSendingProfile(id: number) {
	return consoleApi.delete<SendingProfile[]>({
		endpoint: `sending-profiles/${id}`
	});
}
