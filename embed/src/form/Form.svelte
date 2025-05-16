<script lang="ts">
    import { onMount } from "svelte";
    import Skeleton from "./Skeleton.svelte";
    import { fade, slide } from "svelte/transition";
    import { apiFromInstance } from "./api";
    import type { List, Project } from "./types";
    import Switch from "./Switch.svelte";
    import Message from "./Message.svelte";

    interface Props {
        projectUuid: string;
        instance: string;
        shadowRoot: ShadowRoot;
    }

    let { projectUuid, instance, shadowRoot }: Props = $props();
    let email = $state("");
    let selectedListsIds: number[] = $state([]);
    let loading = $state(true);
    let focused = $state(false);
    let subscribing = $state(false);
    let subscribingSuccess = $state(false);
    let subscribingError = $state("");

    let project: Project = $state({} as Project);
    let lists: List[] = $state([]);

    const api = apiFromInstance(instance);

    interface InitResponse {
        project: Project;
        lists: List[];
    }

    onMount(() => {
        api<InitResponse>("/init", {
            project_uuid: projectUuid,
        })
            .then((response) => {
                project = response.project;
                lists = response.lists;
                selectedListsIds = lists.map((list) => list.id);

                setCustomCss();
            })
            .catch((err) => {
                console.error("Error loading Hyvor Post form:", err);
            })
            .finally(() => {
                loading = false;
            });
    });

    function onSubscribe(e: Event) {
        e.preventDefault();

        subscribing = true;
        subscribingSuccess = false;
        subscribingError = "";

        api<InitResponse>("/subscribe", {
            project_uuid: projectUuid,
            email,
            list_ids: selectedListsIds,
        })
            .then((response) => {
                console.log("Subscribed successfully:", response);

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
        if (project.form.custom_css) {
            const style = document.createElement("style");
            style.textContent = project.form.custom_css;
            shadowRoot.appendChild(style);
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
{:else}
    <div class="form">
        <div
            class="title"
            transition:fade={firstElementsAnimation}
            class:hidden={!project.form.title}
        >
            {@html project.form.title}
        </div>

        <div
            class="description"
            transition:fade={firstElementsAnimation}
            class:hidden={!project.form.description}
        >
            {@html project.form.description}
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
                    <Switch checked={true} />
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
                {project.form.button_text || "Subscribe"}
            </button>
        </form>

        {#if subscribingSuccess}
            <Message
                message={project.form.success_message ||
                    "Thank you for subscribing!"}
                type="success"
            />
        {/if}

        {#if subscribingError}
            <Message message={subscribingError} type="error" />
        {/if}

        <div transition:slide={laterElementsAnimation}>
            {#if project.form.footer_text}
                <div class="footer">
                    {@html project.form.footer_text}
                </div>
            {/if}
        </div>
    </div>
{/if}
