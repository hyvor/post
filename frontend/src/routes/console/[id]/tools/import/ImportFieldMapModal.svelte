<script lang="ts">
    import { Modal, SplitControl, TextInput } from '@hyvor/design/components';
    import { getI18n } from '../../../lib/i18n';

    interface Props {
        show: boolean;
        fieldMap: Record<string, string|null>;
    }

    let { show = $bindable(), fieldMap = $bindable() }: Props = $props();

    const I18n = getI18n();
</script>

<Modal
    bind:show
    title={I18n.t('console.tools.import.mappedFields')}
>

    {#each Object.entries(fieldMap) as [key, value]}
        <SplitControl label={key}>
            <TextInput
                value={value ? value : I18n.t('console.tools.import.notMapped')}
                disabled
                block
                style={value ? '' : 'color: var(--text-light);'}
            />
        </SplitControl>
    {/each}

</Modal>

