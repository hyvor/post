<script lang="ts">
    import { onMount } from "svelte";
    import Skeleton from "./Skeleton.svelte";
    import { fade, slide } from "svelte/transition";
    import { apiFromInstance } from "./api";
    import type { List, Project } from "./types";

    interface Props {
        projectUuid: string;
        instance: string;
    }

    let { projectUuid, instance }: Props = $props();
    let loading = $state(true);

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
            })
            .finally(() => {
                loading = false;
            });
    });

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
                    <div class="list-checkbox">
                        <input type="checkbox" />
                    </div>
                </label>
            {/each}
        </div>

        <div class="input">
            <input
                type="email"
                name="email"
                placeholder="Your Email"
                class="email-input"
            />
            <button>
                {project.form.button_text || "Subscribe"}
            </button>
        </div>

        <div transition:slide={laterElementsAnimation}>
            {#if project.form.footer_text}
                <div class="footer">
                    {@html project.form.footer_text}
                </div>
            {/if}
        </div>
    </div>
{/if}

<style>
    .title.hidden,
    .lists.hidden,
    .description.hidden {
        display: none;
    }

    .form {
        width: 425px;
        margin: 0 auto;
        max-width: 100%;
    }
    .form :global(*) {
        box-sizing: border-box;
    }
    .title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .description {
        margin-top: -0.8rem;
        font-size: 16px;
        color: #666;
        margin-bottom: 1.2rem;
    }

    .input {
        position: relative;
    }
    .email-input {
        padding: 10px 25px;
        border: none;
        background-color: #fff;
        border-radius: 20px;
        box-shadow: 0 0 8px #0000001c;
        border-radius: 20px;
        width: 100%;
        font-family: inherit;
    }
    button {
        position: absolute;
        right: 3px;
        top: 50%;
        transform: translateY(-50%);
        padding: 0 25px;
        background-color: #000;
        color: #fff;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        height: calc(100% - 6px);
        font-family: inherit;
        z-index: 1;
    }

    .lists {
        margin-bottom: 1.2rem;
    }

    .footer {
        padding-top: 1rem;
        font-size: 14px;
        color: #666;
    }

    .list {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8em;
        cursor: pointer;
    }

    .list-name {
        font-size: 16px;
        font-weight: 600;
    }

    .list-description {
        font-size: 14px;
        color: #666;
    }
</style>
