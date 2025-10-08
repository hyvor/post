<script>
	import SingleBox from '../@components/content/SingleBox.svelte';
	import HomeStats from './home/HomeStats.svelte';
	import ListView from './home/ListView.svelte';
</script>

<SingleBox style="overflow-auto">
	<HomeStats />
	<ListView />
</SingleBox>
