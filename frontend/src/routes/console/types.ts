export interface Stats {
	subscribers: { total: number; last_30d: number };
	issues: { total: number; last_30d: number };
	lists: { total: number; last_30d: number };
}