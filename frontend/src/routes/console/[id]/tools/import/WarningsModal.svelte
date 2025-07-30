<script lang="ts">
    import { CodeBlock, Modal } from '@hyvor/design/components';
    import { getI18n } from '../../../lib/i18n';

    interface Props {
        show: boolean;
        warnings: string | null;
    }

    let { show = $bindable(), warnings = $bindable() }: Props = $props();

    const I18n = getI18n();
</script>

<Modal
    bind:show
    title={I18n.t('console.tools.import.importWarnings')}
>
    <div class="field-modal-wrap">
        <CodeBlock
            language="yaml"
            code={warnings ?? ''}
        />
    </div>
</Modal>

<style>
    .field-modal-wrap {
        max-height: 65vh;
        overflow-y: auto;
    }
</style>
