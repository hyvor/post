<script lang="ts">
	interface Props {
		image: string | null;
		link?: string;
	}

	let { image = null, link }: Props = $props();
</script>

<div class="browser-window">
	<div class="browser-header">
		<div class="buttons">
			<span class="red"></span>
			<span class="yellow"></span>
			<span class="green"></span>
		</div>
		<div class="address-bar">{link}</div>
	</div>
	<div class="browser-content">
		<img src={image} alt="Hyvor Blogs Screenshot" />
	</div>
</div>

<style>
	.browser-window {
		border-radius: 10px;
		overflow: hidden;
		box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
		background: #fff;
	}

	.browser-header {
		display: flex;
		align-items: center;
		padding: 8px 12px;
		background: #e3e3e3;
		border-bottom: 1px solid #ddd;
	}

	.buttons {
		display: flex;
		gap: 6px;
		margin-right: 12px;
	}

	.buttons span {
		width: 12px;
		height: 12px;
		border-radius: 50%;
		display: inline-block;
	}

	.red {
		background: #ff5f57;
	}
	.yellow {
		background: #ffbd2e;
	}
	.green {
		background: #28c840;
	}

	.address-bar {
		flex: 1;
		background: #f3f3f3;
		border-radius: 8px;
		height: 20px;
		display: flex;
		align-items: center;
		padding: 0 10px;
		font-size: 12px;
		color: #555;
		justify-content: center;
	}

	.browser-content img {
		width: 100%;
		display: block;
	}
</style>
