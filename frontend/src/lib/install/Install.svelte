<script lang="ts">
    import { platforms } from '../install/platforms';
    import PlatformList from '../install/PlatformList.svelte';
    import { DocsContent } from '@hyvor/design/marketing';

    interface Props {
        platform: string; 
        prefix: string;
        websiteId?: null | number | string;
    }

    // let { platform, prefix, websiteId = null }: Props = $props();
    let { platform = 'html', prefix, websiteId = null }: Props = $props();


    // 1. Safely derive platform data
    let platformData = $derived(platforms[platform]);
    
    // 2. Safely derive component with a fallback
    let component = $derived(platformData?.component);
</script>

<PlatformList {prefix} {platform} />

<div class="guide-content">
    <!-- <DocsContent>
        {#if component}
            {@const SvelteComponent = component}
            <SvelteComponent />
        {:else if platformData?.code || platform === 'html'}
            <p>Standard HTML installation guide goes here...</p>
        {:else}
            <div class="error">
                <p>Platform <strong>{platform}</strong> not found or has no guide component.</p>
            </div>
        {/if}
    </DocsContent> -->
    <p style="opacity:0.6">platform = "{platform}"</p>

	<DocsContent>
		{@const SvelteComponent = component}
		<SvelteComponent />
	</DocsContent>
</div>

<style lang="scss">
    .guide-content {
        flex: 1;

        :global(.content-wrap > div) {
            box-shadow: none !important;
            :global(content) {
                padding: 0 !important;
            }
        }

        :global(.content-wrap) {
            margin: 0;
        }
    }
    
    .error {
        padding: 20px;
        background: #fff5f5;
        border: 1px solid #feb2b2;
        border-radius: 8px;
        color: #c53030;
    }
</style>