<script lang="ts">
    import { onMount } from "svelte";
    import Skeleton from "./Skeleton.svelte";
    import { fade, slide } from "svelte/transition";
    import { apiFromInstance } from "./api";
    import type { List, Project } from "./types";
    import ListRow from "./ListRow.svelte";

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
        {#if project.form.title}
            <div class="title">
                {project.form.title}
            </div>
        {/if}

        <div transition:slide={laterElementsAnimation}>
            {#if lists.length > 1}
                <div class="lists">
                    {#each lists as list}
                        <ListRow {list} />
                    {/each}
                </div>
            {/if}
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
        animation: fade-in 0.6s ease-in-out;
    }
    .input {
        position: relative;
        animation: fade-in 0.6s ease-in-out;
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
        margin-top: 1rem;
        font-size: 14px;
        color: #666;
    }

    /* fade in */
    @keyframes fade-in {
        0% {
            opacity: 0;
        }
        100% {
            opacity: 1;
        }
    }
</style>
