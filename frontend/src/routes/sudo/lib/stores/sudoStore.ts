import { writable } from "svelte/store";
import type {
  Approval,
  SubscriberImport,
  SudoConfig,
  SudoStats,
} from "../../types";

export const configStore = writable<SudoConfig>();
export const statsStore = writable<SudoStats>({
  reviewing_approvals: 0,
  pending_imports: 0,
});
export const approvalStore = writable<Approval[]>([]);
export const subscriberImportStore = writable<SubscriberImport[]>([]);

approvalStore.subscribe((approvals) => {
  statsStore.update((stats) => ({
    ...stats,
    pending_approvals: approvals.filter((a) => a.status === "reviewing").length,
  }));
});

subscriberImportStore.subscribe((imports) => {
  statsStore.update((stats) => ({
    ...stats,
    pending_imports: imports.filter((i) => i.status === "pending_approval")
      .length,
  }));
});
