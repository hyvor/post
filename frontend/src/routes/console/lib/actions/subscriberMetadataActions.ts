import type { SubscriberMetadataDefinition } from "../../types";
import consoleApi from "../consoleApi";

export function createSubscriberMetadataDefinition(
  key: string,
  name: string,
): Promise<SubscriberMetadataDefinition> {
  return consoleApi.post({
    endpoint: "/subscriber-metadata-definitions",
    data: {
      key,
      name,
    },
  });
}

export function updateSubscriberMetadataDefinition(
  id: number,
  name: string,
): Promise<SubscriberMetadataDefinition> {
  return consoleApi.patch({
    endpoint: `/subscriber-metadata-definitions/${id}`,
    data: {
      name,
    },
  });
}

export function deleteSubscriberMetadataDefinition(id: number): Promise<void> {
  return consoleApi.delete({
    endpoint: `/subscriber-metadata-definitions/${id}`,
  });
}
