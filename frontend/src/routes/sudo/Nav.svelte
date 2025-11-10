<script>
    import {NavLink} from '@hyvor/design/components';
    import IconClipboardCheck from "@hyvor/icons/IconClipboardCheck";
    import IconBoxArrowInDown from "@hyvor/icons/IconBoxArrowInDown";
    import NavItem from "../console/@components/Nav/NavItem.svelte";
    import {page} from '$app/stores';
    import {statsStore} from "./lib/stores/sudoStore.js";
    import {Tag} from "@hyvor/design/components";

</script>

<div class="nav">
    <div class="nav-links hds-box">
        <NavLink href="/sudo/approvals" active={$page.url.pathname === '/sudo/approvals'}>
            <NavItem>
                <IconClipboardCheck slot="icon"/>
                <span slot="text">
                    Approvals
                    {#if $statsStore.reviewing_approvals > 0}
                        <Tag size="x-small" color="orange">
                            {$statsStore.reviewing_approvals}
                        </Tag>
                    {/if}
                </span>
            </NavItem>
        </NavLink>
        <NavLink href="/sudo/imports" active={$page.url.pathname === '/sudo/imports'}>
            <NavItem>
                <IconBoxArrowInDown slot="icon"/>
                <span slot="text">
                    Subscriber Imports
                    {#if $statsStore.pending_imports > 0}
                        <Tag size="x-small" color="orange">
                            {$statsStore.pending_imports}
                        </Tag>
                    {/if}
                </span>
            </NavItem>
        </NavLink>
    </div>
</div>

<style lang="scss">

    .nav {
        width: 280px;
        padding: 15px;
        padding-right: 0;
        flex-shrink: 0;
    }

    .nav-links {
        height: calc(100vh - 30px);
        padding: 15px 0;
        overflow: hidden;
    }

    .nav-links :global(a.active) {
        background-color: var(--accent-light-mid);
    }
</style>
