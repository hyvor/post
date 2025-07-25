<script lang="ts">
    import { SplitControl, Textarea, TextInput, Button, toast, Callout } from "@hyvor/design/components";
    import IconBell from "@hyvor/icons/IconBell";
    import IconExclamationCircle from "@hyvor/icons/IconExclamationCircle";
    import { getI18n } from "../../lib/i18n";
    import { createApproval, updateApproval } from "../../lib/actions/approvalActions";
    import { approvalStore, userApprovalStatusStore } from "../../lib/stores/consoleStore";
    import type { Approval } from "../../types";

    const I18n = getI18n();

    let approval: Approval = $state($approvalStore);

    let companyName: string = $state(approval?.company_name || "");
    let country: string = $state(approval?.country || "");
    let website: string = $state(approval?.website || "");
    let socialLinks: string = $state(approval?.social_links || "");
    let typeOfContent: string = $state(approval?.type_of_content || "");
    let frequency: string = $state(approval?.frequency || "");
    let existingList: string = $state(approval?.existing_list || "");
    let sample: string = $state(approval?.sample || "");
    let whyPost: string = $state(approval?.why_post || "");

    let error: string | undefined = $state(undefined);

    function validate() {
        if (companyName === "") {
            error = I18n.t("console.approve.companyNameRequired");
            return false;
        }
        if (country === "") {
            error = I18n.t("console.approve.countryRequired");
            return false;
        }
        if (website === "") {
            error = I18n.t("console.approve.websiteRequired");
            return false;
        }
        try {
            new URL(website);
        } catch {
            error = I18n.t("console.approve.websiteInvalid");
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
                type_of_content: typeOfContent !== (approval.type_of_content ?? '') ? typeOfContent : null,
                frequency: frequency !== (approval.frequency ?? '') ? frequency : null,
                existing_list: existingList !== (approval.existing_list ?? '') ? existingList : null,
                sample: sample !== (approval.sample ?? '') ? sample : null,
                why_post: whyPost !== (approval.why_post ?? '') ? whyPost : null
            })
                .then((data) => {
                    approvalStore.set(data);
                    toast.success(I18n.t("console.approve.updatedInfo"));
                })
                .catch(() => {
                    toast.error(I18n.t("console.approve.error"));
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
                    toast.success(I18n.t("console.approve.submittedForApproval"));
                })
                .catch(() => {
                    toast.error(I18n.t("console.approve.error"));
                });
        }
    }

    let isUpdating: boolean = $state(false);

    $effect(() => {
        isUpdating = !!(approval) && (
            companyName !== approval.company_name
            || country !== approval.country
            || website !== approval.website
            || socialLinks !== (approval.social_links ?? "")
            || typeOfContent !== (approval.type_of_content ?? "")
            || frequency !== (approval.frequency ?? "")
            || existingList !== (approval.existing_list ?? "")
            || sample !== (approval.sample ?? "")
            || whyPost !== (approval.why_post ?? "")
        )
    });

    let readOnly: boolean = $state(false);

    $effect(() => {
        readOnly = !!(approval) && approval.status === "rejected";
    });
</script>

{#if $approvalStore?.status === "reviewing"}
    <Callout
        type="info"
    >
        {#snippet icon()}
            <IconBell />
        {/snippet}
        {I18n.t("console.approve.reviewNotice")}
    </Callout>
{:else if $approvalStore?.status === "rejected"}
    <Callout
        type="warning"
    >
        {#snippet icon()}
            <IconExclamationCircle />
        {/snippet}
        {I18n.t("console.approve.rejectNotice")}
    </Callout>
{/if}

<SplitControl label={I18n.t("console.approve.companyName")} caption={`(${I18n.t("console.approve.required")})`}>
    <TextInput
        bind:value={companyName}
        disabled={readOnly}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.country")} caption={`(${I18n.t("console.approve.required")})`}>
    <TextInput
        bind:value={country}
        disabled={readOnly}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.website")} caption={`(${I18n.t("console.approve.required")})`}>
    <TextInput
        type="url"
        bind:value={website}
        disabled={readOnly}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.socialLinks")} caption={`(${I18n.t("console.approve.preferred")})`}>
    <Textarea
        bind:value={socialLinks}
        disabled={readOnly}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.typeOfContent")} caption={I18n.t("console.approve.typeOfContentCaption")}>
    <TextInput
        bind:value={typeOfContent}
        disabled={readOnly}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.frequency")} caption={I18n.t("console.approve.frequencyCaption")}>
    <TextInput
        placeholder={I18n.t("console.approve.frequencyPlaceholder")}
        bind:value={frequency}
        disabled={readOnly}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.existingList")} caption={I18n.t("console.approve.existingListCaption")}>
    <Textarea
        bind:value={existingList}
        disabled={readOnly}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.sample")} caption={I18n.t("console.approve.sampleCaption")}>
    <TextInput
        bind:value={sample}
        disabled={readOnly}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.whyPost")} caption={I18n.t("console.approve.whyPostCaption")}>
    <Textarea
        bind:value={whyPost}
        disabled={readOnly}
        block
    />
</SplitControl>

<div class="submit">
    <Button
        size="medium"
        on:click={onSubmit}
        disabled={$approvalStore && !isUpdating}
    >
        {$approvalStore ? I18n.t("console.approve.update") : I18n.t("console.approve.submit")}
    </Button>
</div>

<style>
    .submit{
        display: flex;
        justify-content: center;
        margin: 20px 0 30px 0;
    }
</style>

