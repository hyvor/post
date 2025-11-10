import type { Media } from '../../types';
import consoleApi from '../consoleApi';

type MediaFolder = 'issue_images' | 'newsletter_images';

export function uploadImage(file: File | Blob, folder: MediaFolder) {
	const formData = new FormData();
	formData.append('file', file);
	formData.append('folder', folder);

	return consoleApi.post<Media>({
		endpoint: '/media',
		data: formData
	});
}
