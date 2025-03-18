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