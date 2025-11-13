<script>
	import Header from './(marketing)/Header.svelte';
	import Footer from './(marketing)/Footer.svelte';
	import { Button } from '@hyvor/design/components';
</script>

<Header />
<main>
	<h1 class="heading">404 - Page Not Found</h1>
	<p class="description">
		Sorry, the page you are looking for might have been removed, had its name changed, or is
		temporarily unavailable.
	</p>
	<Button as="a" href="/">Go to Homepage</Button>
</main>

<Footer />

<style>
	main {
		padding: 20px;
		text-align: center;
		height: calc(100vh - 100px);
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
	}
	.heading {
		font-size: 38px;
		margin: 0;
	}
	.description {
		max-width: 100%;
		width: 450px;
	}
</style>
