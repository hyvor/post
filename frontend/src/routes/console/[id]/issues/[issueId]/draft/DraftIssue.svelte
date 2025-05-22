<script lang="ts">
	import {
		Button,
		Checkbox,
		FormControl,
		Modal,
		Radio,
		SplitControl,
		TextInput,
		Validation,
		confirm,
		toast
	} from '@hyvor/design/components';
	import type { Issue } from '../../../../types';
	import { issueStore, listStore, projectStore } from '../../../../lib/stores/newsletterStore';
	import { sendIssue, sendIssueTest, updateIssue } from '../../../../lib/actions/issueActions';
	import { debounce } from '../../../../../../lib/helpers/debounce';
	import { EMAIL_REGEX } from '../../../../lib/regex';
	import Editor from '../../Editor/Editor.svelte';
	import Preview from './Preview.svelte';
	import IconSend from '@hyvor/icons/IconSend';
	import { onMount, onDestroy } from 'svelte';
	import { getSendingAddresses } from '../../../../lib/actions/sendingAddressActions';
	import SendingEmails from '../../../settings/sending/SendingAddresses.svelte';
	import { getI18n } from '../../../../lib/i18n';

	interface Props {
		issue: Issue;
		send: (e: Issue) => void;
	}

	let { issue, send }: Props = $props();

	let scrollTopEl = $state({} as HTMLDivElement);
	let subjectInput = $state({} as HTMLInputElement);

	let subject = $state(issue.subject || '');
	let fromName = $state(issue.from_name);
	let replyToEmail = $state(issue.reply_to_email || '');
	let selectedLists = $state(issue.lists);
	let content = $state(issue.content || '');
	let showSendingEmailsModal = $state(false);

	let subjectError = $state('');
	let replyToEmailError = $state('');
	let selectedSegmentsError = $state('');

	let testEmail = $state('');

	let sendingEmails = $state([] as string[]);
	let currentSendingEmail = $state(issue.from_email);

	function initSendingEmails() {
		getSendingAddresses()
			.then((emails) => {
				let emailList = emails.map((email) => email.email);
				emailList = [$projectStore.default_email_username + '@hvrpst.com', ...emailList];
				sendingEmails = emailList;

				// Find the default sending address
				const defaultEmail = emails.find((email) => email.is_default)?.email;
				if (defaultEmail) {
					currentSendingEmail = defaultEmail;
					debouncedUpdate();
				}
			})
			.catch((e) => {
				toast.error('Failed to load sending emails: ' + e.message);
			});
	}

	let contentDirty = false;
	let previewKey = $state(0);
	let previewInterval: ReturnType<typeof setInterval>;

	onMount(() => {
		previewInterval = setInterval(() => {
			if (contentDirty) {
				previewKey += 1;
				contentDirty = false;
			}
		}, 10000);
	});

	onDestroy(() => {
		clearInterval(previewInterval);
	});

	function update() {
		updateIssue(issue.id, {
			subject,
			from_name: fromName,
			from_email: currentSendingEmail,
			// reply_to_email: replyToEmail,
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

		sendIssueTest(issue.id, testEmail)
			.then((res) => {
				toast.success('Test email sent successfully', { id: toastId });
			})
			.catch((e) => {
				toast.error('Failed to send test email: ' + e.message, { id: toastId });
			});
	}

	function onContentDocUpdate(e: string) {
		content = e;
		contentDirty = true;
		debouncedUpdate();
	}

	const I18n = getI18n();

	onMount(() => {
		initSendingEmails();

		if (subjectInput.value === '') {
			subjectInput.focus();
		}
	});
</script>

<div bind:this={scrollTopEl}></div>

<SplitControl label={I18n.t('console.issues.draft.subject')}>
	<FormControl>
		<TextInput
			block
			bind:value={subject}
			maxlength={255}
			on:input={debouncedUpdate}
			state={subjectError ? 'error' : undefined}
			bind:input={subjectInput}
			placeholder={I18n.t('console.issues.draft.subject') + '...'}
		/>
		{#if subjectError}
			<Validation state="error">{subjectError}</Validation>
		{/if}
	</FormControl>
</SplitControl>

<SplitControl label={I18n.t('console.issues.draft.lists')}>
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
	{#snippet nested()}
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
				<Button on:click={() => (showSendingEmailsModal = true)}
					>Manage Sending Emails</Button
				>
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
	{/snippet}
</SplitControl>

<SplitControl label="Content" column>
	<Editor {content} docupdate={onContentDocUpdate} />
</SplitControl>

{#key previewKey}
	<Preview id={issue.id} />
{/key}

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
		{#snippet end()}
			<IconSend />
		{/snippet}
	</Button>
</div>

<Modal bind:show={showSendingEmailsModal} title="Manage Sending Emails">
	<SendingEmails updateContent={() => initSendingEmails()} />
</Modal>

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
