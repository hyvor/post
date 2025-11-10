<script lang="ts">
	import { Button, Modal, Loader, Avatar, toast, Text } from '@hyvor/design/components';
	import { uploadImage } from '../../lib/actions/mediaActions';

	interface Props {
		url?: string | null;
		title?: string;
		preview_size?: number | 'small' | 'medium' | 'large';
		square_avatar?: boolean;
        change?: (url: string | null) => void;
	}

	let { 
		url = $bindable(null),
		title = 'Upload Image',
		preview_size = 'large',
		square_avatar = false,
        change = () => {}  
	}: Props = $props();

	let showModal = $state(false);
	let isUploading = $state(false);
	let fileInput: HTMLInputElement | undefined = $state();
	let previewImage: string | null = $state(null);
	let image: File | null = $state(null);

	const MAX_FILE_SIZE_MB = 2;
	const ALLOWED_FILE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/svg', 'image/jpg'];

	const onFileSelected = (e: Event) => {
		const target = e.target as HTMLInputElement;
		const file = target.files?.[0] || null;

		if (file) {
			if (file.size / 1024 / 1024 > MAX_FILE_SIZE_MB) {
				toast.error(`File size exceeds ${MAX_FILE_SIZE_MB}MB limit`);
				return;
			}

			if (!ALLOWED_FILE_TYPES.includes(file.type)) {
				toast.error('Invalid file type. Please upload an image file (JPEG, PNG, or GIF)');
				return;
			}

			image = file;
			previewImage = URL.createObjectURL(file);
		}
	};

	const uploadImageHandler = async () => {
		if (image) {
			isUploading = true;
			try {
				const media = await uploadImage(image, 'newsletter_images');
				const uploadedImageUrl = media.url;
				toast.success('Image uploaded successfully');
				url = uploadedImageUrl; // Updating the url with the uploaded image URL
				change(uploadedImageUrl);
				showModal = false;
				previewImage = null;
			} catch (error) {
				console.error('Error uploading image:', error);
				toast.error('Failed to upload image');
			} finally {
				isUploading = false;
			}
		}
	};

	const resetImage = () => {
		image = null;
		url = null;
		previewImage = null;
		showModal = false;
		change(url);
		toast.info('Image removed');
	};
</script>

{#if !url}
	<Button on:click={() => (showModal = true)}>{title}</Button>
{/if}
{#if url}
	<button class:square-avatar={square_avatar} onclick={() => (showModal = true)}><Avatar src={url} size={preview_size} /></button>
{/if}

<Modal title={title} bind:show={showModal} loading={isUploading} closeOnOutsideClick={false}>

	<div class="text">
		<input
			type="file"
			id="fileInput"
			accept="image/*"
			onchange={onFileSelected}
			style="display: none;"
			bind:this={fileInput}
		/>
		<div class="image-prev {square_avatar ? 'square-avatar' : ''}">
			{#if previewImage}
				<Avatar src={previewImage} size={64} />
			{:else if url}
				<Avatar src={url} size={64} />
			{/if}
		</div>
		<Button color="accent" variant="fill" on:click={() => fileInput?.click()}
			>Choose File</Button
		>
		<div class="info">
			<Text light small>Max file size: {MAX_FILE_SIZE_MB}MB</Text>
			<Text light small>Allowed file types: JPEG, PNG, GIF, SVG, JPG</Text>
		</div>
	</div>
	{#snippet footer()}
		<div>
			<Button color="red" variant="invisible" on:click={resetImage}>Reset</Button>
			<Button variant="invisible" on:click={() => (showModal = false)}>Cancel</Button>

			{#if !image || isUploading || previewImage === null}
				<Button disabled>Upload</Button>
			{:else}
				<Button color="accent" on:click={uploadImageHandler} disabled={isUploading}>
					{#if isUploading}
						<Loader size="small" />
					{:else}
						Upload
					{/if}
				</Button>
			{/if}
		</div>
	{/snippet}
</Modal>

<style>
	.text {
		text-align: center;
	}

	.image-prev {
		margin-bottom: 20px;
		display: flex;
		justify-content: center;
	}

	.info {
		margin-top: 20px;
		display: flex;
		flex-direction: column;
		align-items: center;
	}
	.square-avatar :global(img) {
		border-radius: 0;
	}
</style>
