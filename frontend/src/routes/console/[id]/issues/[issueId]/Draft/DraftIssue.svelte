<script lang="ts">
	import {
		Button,
		Checkbox,
		FormControl,
		Link,
		Radio,
		SplitControl,
		TextInput,
		Validation,
		confirm,
		toast
	} from '@hyvor/design/components';
	import type { Issue } from '../../../../types';
	import { issueStore, listStore, projectStore } from '../../../../lib/stores/projectStore';
	import { sendIssue, updateIssue } from '../../../../lib/actions/issueActions';
	import { debounce } from '../../../../../../lib/helpers/debounce';
	import { EMAIL_REGEX } from '../../../../lib/regex';
	import Editor from '../../Editor/Editor.svelte';
	import Preview from './Preview.svelte';

	export let issue: Issue;
    export let send: (e: Issue) => void;

	let scrollTopEl: HTMLDivElement;

	let subject = issue.subject || '';
	let fromName = issue.from_name;
	let replyToEmail = issue.reply_to_email || '';
	let selectedLists = issue.lists;
	let content = issue.content || '';

	let subjectError = '';
	let replyToEmailError = '';
	let selectedSegmentsError = '';

	let testEmail = '';

	let sendingEmails = getSendingAddresses();
	let currentSendingEmail = issue.from_email || sendingEmails?.[1] || sendingEmails[0];

	function update() {
		updateIssue(issue.id, {
			subject,
			from_name: fromName,
			from_email: currentSendingEmail,
			reply_to_email: replyToEmail,
			lists: selectedLists,
			content
		})
			.then((res) => {
				$issueStore = $issueStore.map((i) => (i.id === issue.id ? res : i));
			})
			.catch((e) => {
				toast.error('Failed to update issue: ' + e.message);
			});
	}

	const debouncedUpdate = debounce(update, 1000);

	function onSegmentChange(id: number) {
		if (selectedLists.includes(id)) {
			selectedLists = selectedLists.filter((s) => s !== id);
		} else {
			selectedLists = [...selectedLists, id];
		}
		debouncedUpdate();
	}

	function updateReplyToEmail() {
		replyToEmailError = '';

		if (replyToEmail && !EMAIL_REGEX.test(replyToEmail)) {
			replyToEmailError = 'Invalid email address';
			return;
		}

		debouncedUpdate();
	}

	function validate(): boolean {
		subjectError = '';
		selectedSegmentsError = '';

		let ret = true;

		function hasError() {
			ret = false;
			scrollTopEl.scrollIntoView({ behavior: 'smooth' });
		}

		if (subject.trim() === '') {
			subjectError = 'Subject is required';
			hasError();
		}

		if (selectedLists.length === 0) {
			selectedSegmentsError = 'At least one segment is required';
			hasError();
		}

		return ret;
	}

	async function onSend() {
		if (!validate()) {
			return;
		}

		const confirmed = await confirm({
			title: 'Final Confirmation',
			content:
				'You are about to send this newsletter issue. This is the final step. Are you sure you want to send this? Please double-check everything before sending as you cannot undo this action.',
			confirmText: 'Yes, send it',
			cancelText: 'Cancel'
		});

		if (confirmed) {
			confirmed.loading();

			sendIssue(issue.id)
				.then((res) => {
					toast.success('Newsletter sent successfully');
					send(res);
				})
				.catch((e) => {
					toast.error('Failed to send newsletter: ' + e.message);
				})
				.finally(() => {
					confirmed.close();
				});
		}
	}

	function onTestSend() {
		const toastId = toast.loading('Sending test email...');

        //TODO: Implement sendIssueTest
		/*sendIssueTest(issue.id, testEmail)
			.then((res) => {
				toast.success('Test email sent successfully', { id: toastId });
			})
			.catch((e) => {
				toast.error('Failed to send test email: ' + e.message, { id: toastId });
			});
        */
	}

	function onContentDocUpdate(e: string) {
		content = e;
		debouncedUpdate();
	}

	function updateSendingAddresses() {
		sendingEmails = getSendingAddresses();
	}
	function getSendingAddresses() {
        // TODO: Add sending addresses stored in project
		return ['newsletters@post.hyvor.com'];
	}
</script>

<div bind:this={scrollTopEl} />

<SplitControl label="Subject">
	<FormControl>
		<TextInput
			block
			bind:value={subject}
			maxlength={255}
			on:input={debouncedUpdate}
			state={subjectError ? 'error' : undefined}
		/>
		{#if subjectError}
			<Validation state="error">{subjectError}</Validation>
		{/if}
	</FormControl>
</SplitControl>

<SplitControl label="Segments">
	{#each $listStore as list}
		<div class="list">
			<Checkbox
				checked={selectedLists.includes(list.id)}
				on:change={() => onSegmentChange(list.id)}
			>
				{list.name} ({list.subscribers_count} subscribers)
			</Checkbox>
		</div>
	{/each}
	{#if selectedSegmentsError}
		<Validation state="error">{selectedSegmentsError}</Validation>
	{/if}
</SplitControl>

<SplitControl label="Emails">
	<div slot="nested">
		<SplitControl label="From Name">
			<TextInput
				block
				placeholder={$projectStore.name}
				bind:value={fromName}
				on:input={debouncedUpdate}
				on:blur={debouncedUpdate}
			/>
		</SplitControl>
		<SplitControl
			label="From Email"
			caption="This is the email address that will be shown as the sender"
		>
			<div class="from-email">
				<FormControl>
					{#each sendingEmails as sendingEmail}
						<Radio
							bind:group={currentSendingEmail}
							value={sendingEmail}
							name="sending-email"
							style="font-weight:normal"
							on:change={debouncedUpdate}
						>
							{sendingEmail}
						</Radio>
					{/each}
				</FormControl>
			</div>


            <!-- 
            TODO: Implement custom email domain
			<div style="font-size:14px;margin-top:5px;">
				{#if $emailDomain && $emailDomain.verified && $emailDomain.verified_in_ses}
					<ConfigureSendingAddresses on:update={updateSendingAddresses} />
				{:else}
					<Link href={consoleUrlWithProject('/settings/notifications')}
						>Configure a custom email domain</Link
					> to send email from your own domain.
				{/if}
			</div>
            -->
		</SplitControl>
		<SplitControl
			label="Reply to Email"
			caption="You will receive replies to this email address"
		>
			<FormControl>
				<TextInput
					block
					bind:value={replyToEmail}
					on:blur={updateReplyToEmail}
					state={replyToEmailError ? 'error' : undefined}
				/>
				{#if replyToEmailError}
					<Validation state="error">{replyToEmailError}</Validation>
				{/if}
			</FormControl>
		</SplitControl>
	</div>
</SplitControl>

<SplitControl label="Content" column>
	<Editor
        content={content}
        docupdate={onContentDocUpdate}
    />
</SplitControl>

<Preview id={issue.id} />

<SplitControl label="Send Test Email">
	<div class="send-test">
		<TextInput bind:value={testEmail} block placeholder="Email address" />
		<Button on:click={onTestSend}>Send Test Email</Button>
	</div>
</SplitControl>

<div class="send">
	<div class="ready">Ready to send?</div>
	<Button size="large" on:click={onSend}>
		Send Now
	</Button>
</div>

<style>
	.list {
		margin-bottom: 10px;
	}
	.send {
		padding: 30px;
		text-align: center;
	}
	.send .ready {
		font-size: 20px;
		margin-bottom: 10px;
	}
	.send-test {
		display: flex;
		gap: 6px;
	}
	.send-test :global(button) {
		flex-shrink: 0;
	}
	.from-email :global(label) {
		font-weight: normal !important;
	}

	@media (max-width: 992px) {
		.send-test {
			flex-direction: column;
		}
	}
</style>
