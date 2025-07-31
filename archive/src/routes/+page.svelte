<script lang="ts">
    import {subdomainStore} from "$lib/archiveStore";
    import ArchivePage from "./(archive)/ArchivePage.svelte";
</script>

<h1>Welcome to {$subdomainStore}!</h1>

<ArchivePage/>
