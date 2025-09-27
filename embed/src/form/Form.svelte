<script lang="ts">
    import { onMount } from "svelte";
    import Skeleton from "./Skeleton.svelte";
    import { fade, slide } from "svelte/transition";
    import { apiFromInstance } from "./api";
    import type { List, Palette, Newsletter } from "./types";
    import Switch from "./Switch.svelte";
    import Message from "./Message.svelte";

    interface Props {
        newsletterSubdomain: string;
        instance: string;
        shadowRoot: ShadowRoot;
    }

    let { newsletterSubdomain, instance, shadowRoot }: Props = $props();

    let initError = $state("");
    let email = $state("");
    let selectedListsIds: number[] = $state([]);
    let loading = $state(true);
    let focused = $state(false);
    let subscribing = $state(false);
    let subscribingSuccess = $state(false);
    let subscribingError = $state("");

    let newsletter: Newsletter = $state({} as Newsletter);
    let lists: List[] = $state([]);
    let palette = $state({} as Palette);

    const api = apiFromInstance(instance);

    interface InitResponse {
        newsletter: Newsletter;
        lists: List[];
    }

    function doInit() {
        loading = true;
        initError = "";

        api<InitResponse>("/init", {
            newsletter_subdomain: newsletterSubdomain,
        })
            .then((response) => {
                newsletter = response.newsletter;
                lists = response.lists;
                selectedListsIds = lists.map((list) => list.id);
                palette = newsletter.palette_light;

                setCustomCss();
            })
            .catch((err) => {
                initError = err.message || "Unable to load signup form.";
            })
            .finally(() => {
                loading = false;
            });
    }

    onMount(doInit);

    function onSubscribe(e: Event) {
        e.preventDefault();

        subscribing = true;
        subscribingSuccess = false;
        subscribingError = "";

        api<InitResponse>("/subscribe", {
            newsletter_subdomain: newsletterSubdomain,
            email,
            list_ids: selectedListsIds,
        })
            .then(() => {
                subscribingSuccess = true;
                setTimeout(() => {
                    subscribingSuccess = false;
                }, 5000);
            })
            .catch((err) => {
                console.error("Error loading Hyvor Post form:", err);
                subscribingError = err.message || "An error occurred";
            })
            .finally(() => {
                subscribing = false;
            });
    }

    function setCustomCss() {
        if (newsletter.form.custom_css) {
            const style = document.createElement("style");
            style.textContent = newsletter.form.custom_css;
            shadowRoot.appendChild(style);
        }
    }

    function handleListSwitch(listId: number) {
        return (event: Event) => {
            const checkbox = event.target as HTMLInputElement;
            if (checkbox.checked) {
                selectedListsIds.push(listId);
            } else {
                selectedListsIds = selectedListsIds.filter(
                    (id) => id !== listId,
                );
                if (selectedListsIds.length === 0) {
                    // Select the next list ID
                    const nextList = lists.find((list) => list.id !== listId);
                    if (nextList) {
                        selectedListsIds = [nextList.id];
                    }
                }
            }
        };
    }

    export function setPalette(type: "light" | "dark") {
        if (type === "dark") {
            palette = newsletter.palette_dark;
        } else {
            palette = newsletter.palette_light;
        }
    }

    /**
     * Firstly loaded elements
     * The heading and the input
     * Fades in
     */
    const firstElementsAnimation = {
        duration: 600,
    };

    /**
     * Later loaded elements
     * The lists and the footer
     * Slides in
     * The delay is set to the duration of the first elements animation
     */
    const laterElementsAnimation = {
        duration: 400,
        delay: firstElementsAnimation.duration,
    };
</script>

{#if loading}
    <Skeleton />
{:else if initError}
    <div class="form">
        <Message message={initError} type="error" />

        <div style="text-align:center;margin-top:10px;">
            <button style="padding: 6px 15px;" onclick={doInit}>
                Reload
            </button>
        </div>
    </div>
{:else}
    <div
        class="form"
        style="
            --hp-text: {palette.text};
            --hp-accent: {palette.accent};
            --hp-accent-text: {palette.accent_text};
            --hp-input: {palette.input};
            --hp-input-text: {palette.input_text};
            --hp-input-box-shadow: {palette.input_box_shadow};
            --hp-input-border: {palette.input_border};
            --hp-border-radius: {palette.border_radius}px;

            --hp-accent-light: color-mix(in srgb, var(--hp-accent), transparent 90%);
            --hp-text-light: color-mix(in srgb, var(--hp-text), transparent 50%);
            --hp-link: var(--hp-accent);
        "
    >
        <div
            class="title"
            transition:fade={firstElementsAnimation}
            class:hidden={!newsletter.form.title}
        >
            {@html newsletter.form.title}
        </div>

        <div
            class="description"
            transition:fade={firstElementsAnimation}
            class:hidden={!newsletter.form.description}
        >
            {@html newsletter.form.description}
        </div>

        <div
            class="lists"
            transition:slide={laterElementsAnimation}
            class:hidden={lists.length === 0}
        >
            {#each lists as list (list.id)}
                <label class="list">
                    <div class="list-name-description">
                        <div class="list-name">{list.name}</div>
                        <div class="list-description">{list.description}</div>
                    </div>
                    <Switch
                        checked={selectedListsIds.includes(list.id)}
                        onchange={handleListSwitch(list.id)}
                        disabled={subscribing}
                    />
                </label>
            {/each}
        </div>

        <form class="input" class:focused onsubmit={onSubscribe}>
            <input
                type="email"
                name="email"
                required
                placeholder="Your Email"
                class="email-input"
                onfocus={() => (focused = true)}
                onblur={() => (focused = false)}
                bind:value={email}
            />
            <button type="submit" disabled={subscribing}>
                {newsletter.form.button_text || "Subscribe"}
            </button>
        </form>

        {#if subscribingSuccess}
            <Message
                message={newsletter.form.success_message ||
                    "Please check your email to confirm your subscription."}
                type="success"
            />
        {/if}

        {#if subscribingError}
            <Message message={subscribingError} type="error" />
        {/if}

        <div transition:slide={laterElementsAnimation}>
            {#if newsletter.form.footer_text}
                <div class="footer">
                    {@html newsletter.form.footer_text}
                </div>
            {/if}
        </div>
    </div>
{/if}
