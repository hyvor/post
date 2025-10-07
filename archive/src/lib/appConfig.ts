interface AppConfig {
	app_url: string;
}

export function getAppConfig() {
	return (window as any).APP_CONFIG || ({} as AppConfig);
}
