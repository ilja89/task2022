<template>
    <div class="card  has-padding">
        <h3 class="title  is-3">
            {{similarity.name}}
        </h3>

        <p>
            Link:
            <a
                :href="similarity.link"
                target="_blank"
                rel="noopener noreferrer"
            >
                {{similarity.link}}
            </a>
        </p>

        <table class="table is-fullwidth">
            <tr>
                <th>Similarity</th>
                <th>First resource</th>
                <th>Second resource</th>
            </tr>
            <tr v-for="result in similarity.results">
                <td>{{result.similarity}}</td>
                <td>{{result.firstResource.name}}</td>
                <td>{{result.secondResource.name}}</td>
            </tr>
        </table>
    </div>
</template>

<script>
    export default {
        name: 'plagiarism-similarities-table',
        props: {
            similarity: {
                type: Object,
            },
        },
    }
</script>
