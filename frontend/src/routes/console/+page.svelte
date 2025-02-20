<script lang="ts">
	import { onMount } from "svelte";
	import { projectListStore } from "./lib/stores/projectListStore";
	import { consoleUrl } from "./lib/consoleUrl";
	import { goto } from "$app/navigation";

	 onMount(() => {
        if ($projectListStore.length > 0) {
            goto(consoleUrl($projectListStore[0]!.id.toString()), {replaceState: true})
        } else {
            goto(consoleUrl('new'), {replaceState: true})
        }
    });
</script>
