<script lang="ts">
	import { onMount } from "svelte";
	import { userProjectsStore } from "./lib/stores/userProjectsStore";
	import { consoleUrl } from "./lib/consoleUrl";
	import { goto } from "$app/navigation";

	 onMount(() => {
        if ($userProjectsStore.length > 0) {
            goto(consoleUrl($userProjectsStore[0]!.project.id.toString()), {replaceState: true})
        } else {
            goto(consoleUrl('new'), {replaceState: true})
        }
    });
</script>
