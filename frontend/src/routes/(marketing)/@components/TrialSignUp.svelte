<script lang="ts">
    import {Button} from '@hyvor/design/components';
    import TrialChecks from './TrialChecks.svelte';
    import {getMarketingI18n} from '../locale';

    const I18n = getMarketingI18n();

    interface Props {
        title?: string;

        [key: string]: any;
    }

    let {title = I18n.t('comingSoon.readytoExplore'), ...rest}: Props = $props();
</script>

<div class="wrap" {...rest}>
    <div class="hds-container inner">
        <h3>
            {title}
        </h3>

        <div class="button-wrap">
            <Button as="a" size="x-large" href={`/#waitlist`}
            >{I18n.t('comingSoon.joinWaitlist')}</Button
            >
        </div>

        <div class="trial-checks">
            <TrialChecks/>
        </div>
    </div>
</div>

<style>
    .wrap {
        background-color: var(--accent-light);
        padding: 80px 15px;
        margin: 60px 0;
    }

    .inner {
        width: 700px;
        max-width: 100%;
        margin: auto;
    }

    h3 {
        text-align: center;
        font-size: 25px;
        font-weight: 600;
    }

    .button-wrap {
        padding: 20px;
        text-align: center;
    }

    .trial-checks {
        display: flex;
        gap: 20px;
        justify-content: center;
        margin-top: 50px;
        flex-direction: row;
    }

    .trial-checks :global(.checks) {
        flex-direction: row;
        gap: 15px !important;
    }
</style>
