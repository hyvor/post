<script lang="ts">
    import {page} from '$app/state';
    import {NavLink} from '@hyvor/design/components';
    import IconBrush from '@hyvor/icons/IconBrush';
    import {newsletterStore} from '../../lib/stores/newsletterStore';
    import IconEnvelopeAt from '@hyvor/icons/IconEnvelopeAt';
    import IconPeople from '@hyvor/icons/IconPeople';
    import IconCardText from '@hyvor/icons/IconCardText';
    import IconEnvelopeCheck from '@hyvor/icons/IconEnvelopeCheck';
    import IconDatabase from '@hyvor/icons/IconDatabase';
    import IconSendArrowUp from '@hyvor/icons/IconSendArrowUp';
    import IconKey from '@hyvor/icons/IconKey';
    import {getI18n} from '../../lib/i18n';
    import {setContext} from 'svelte';
    import {saveDiscardBoxClassContextName} from '../@components/save/save';

    interface Props {
        children?: import('svelte').Snippet;
    }

    let {children}: Props = $props();

    const prefix = `/console/${$newsletterStore.id}/settings`;

    setContext(saveDiscardBoxClassContextName, 'settings-content');

    const I = getI18n();
</script>

<div class="settings">
    <div class="nav hds-box">
        <NavLink href={prefix} active={page.url.pathname === prefix}>
            {#snippet start()}
                <IconCardText/>
            {/snippet}
            {I.t('console.settings.newsletter.title')}
        </NavLink>

        <NavLink href="{prefix}/users" active={page.url.pathname === prefix + '/users'}>
            {#snippet start()}
                <IconPeople/>
            {/snippet}
            {I.t('console.settings.users.title')}
        </NavLink>

        <NavLink
            href="{prefix}/sending-profiles"
            active={page.url.pathname === prefix + '/sending-profiles'}
        >
            {#snippet start()}
                <IconSendArrowUp/>
            {/snippet}
            {I.t('console.settings.sendingProfiles.title')}
        </NavLink>

        <NavLink
            href="{prefix}/api"
            active={page.url.pathname === prefix + '/api'}
        >
            {#snippet start()}
                <IconKey/>
            {/snippet}
            {I.t('console.settings.api.title')}
        </NavLink>

        <div class="section-div"></div>

        <NavLink href="{prefix}/design" active={page.url.pathname === prefix + '/design'}>
            {#snippet start()}
                <IconBrush/>
            {/snippet}
            Email Design
        </NavLink>

        <NavLink href="{prefix}/form" active={page.url.pathname === prefix + '/form'}>
            {#snippet start()}
                <IconEnvelopeAt/>
            {/snippet}
            {I.t('console.settings.form.signupForm')}
        </NavLink>

        <NavLink href="{prefix}/metadata" active={page.url.pathname === prefix + '/metadata'}>
            {#snippet start()}
                <IconDatabase/>
            {/snippet}
            {I.t('console.settings.metadata.subscriberMetadata')}
        </NavLink>

        <div class="section-div"></div>
    </div>

    <div class="content hds-box settings-content">
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

    .section-div {
        height: 25px;
        flex-shrink: 0;
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
