<script lang="ts">
	import { SplitControl, Textarea, TextInput, Button } from "@hyvor/design/components";
    import {getI18n} from "../../lib/i18n";

    const I18n = getI18n();

</script>

<SplitControl label={I18n.t("console.approve.companyName")} caption={`(${I18n.t("console.approve.required")})`}>
    <TextInput block/>
</SplitControl>

<SplitControl label={I18n.t("console.approve.country")} caption={`(${I18n.t("console.approve.required")})`}>
    <TextInput block/>
</SplitControl>

<SplitControl label={I18n.t("console.approve.website")} caption={`(${I18n.t("console.approve.required")})`}>
    <TextInput
        type="url"
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.socialLinks")} caption={`(${I18n.t("console.approve.preferred")})`}>
    <Textarea block/>
</SplitControl>

<SplitControl label={I18n.t("console.approve.typeOfContent")} caption={I18n.t("console.approve.typeOfContentCaption")}>
    <TextInput block/>
</SplitControl>

<SplitControl label={I18n.t("console.approve.frequency")} caption={I18n.t("console.approve.frequencyCaption")}>
    <TextInput
        placeholder={I18n.t("console.approve.frequencyPlaceholder")}
        block
    />
</SplitControl>

<SplitControl label={I18n.t("console.approve.emailList")} caption={I18n.t("console.approve.emailListCaption")}>
    <Textarea block/>
</SplitControl>

<SplitControl label={I18n.t("console.approve.sample")} caption={I18n.t("console.approve.sampleCaption")}>
    <TextInput type="url" block/>
</SplitControl>

<SplitControl label={I18n.t("console.approve.whyPost")} caption={I18n.t("console.approve.whyPostCaption")}>
    <Textarea block/>
</SplitControl>

<div class="submit">
    <Button size="medium">
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

