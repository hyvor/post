import type { Invite, User } from "../../types";
import consoleApi from "../consoleApi";


export function getProjectUsers() {
	return consoleApi.get<User[]>({
		endpoint: 'users'
	});
}

export function getProjectInvites() {
    return consoleApi.get<Invite[]>({
        endpoint: 'invites'
    });
}

interface InviteUserInput {
	email?: string;
	username?: string;
	role: string;
}
export function inviteUser(data: InviteUserInput) {
	return consoleApi.post<Invite>({
		endpoint: 'invites',
		data
	});
}

export function deleteInvite(id: number) {
    return consoleApi.delete({
        endpoint: `invites/${id}`
    });
}
