import type { Issue } from "../../types";
import consoleApi from "../consoleApi";

export function createIssueDraft() {
    return consoleApi.post<Issue>({
        endpoint: 'issues',
    });
}

export function deleteIssue(id: number) {
	return consoleApi.delete({
		endpoint: 'issues/' + id
	});
}

export function getIssue(id: number) {
    return consoleApi.get<Issue>({
        endpoint: 'issues/' + id
    });
}

type IssueUpdate = {
	subject: string;
	from_name: string;
	from_email: string;
	reply_to_email: string;
	lists: number[];
	content: string;
};

export function updateIssue(id: number, updates: IssueUpdate) {
	return consoleApi.patch<Issue>({
		endpoint: 'issues/' + id,
		data: updates
	});
}

export function sendIssue(id: number) {
	return consoleApi.post<Issue>({
		endpoint: `issues/${id}/send`
	});
}

export function previewIssue(id: number) {
	return consoleApi.get<{ html: string }>({
		endpoint: `issues/${id}/preview`
	});
}

export function sendIssueTest(id: number, email: string) {
	return consoleApi.post({
		endpoint: `issues/${id}/test`,
		data: {
			email
		}
	});
}