import type { User } from "../../types";
import consoleApi from "../consoleApi";

export function getNewsletterUsers() {
  return consoleApi.get<User[]>({
    endpoint: "users",
  });
}

interface AddUserInput {
  userId: number;
  role: string;
}

export function addUser(data: AddUserInput) {
  return consoleApi.post<User>({
    endpoint: "invites",
    data,
  });
}

export function deleteUser(id: number) {
  return consoleApi.delete({
    endpoint: `users/${id}`,
  });
}
