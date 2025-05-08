import type { User } from "../../types";
import consoleApi from "../consoleApi";


export function getProjectUsers() {
	return consoleApi.get<User[]>({
		endpoint: 'users'
	});
}