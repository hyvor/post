<script lang="ts">
	import dayjs from "dayjs";


	export let time: number;
	$: day = dayjs.unix(time);
</script>

<time datetime={day.toISOString()}>{day.format('MMM D, YYYY')}</time>
