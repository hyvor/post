<script>
	import './app.css';
	import { Base } from '@hyvor/design/components';
	import { page } from '$app/state';
</script>

<Base marketing={page.route.id?.includes('(docs)')}>
	<slot />
</Base>
