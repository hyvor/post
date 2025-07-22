<script lang="ts">
    import {SplitControl, Textarea, TextInput, Button, toast} from "@hyvor/design/components";
    import {getI18n} from "../../lib/i18n";
    import {createApproval} from "../../lib/actions/approvalActions";

    const I18n = getI18n();

    let companyName: string = $state("");
    let country: string = $state("");
    let website: string = $state("");
    let socialLinks: string = $state("");
    let typeOfContent: string = $state("");
    let frequency: string = $state("");
    let existingList: string = $state("");
    let sample: string = $state("");
    let whyPost: string = $state("");

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
            .then(() => {
                toast.success('Submitted');
            });
    }
</script>

<SplitControl label={I18n.t("console.approve.companyName")} caption={`(${I18n.t("console.approve.required")})`}>
    <TextInput
        bind:value={companyName}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.country")} caption={`(${I18n.t("console.approve.required")})`}>
    <TextInput
        bind:value={country}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.website")} caption={`(${I18n.t("console.approve.required")})`}>
    <TextInput
        type="url"
        bind:value={website}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.socialLinks")} caption={`(${I18n.t("console.approve.preferred")})`}>
    <Textarea
        bind:value={socialLinks}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.typeOfContent")} caption={I18n.t("console.approve.typeOfContentCaption")}>
    <TextInput
        bind:value={typeOfContent}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.frequency")} caption={I18n.t("console.approve.frequencyCaption")}>
    <TextInput
        placeholder={I18n.t("console.approve.frequencyPlaceholder")}
        bind:value={frequency}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.existingList")} caption={I18n.t("console.approve.existingListCaption")}>
    <Textarea
        bind:value={existingList}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.sample")} caption={I18n.t("console.approve.sampleCaption")}>
    <TextInput
        bind:value={sample}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.whyPost")} caption={I18n.t("console.approve.whyPostCaption")}>
    <Textarea
        bind:value={whyPost}
        block
    />
</SplitControl>

<div class="submit">
    <Button
        size="medium"
        on:click={onSubmit}
    >
        {I18n.t("console.approve.submit")}
    </Button>
</div>

<style>
    .submit{
        display: flex;
        justify-content: center;
        margin: 20px 0 30px 0;
    }
</style>

