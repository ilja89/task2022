<template>
    <charon-tabs
        class="card popup-tabs"
        id="tabs"
    >

        <charon-tab
            v-for="(similarity, index) in similarities"
            :name="similarity.name"
            :key="similarity.name"
            :selected="index === 0"
        >
            <h4
                class="title  is-4  has-text-centered  state-message"
                v-if="similarity.state === 'PLAGIARISM_SERVICE_FAILED'"
            >
                Plagiarism service
                <strong class="has-text-weight-semibold">
                    {{similarity.name}}
                </strong>
                has failed.
            </h4>
            <h4
                class="title  is-4  has-text-centered  state-message"
                v-if="similarity.state === 'PLAGIARISM_SERVICE_PROCESSING'"
            >
                Plagiarism service
                <strong class="has-text-weight-semibold">
                    {{similarity.name}}
                </strong>
                is processing.
            </h4>
            <div
                v-if="similarity.state === 'PLAGIARISM_SERVICE_SUCCESS'"
            >
                <h5 class="title  is-5  similarity-link">
                    <a
                        :href="similarity.link"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Link to service
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path
                                d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z" />
                        </svg>
                    </a>
                </h5>

                <table class="table is-fullwidth is-striped">
                    <thead>
                    <tr>
                        <th>Similarity</th>
                        <th>First resource</th>
                        <th>Second resource</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="result in similarity.results">
                        <td>{{result.similarity}}</td>
                        <td>{{result.firstResource.name}}</td>
                        <td>{{result.secondResource.name}}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </charon-tab>

    </charon-tabs>
</template>

<script>
    import { CharonTab, CharonTabs } from '../../../components/partials'

    export default {
        name: 'plagiarism-similarities-tabs',
        components: { CharonTab, CharonTabs },
        props: {
            similarities: {
                type: Array,
            },
        },
    }
</script>

<style lang="scss" scoped>

    .similarity-link a {
        svg {
            display: inline-block;
            vertical-align: middle;
            width: 1rem;
            height: 1rem;
        }
    }

    .state-message {
        margin: 2rem 0;
    }

</style>
