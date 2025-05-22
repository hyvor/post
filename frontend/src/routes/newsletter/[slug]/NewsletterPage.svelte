<script>
	import { newsletterStore } from './newsletterPageStore';
</script>

<div class="container">
	<div class="header">
		{$newsletterStore.name}
	</div>
</div>

<style>
	.container {
		width: 700px;
		margin: 0 auto;
		max-width: 100%;
	}
	.header {
		padding: 20px;
		font-size: 20px;
		font-weight: 600;
	}
</style>
