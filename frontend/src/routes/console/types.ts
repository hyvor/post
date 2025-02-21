export type Project = {
	id: number,
	created_at: number,
	name: string,
}

export interface ProjectStats {
	subscribers: { total: number; last_30d: number };
	issues: { total: number; last_30d: number };
	lists: { total: number; last_30d: number };
}

export type List = {
	id: number,
	created_at: number,
	name: string,
}
