<script lang="ts">
	import {
		SplitControl,
		Textarea,
		TextInput,
		Button,
		toast,
		Callout,
		Checkbox
	} from '@hyvor/design/components';
	import { getI18n } from '../../lib/i18n';
	import { createApproval, updateApproval } from '../../lib/actions/approvalActions';
	import { approvalStore, userApprovalStatusStore } from '../../lib/stores/consoleStore';
	import type { Approval } from '../../types';
	import IconHourglassSplit from '@hyvor/icons/IconHourglassSplit';
	import IconXCircle from '@hyvor/icons/IconXCircle';
	import IconCardChecklist from '@hyvor/icons/IconCardChecklist';

	const I18n = getI18n();

	let approval: Approval = $state($approvalStore);

	let companyName: string = $state(approval?.company_name || '');
	let country: string = $state(approval?.country || '');
	let website: string = $state(approval?.website || '');
	let socialLinks: string = $state(approval?.social_links || '');
	let typeOfContent: string = $state(approval?.type_of_content || '');
	let frequency: string = $state(approval?.frequency || '');
	let existingList: string = $state(approval?.existing_list || '');
	let sample: string = $state(approval?.sample || '');
	let whyPost: string = $state(approval?.why_post || '');
	let checkbox1: boolean = $state(false);
	let checkbox2: boolean = $state(false);

	let error: string | undefined = $state(undefined);

	function validate() {
		if (companyName === '') {
			error = I18n.t('console.approve.companyNameRequired');
			return false;
		}
		if (country === '') {
			error = I18n.t('console.approve.countryRequired');
			return false;
		}
		if (website === '') {
			error = I18n.t('console.approve.websiteRequired');
			return false;
		}
		try {
			new URL(website);
		} catch {
			error = I18n.t('console.approve.websiteInvalid');
			return false;
		}
		if (!checkbox1 || !checkbox2) {
			error = I18n.t('console.approve.checkboxRequired');
			return false;
		}
		return true;
	}

	function onSubmit() {
		if (!validate() && error) {
			toast.error(error);
			return;
		}

		if (approval) {
			updateApproval(approval.id, {
				company_name: companyName !== approval.company_name ? companyName : null,
				country: country !== approval.country ? country : null,
				website: website !== approval.website ? website : null,
				social_links: socialLinks !== (approval.social_links ?? '') ? socialLinks : null,
				type_of_content:
					typeOfContent !== (approval.type_of_content ?? '') ? typeOfContent : null,
				frequency: frequency !== (approval.frequency ?? '') ? frequency : null,
				existing_list:
					existingList !== (approval.existing_list ?? '') ? existingList : null,
				sample: sample !== (approval.sample ?? '') ? sample : null,
				why_post: whyPost !== (approval.why_post ?? '') ? whyPost : null
			})
				.then((data) => {
					approvalStore.set(data);
					toast.success(I18n.t('console.approve.updatedInfo'));
				})
				.catch(() => {
					toast.error(I18n.t('console.approve.error'));
				});
		} else {
			createApproval({
				company_name: companyName,
				country: country,
				website: website,
				social_links: socialLinks,
				type_of_content: typeOfContent,
				frequency: frequency,
				existing_list: existingList,
				sample: sample,
				why_post: whyPost
			})
				.then((data) => {
					approvalStore.set(data);
					userApprovalStatusStore.set(data.status);
					toast.success(I18n.t('console.approve.submittedForApproval'));
				})
				.catch(() => {
					toast.error(I18n.t('console.approve.error'));
				});
		}
	}

	let isUpdating: boolean = $state(false);

	$effect(() => {
		isUpdating =
			!!approval &&
			(companyName !== approval.company_name ||
				country !== approval.country ||
				website !== approval.website ||
				socialLinks !== (approval.social_links ?? '') ||
				typeOfContent !== (approval.type_of_content ?? '') ||
				frequency !== (approval.frequency ?? '') ||
				existingList !== (approval.existing_list ?? '') ||
				sample !== (approval.sample ?? '') ||
				whyPost !== (approval.why_post ?? ''));
	});

	let readOnly: boolean = $state(false);

	$effect(() => {
		readOnly = !!approval && approval.status === 'rejected';
	});
</script>

{#if !$approvalStore || $approvalStore?.status === 'pending'}
	<Callout type="warning">
		{#snippet icon()}
			<IconCardChecklist />
		{/snippet}
		{I18n.t('console.approve.pendingNotice')}
	</Callout>
{:else if $approvalStore?.status === 'reviewing'}
	<Callout type="info">
		{#snippet icon()}
			<IconHourglassSplit />
		{/snippet}
		{I18n.t('console.approve.reviewNotice')}
	</Callout>
{:else if $approvalStore?.status === 'rejected'}
	<Callout type="danger">
		{#snippet icon()}
			<IconXCircle />
		{/snippet}
		{I18n.t('console.approve.rejectNotice')}
		{#if $approvalStore?.public_note}
			<p>
				<strong>
					{I18n.t('console.approve.rejectReason', {
						reason: $approvalStore?.public_note
					})}
				</strong>
			</p>
		{/if}
	</Callout>
{/if}

<SplitControl
	label={`${I18n.t('console.approve.companyName')}*`}
	caption={I18n.t('console.approve.companyNameCaption')}
>
	<TextInput bind:value={companyName} maxLength={255} disabled={readOnly} block />
</SplitControl>

<SplitControl
	label={I18n.t('console.approve.country')}
	caption={`(${I18n.t('console.approve.required')})`}
>
	<TextInput bind:value={country} maxLength={255} disabled={readOnly} block />
</SplitControl>

<SplitControl
	label={I18n.t('console.approve.website')}
	caption={`(${I18n.t('console.approve.required')})`}
>
	<TextInput type="url" bind:value={website} maxLength={1024} disabled={readOnly} block />
</SplitControl>

<SplitControl
	label={I18n.t('console.approve.socialLinks')}
	caption={`(${I18n.t('console.approve.preferred')})`}
>
	<Textarea bind:value={socialLinks} maxLength={1024} disabled={readOnly} block />
</SplitControl>

<SplitControl
	label={I18n.t('console.approve.typeOfContent')}
	caption={I18n.t('console.approve.typeOfContentCaption')}
>
	<TextInput
		bind:value={typeOfContent}
		maxLength={1024}
		disabled={readOnly}
		block
		placeholder={readOnly ? '' : I18n.t('console.approve.typeOfContentPlaceholder')}
	/>
</SplitControl>

<SplitControl
	label={I18n.t('console.approve.frequency')}
	caption={I18n.t('console.approve.frequencyCaption')}
>
	<TextInput
		placeholder={readOnly ? '' : I18n.t('console.approve.frequencyPlaceholder')}
		maxLength={1024}
		bind:value={frequency}
		disabled={readOnly}
		block
	/>
</SplitControl>

<SplitControl
	label={I18n.t('console.approve.existingList')}
	caption={I18n.t('console.approve.existingListCaption')}
>
	<Textarea bind:value={existingList} maxLength={1024} disabled={readOnly} block />
</SplitControl>

<SplitControl
	label={I18n.t('console.approve.sample')}
	caption={I18n.t('console.approve.sampleCaption')}
>
	<TextInput bind:value={sample} maxLength={1024} disabled={readOnly} block />
</SplitControl>

<SplitControl
	label={I18n.t('console.approve.whyPost')}
	caption={I18n.t('console.approve.whyPostCaption')}
>
	<Textarea bind:value={whyPost} maxLength={1024} disabled={readOnly} block />
</SplitControl>

{#if !$approvalStore}
	<div class="checkboxes">
		<Checkbox bind:checked={checkbox1}>
			<I18n.T key="console.approve.checkbox1" params={{ strong: { element: 'strong' } }} />
		</Checkbox>
		<Checkbox bind:checked={checkbox2}>
			<I18n.T
				key="console.approve.checkbox2"
				params={{
					termsLink: {
						element: 'a',
						props: {
							href: '/terms',
							target: '_blank',
							class: 'hds-link'
						}
					},
					privacyLink: {
						element: 'a',
						props: {
							href: '/privacy',
							target: '_blank',
							class: 'hds-link'
						}
					}
				}}
			/>
		</Checkbox>
	</div>
{/if}

<div class="submit">
	<Button size="medium" on:click={onSubmit} disabled={$approvalStore && !isUpdating}>
		{$approvalStore ? I18n.t('console.approve.update') : I18n.t('console.approve.submit')}
	</Button>
</div>

<style>
	.submit {
		display: flex;
		justify-content: center;
		margin: 20px 0 30px 0;
	}
	.checkboxes {
		margin: 20px auto;
		width: 700px;
		max-width: 100%;
		display: flex;
		flex-direction: column;
		gap: 10px;
	}
	.checkboxes :global(.placeholder) {
		flex-shrink: 0;
	}
</style>
