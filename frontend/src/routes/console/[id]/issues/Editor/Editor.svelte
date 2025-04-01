<script lang="ts">
	import { onMount } from 'svelte';
	import { EditorView } from 'prosemirror-view';
	import { EditorState } from 'prosemirror-state';
	import { newsletterSchema } from './schema';
	import { MarkType, Node, NodeType } from 'prosemirror-model';
	import { lift, toggleMark, wrapIn } from 'prosemirror-commands';
	import HeadingNodeView from './nodeview-heading';
	import { IconButton, toast } from '@hyvor/design/components';
	import { getPlugins } from './plugin';
    import IconImage from '@hyvor/icons/IconImage';
    import IconLink45deg from '@hyvor/icons/IconLink45deg';
    import IconTypeBold from '@hyvor/icons/IconTypeBold';
    import IconTypeItalic from '@hyvor/icons/IconTypeItalic';
    import IconTypeUnderline from '@hyvor/icons/IconTypeUnderline';
    import IconTypeStrikethrough from '@hyvor/icons/IconTypeStrikethrough';
    import IconCode from '@hyvor/icons/IconCode';
    import IconHr from '@hyvor/icons/IconHr';
    import IconTypeH2 from '@hyvor/icons/IconTypeH2';
    import IconQuote from '@hyvor/icons/IconQuote';
	import AddLink from './AddLink.svelte';
	import AddButton from './AddButton.svelte';
	import { contentUpdateId } from '../[issueId]/issueStore';
	import IconBoxArrowInDown from '@hyvor/icons/IconBoxArrowInDown';

	export let content: string | null = null;
    export let docupdate: (doc: string) => void;

	let el: HTMLDivElement;
	let view: EditorView;

	export const DEFAULT_EDITOR_JSON = '{"type": "doc", "content": [{"type": "paragraph"}]}';

	let imageInput: HTMLInputElement;


	function handleUpload() {
		const files = imageInput.files;

		if (!files || files.length === 0) {
			return;
		}

		const file = files[0];

		if (file.size > 5 * 1024 * 1024) {
			toast.error('Image size should be less than 5MB');
			return;
		}

		const toastId = toast.loading('Uploading image...');

        // TODO: Implement uploadImage function
		/*uploadImage(file)
			.then((res) => {
				const { state } = view;
				const { from, to } = state.selection;
				const imageNode = newsletterSchema.nodes.image.createAndFill({
					src: res.url
				})!;
				const tr = state.tr.replaceWith(from, to, imageNode);
				view.dispatch(tr);
				toast.close(toastId);
			})
			.catch((err) => {
				console.error(err);
				toast.error('Failed to upload image', { id: toastId });
			});
        */
	}

	let linkActive = false;
	let boldActive = false;
	let italicActive = false;
	let underlineActive = false;
	let strikethroughActive = false;
	let codeActive = false;

	function updateActiveStates() {
		function isMarkActive(state: EditorState, type: MarkType): boolean {
			const sel = state.selection;
			if (sel.empty) return Boolean(type.isInSet(state.storedMarks || sel.$from.marks()));
			else return state.doc.rangeHasMark(sel.from, sel.to, type);
		}

		const { state } = view;
		linkActive = isMarkActive(state, newsletterSchema.marks.link);
		boldActive = isMarkActive(state, newsletterSchema.marks.strong);
		italicActive = isMarkActive(state, newsletterSchema.marks.em);
		underlineActive = isMarkActive(state, newsletterSchema.marks.underline);
		strikethroughActive = isMarkActive(state, newsletterSchema.marks.strike);
		codeActive = isMarkActive(state, newsletterSchema.marks.code);
	}

	function onToggle(type: 'link' | 'strong' | 'em' | 'underline' | 'strike' | 'code') {
		toggleMark(newsletterSchema.marks[type])(view.state, view.dispatch);
		view.focus();
	}

	function onHeadingToggle() {
		const { state } = view;
		const { $from, $to } = state.selection;

		let range = $from.blockRange($to);
		if (!range) return;

		const targetType = newsletterSchema.nodes.heading;
		const paragraphType = newsletterSchema.nodes.paragraph;

		const tr = state.tr;
		let found = false;
		state.doc.nodesBetween(range.start, range.end, (node, pos) => {
			if (node.type === targetType && node.attrs.level === 2) {
				found = true;
				tr.setNodeMarkup(pos, paragraphType);
			}
		});

		if (!found) {
			tr.setBlockType(range.start, range.end, targetType, { level: 2 });
		}

		view.dispatch(tr.scrollIntoView());

		view.focus();
	}


	let addingButton = false;
	let editingButton = false;
	let editingButtonNode: any = null;

	function onButtonToggle() {
		const { state } = view;
		const { from, to } = state.selection;
		
		// Check if we're clicking on an existing button
		const node = state.doc.nodeAt(from);
		if (node && node.type === newsletterSchema.nodes.button) {
			editingButton = true;
			editingButtonNode = node;
		} else {
			addingButton = true;
		}
	}

	function onButtonAdd(text: string, href: string) {
		const { state } = view;
		const { from, to } = state.selection;
		
		// Create a button node with the provided attributes
		const buttonNode = newsletterSchema.nodes.button.createAndFill({
			href,
			text
		})!;
		
		// Replace the selected content with the button
		const tr = state.tr.replaceWith(from, to, buttonNode);
		view.dispatch(tr);
		view.focus();
	}

	function onButtonEdit(text: string, href: string) {
		const { state } = view;
		
		// Find the button node in the document
		let buttonPos = -1;
		state.doc.descendants((node, pos) => {
			if (node.type === newsletterSchema.nodes.button && node === editingButtonNode) {
				buttonPos = pos;
			}
		});
		
		if (buttonPos === -1) {
			console.error('Could not find button node');
			return;
		}
		
		// Update the button node with new attributes
		const tr = state.tr.setNodeMarkup(buttonPos, undefined, {
			href,
			text
		});
		
		view.dispatch(tr);
		view.focus();
	}

	function addHr() {
		const { state } = view;
		const { schema, selection } = state;
		const { $from } = selection;

		const tr = state.tr;
		const hr = schema.nodes.horizontal_rule.create();
		tr.insert($from.pos, hr);
		view.dispatch(tr.scrollIntoView());
	}

	function onBlockquoteToggle() {
		const { state } = view;

		function isNodeActive(state: EditorState, type: NodeType): boolean {
			const $from = state.selection.$from;

			let wrapperDepth;
			let currentDepth = $from.depth;
			while (currentDepth > 0) {
				const currentNodeAtDepth = $from.node(currentDepth);

				/* Previous versions used node.hasMarkup but that */
				/* mandates deep equality on attrs. We just want to */
				/* ensure that everyting in the passed in attrs */
				/* is present in the node at the depth */
				const isType = type.name === currentNodeAtDepth.type.name;

				if (isType) {
					wrapperDepth = currentDepth;
				}
				currentDepth -= 1;
			}

			return Boolean(wrapperDepth);
		}

		if (isNodeActive(state, newsletterSchema.nodes.blockquote)) {
			lift(state, view.dispatch);
		} else {
			wrapIn(state.schema.nodes.blockquote)(state, view.dispatch);
		}

		view.focus();
	}

	let addingLink = false;
	function toggleLink() {
		if (linkActive) {
			onToggle('link');
		} else {
			addingLink = true;
		}
	}

	function onLinkAdd( url: string) {
		toggleMark(newsletterSchema.marks.link, { href: url })(view.state, view.dispatch);
		view.focus();
		addingLink = false;
	}

	onMount(() => {
		const json = JSON.parse(content || DEFAULT_EDITOR_JSON);

		view = new EditorView(el, {
			state: EditorState.create({
				schema: newsletterSchema,
				plugins: getPlugins(newsletterSchema),
				doc: Node.fromJSON(newsletterSchema, json)
			}),
			nodeViews: {
				heading: (node, view, getPos) => new HeadingNodeView(node, view, getPos)
			},
			dispatchTransaction(tr) {
				const state = view.state.apply(tr);
				view.updateState(state);

				setTimeout(updateActiveStates, 0);

				if (tr.docChanged) {
					docupdate(JSON.stringify(tr.doc.toJSON()));

					contentUpdateId.update((id) => id + 1);
				}
			},
			handleClick: (view: EditorView, pos: number, event: MouseEvent) => {
				// Get the node at the clicked position
				const node = view.state.doc.nodeAt(pos);
				
				// If clicking on a button node, prevent default link behavior
				if (node && node.type === newsletterSchema.nodes.button) {
					event.preventDefault();
					editingButton = true;
					editingButtonNode = node;
					return true;
				}
				return false;
			}
		});
	});
</script>

<!-- svelte-ignore a11y-no-static-element-interactions -->
<!-- svelte-ignore a11y-click-events-have-key-events -->
<div class="wrap" on:click={() => view.focus()}>
	<div class="toolbar">
		<div class="left">
			<IconButton size="small" color="input" on:click={() => imageInput.click()}>
				<IconImage size={12} />
			</IconButton>
			<IconButton size="small" color="input" on:click={toggleLink}>
				<IconLink45deg size={14} />
			</IconButton>
			<IconButton
				size="small"
				color={boldActive ? 'accent' : 'input'}
				on:click={() => onToggle('strong')}
				title="Strong"
			>
				<IconTypeBold size={14} />
			</IconButton>
			<IconButton
				size="small"
				color={italicActive ? 'accent' : 'input'}
				on:click={() => onToggle('em')}
				title="Italic"
			>
				<IconTypeItalic size={14} />
			</IconButton>
			<IconButton
				size="small"
				color={underlineActive ? 'accent' : 'input'}
				on:click={() => onToggle('underline')}
				title="Underline"
			>
				<IconTypeUnderline size={14} />
			</IconButton>
			<IconButton
				size="small"
				color={strikethroughActive ? 'accent' : 'input'}
				on:click={() => onToggle('strike')}
				title="Strikethrough"
			>
				<IconTypeStrikethrough size={14} />
			</IconButton>
			<IconButton
				size="small"
				color={codeActive ? 'accent' : 'input'}
				on:click={() => onToggle('code')}
				title="Inline code"
			>
				<IconCode size={14} />
			</IconButton>
		</div>
		<div class="right">
			<IconButton
				size="small"
				color="input"
				on:click={() => onButtonToggle()}
				title="Button"
			>
				<IconBoxArrowInDown size={14} />
			</IconButton>
			<IconButton 
				size="small"
				color="input"
				on:click={() => addHr()}
				title="Horizontal Rule"
			>
				<IconHr size={14} />
			</IconButton>
			<IconButton
				size="small"
				color="input"
				on:click={() => onHeadingToggle()}
				title="Heading"
			>
				<IconTypeH2 size={14} />
			</IconButton>
			<IconButton
				size="small"
				color="input"
				on:click={() => onBlockquoteToggle()}
				title="Blockquote"
			>
				<IconQuote size={14} />
			</IconButton>
		</div>
	</div>
	<div bind:this={el} class="content" />
</div>

<input
	type="file"
	accept="image/*"
	bind:this={imageInput}
	style="display: none"
	on:change={handleUpload}
/>

{#if addingLink}
	<AddLink add={onLinkAdd} bind:show={addingLink} />
{/if}

{#if addingButton}
	<AddButton add={onButtonAdd} bind:show={addingButton} />
{/if}

{#if editingButton}
	<AddButton 
		add={onButtonEdit} 
		bind:show={editingButton} 
		initialText={editingButtonNode?.attrs.text} 
		initialHref={editingButtonNode?.attrs.href}
		isEditing={true}
	/>
{/if}

<style lang="scss">
	.wrap {
		width: 625px;
		max-width: 100%;
		height: 100%;
		margin: 0 auto;
		border: 1px solid var(--border);
		border-radius: 20px;
		min-height: 400px;
		display: flex;
		flex-direction: column;
	}

	.content {
		flex: 1;
		padding: 10px;
	}
	.wrap :global(.ProseMirror) {
		height: 100%;
		
		&:focus-visible {
			outline: none;
		}

		:global(p) {
			margin: 0 0 20px;
		}
		:global(h1) {
			margin: 0 0 24px;
		}
		:global(h2),
		:global(h3),
		:global(h4),
		:global(h5),
		:global(h6) {
			margin: 0 0 20px;
		}

		:global(h1) {
			font-size: 32px;
		}
		:global(h2) {
			font-size: 28px;
		}
		:global(h3) {
			font-size: 24px;
		}
		:global(h4) {
			font-size: 20px;
		}
		:global(h5) {
			font-size: 16px;
		}
		:global(h6) {
			font-size: 14px;
		}

		:global(:not(pre) > code) {
			background: rgba(135, 131, 120, 0.15);
			color: #eb5757;
			border-radius: 3px;
			font-size: 85%;
			padding: 0.2em 0.4em;
			font-family: monospace;
		}

		:global(blockquote) {
			margin-top: 30px;
			border-width: 0;
			border-color: #000000;
			border-style: solid;
			border-left-width: 4px;
			padding: 10px 15px;
		}

		:global(.heading-selectors-wrap) {
			margin-bottom: 3px;
			:global(button) {
				font-size: 10px;
				background-color: var(--input);
				padding: 2px 4px;
				margin-right: 2px;
				border-radius: 2px;
				opacity: 0.3;
				transition: 0.2s opacity;
			}
			&:hover :global(button) {
				opacity: 0.5;
			}
			:global(button:hover) {
				opacity: 0.7;
			}
			:global(button.selected) {
				background-color: var(--gray-light);
				opacity: 1;
			}
		}

		:global(hr) {
			margin: 0 0 20px;
			height: 1px;
			background-color: currentColor;
			opacity: 0.3;
		}

		:global(blockquote) :global(p:last-child) {
			margin-bottom: 0;
		}

		:global(a) {
			text-decoration: underline;
			color: #0000EE;
		}

		:global(img) {
			display: block;
			margin: 30px auto;
			max-width: 100%;
			height: auto;
		}

		:global(.pm-button) {
			display: inline-block;
			background-color: var(--accent);
			color: white;
			padding: 8px 16px;
			border-radius: 20px;
			text-decoration: none;
			text-align: center;
			margin: 20px 0;
			transition: opacity 0.2s;
			cursor: pointer;
			pointer-events: none;
		}

		:global(.pm-button:hover) {
			opacity: 0.9;
		}

		:global(.pm-button:focus) {
			outline: 2px solid var(--accent);
			outline-offset: 2px;
		}

		:global(.pm-button-wrapper) {
			pointer-events: auto;
			cursor: pointer;
			display: flex;
			justify-content: center;
			width: 100%;
		}
	}
	.wrap :global(.ProseMirror :first-child) {
		margin-top: 0 !important;
	}
	.toolbar {
		padding: 10px 15px;
		border-bottom: 1px solid var(--border);
		display: flex;
		position: sticky;
		top: -30px;
		background-color: var(--box-background);
		z-index: 100;
		border-radius: 20px 20px 0 0;
	}
	.left {
		flex: 1;
	}
</style>
