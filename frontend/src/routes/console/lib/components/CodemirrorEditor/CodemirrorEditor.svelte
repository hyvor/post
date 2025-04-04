<script lang="ts">
	import { onMount } from "svelte";
    import './codemirror';
	import { CODEMIRROR_MODES, importCodemirrorAll } from "./codemirror";

    export let value: string;
    export let ext: keyof typeof CODEMIRROR_MODES;
    export let id: string | number = '';
    export let save: () => void = () => {};
    export let change: (val: string) => void = () => {};

    $: tabSize = ext === 'yaml' ? 2 : 4;

    let editorDiv: HTMLDivElement;
    let cm: any;

    async function initCm() {

        await importCodemirrorAll();

        editorDiv.innerHTML = "";

        function handleSave(cm: any) {
            save()
            //dispatch('save', cm.doc.getValue());
        }

        function handleTab(cm: any) {
            if (cm.somethingSelected()) {
                cm.indentSelection("add");
            } else {
                cm.replaceSelection(cm.getOption("indentWithTabs")? "\t":
                    Array(cm.getOption("indentUnit") + 1).join(" "), "end", "+input");
            }
        }

        cm = (window as any).CodeMirror(editorDiv, {
            value,
            mode: CODEMIRROR_MODES[ext],
            theme: 'solarized',
            keyMap: 'sublime',
            tabSize,
            indentWithTabs: false,
            indentUnit: tabSize,
            lineWrapping: true,
            lineNumbers: true,
            matchBrackets: true,
            matchTags: {bothTags: true},
            autoCloseBrackets: true,
            autoCloseTags: true,
            extraKeys: {
                "Ctrl-S": handleSave,
                "Cmd-S": handleSave,
                "Tab": handleTab
            }
        })
        cm.on('change', function() {
            const val = cm.doc.getValue();
            value = val;
            change(val)
            //dispatch('change', val);
        })

    }

    onMount(initCm);

    // re-create codemirror instance when id changes
    $: if (id) {
        initCm();
    }

</script>



<div 
    class="editor"
    bind:this={editorDiv}
    {...$$restProps}
></div>

<style lang="scss">


    .editor {
        height: 100%;
    }

    .editor :global(.CodeMirror) {
        height:100%;
        font-family: 'source-code-pro', Menlo, 'Courier New', Consolas, monospace!important;
        box-shadow: none!important;
        border-radius:20px;
        background-color: var(--input)!important;
        font-size: 14px;
        line-height: 21px;
        :global(.CodeMirror-line) {
            padding-left: 15px!important;
        }
        :global(.CodeMirror-gutters) {
            background-color: var(--input);
        }
        :global(.CodeMirror-scroll) {
            overflow-x: hidden!important;
        }
    }

</style>