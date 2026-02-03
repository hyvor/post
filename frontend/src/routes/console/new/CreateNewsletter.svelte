<script lang="ts">
    import {goto} from '$app/navigation';
    import {
        FormControl,
        SplitControl,
        TextInput,
        toast,
        Validation
    } from '@hyvor/design/components';
    import {addUserNewsletter, userNewslettersStore} from '../lib/stores/userNewslettersStore';
    import {createNewsletter, getSubdomainAvailability} from '../lib/actions/newsletterActions';
    import {validateSubdomain} from '../lib/subdomain';
    import {getArchiveUrlAsUrl} from '../lib/archive';
    import {setNewsletterStoreByNewsletterList} from '../lib/stores/newsletterStore';
    import type {NewsletterList} from '../types';
    import {ResourceCreator} from '@hyvor/design/cloud';

    let name = $state('');
    let subdomain = $state('');

    let subdomainEdited = false;

    let nameError: string | null = $state(null);
    let subdomainError: string | null = $state(null);
    let subdomainSuccess: string | null = $state(null);

    let isCreating = $state(false);

    let subdomainCheckTimeout: null | ReturnType<typeof setTimeout> = null;
    let subdomainCheckAbortController: AbortController | null = null;

    function checkSubdomain() {
        if (subdomainCheckTimeout) {
            clearTimeout(subdomainCheckTimeout);
        }
        if (subdomainCheckAbortController) {
            subdomainCheckAbortController.abort();
        }

        subdomainError = null;
        subdomainSuccess = null;

        if (!subdomain) return;

        subdomainCheckTimeout = setTimeout(() => {
            subdomainCheckAbortController = new AbortController();

            getSubdomainAvailability(subdomain).then((res) => {
                if (res.available) {
                    subdomainSuccess = 'Subdomain is available';
                } else {
                    subdomainError = 'Subdomain is already taken';
                }
            });
        }, 500);
    }

    $effect(() => {
        subdomain;
        checkSubdomain();
    });

    function handleBack() {
        if ($userNewslettersStore.length > 0) {
            goto('/console');
        } else {
            goto('/');
        }
    }

    function handleNameInput(e: any) {
        nameError = null;

        const value = e.target.value;

        if (!subdomainEdited) {
            subdomain = value
                .toLowerCase()
                .replace(/[^a-z0-9-]/g, '-')
                .replace(/-+/g, '-')
                .replace(/(^-|-$)/g, '');
        }
    }

    function handleSubdomainInput(e: any) {
        subdomainEdited = true;
        subdomainError = null;

        subdomain = e.target.value;
        subdomain = subdomain.toLowerCase();

        const subdomainValidation = validateSubdomain(subdomain);

        if (subdomainValidation) {
            subdomainError = subdomainValidation;

            if (subdomainCheckTimeout) {
                clearTimeout(subdomainCheckTimeout);
            }
        }
    }

    async function handleCreate(): Promise<boolean> {
        let valid = true;

        if (name.trim() === '') {
            nameError = 'Name is required';
            valid = false;
        }

        if (subdomain.trim() === '') {
            subdomainError = 'Subdomain is required';
            valid = false;
        }

        if (!valid) {
            return false;
        }

        isCreating = true;

        try {
            const res = await createNewsletter(name, subdomain);
            const list: NewsletterList = {role: 'owner', newsletter: res};
            addUserNewsletter(list);
            setNewsletterStoreByNewsletterList(list);
            goto('/console/' + res.subdomain);
            return true;
        } catch (e: any) {
            toast.error(e.message);
            isCreating = false;
            return false;
        }
    }
</script>

<ResourceCreator
        title="Start a new newsletter"
        resourceTitle="Newsletter"
        cta="Create Newsletter"
        onback={handleBack}
        oncreate={handleCreate}
        ctaDisabled={name.trim() === '' || subdomain.trim() === ''}
>
    <SplitControl label="Name" caption="A name for your newsletter">
        <FormControl>
            <TextInput
                    block
                    bind:value={name}
                    on:input={handleNameInput}
                    on:keydown={(e) => e.key === 'Enter' && handleCreate()}
                    maxlength={255}
                    state={nameError ? 'error' : undefined}
                    autofocus
            />

            {#if nameError}
                <Validation state="error">
                    {nameError}
                </Validation>
            {/if}
        </FormControl>
    </SplitControl>
    <SplitControl label="Subdomain" caption="Only a-z, 0-9, and hyphens (-)">
        <FormControl>
            <TextInput
                    block
                    bind:value={subdomain}
                    on:input={handleSubdomainInput}
                    maxlength={50}
                    state={subdomainError
								? 'error'
								: subdomainSuccess
									? 'success'
									: undefined}
            >
                {#snippet end()}
								<span class="archive-hostname"
                                >.{getArchiveUrlAsUrl().hostname}</span
                                >
                {/snippet}
            </TextInput>

            {#if subdomainError}
                <Validation state="error">
                    {subdomainError}
                </Validation>
            {/if}

            {#if subdomainSuccess}
                <Validation state="success">
                    {subdomainSuccess}
                </Validation>
            {/if}
        </FormControl>
    </SplitControl>
</ResourceCreator>

<style>
    .archive-hostname {
        color: var(--text-light);
        font-size: 14px;
        font-weight: normal;
    }
</style>
