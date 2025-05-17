<script lang="ts">
	import { browser } from "$app/environment";
	import { InternationalizationService } from "@hyvor/design/components";
	import { MARKETING_LANGUAGES } from "./[lang]/locale";
	import { goto } from "$app/navigation";


    if (browser) {

        const closestLanguage = 
        InternationalizationService.getLocaleFromLocalStorage() ||
        InternationalizationService.getClosestLanguageCode(
            window.navigator.language,
            MARKETING_LANGUAGES.map((lang) => lang.code)
        ) || 
        MARKETING_LANGUAGES[0].code;

        goto(`/${closestLanguage}`, { replaceState: true });

    }

</script>

<svelte:head>
    <title>
        Hyvor Post
    </title>
</svelte:head>