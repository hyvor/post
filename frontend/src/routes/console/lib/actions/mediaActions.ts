import type { Media } from '../../types';
import consoleApi from '../consoleApi';

export function uploadImage(file: File | Blob, folder: string) {
	const formData = new FormData();
	formData.append('file', file);
	formData.append('folder', folder);

	return consoleApi.post<Media>({
		endpoint: '/media',
		data: formData
	});
}
