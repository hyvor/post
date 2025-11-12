<script lang="ts">
    import {page} from '$app/state';
    import {NavLink} from '@hyvor/design/components';
    import {newsletterStore} from '../../lib/stores/newsletterStore';
    import IconBoxArrowInDown from '@hyvor/icons/IconBoxArrowInDown';
    import IconBoxArrowUp from '@hyvor/icons/IconBoxArrowUp';
    import {getI18n} from '../../lib/i18n';

    interface Props {
        children?: import('svelte').Snippet;
    }

    let {children}: Props = $props();

    const prefix = `/console/${$newsletterStore.subdomain}/tools`;
    const I18n = getI18n();
</script>

<div class="settings">
    <div class="nav hds-box">
        <NavLink href="{prefix}/import" active={page.url.pathname === prefix + '/import'}>
            {#snippet start()}
                <IconBoxArrowInDown/>
            {/snippet}
            {I18n.t('console.tools.import.title')}
        </NavLink>

        <NavLink href="{prefix}/export" active={page.url.pathname === prefix + '/export'}>
            {#snippet start()}
                <IconBoxArrowUp/>
            {/snippet}
            {I18n.t('console.tools.export.title')}
        </NavLink>
    </div>

    <div class="content hds-box">
        {@render children?.()}
    </div>
</div>

<style>
    .settings {
        display: flex;
        height: 100%;
    }

    .nav {
        width: 315px;
        margin-right: 15px;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        height: 100%;
        padding: 25px 0;
        overflow: auto;
    }

    .nav :global(a.active) {
        background-color: var(--accent-light-mid);
    }

    .content {
        flex: 1;
        min-width: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    @media (max-width: 992px) {
        .settings {
            flex-direction: column;
        }

        .nav {
            width: 100%;
            margin-right: 0;
            margin-bottom: 20px;
        }
    }
</style>
