import consoleApi from "../consoleApi";
import type { Export } from "../../types";

export function createExport() {
  return consoleApi.post<Export>({
    endpoint: "export",
  });
}

export function listExports() {
  return consoleApi.get<Export[]>({
    endpoint: "export",
  });
}
