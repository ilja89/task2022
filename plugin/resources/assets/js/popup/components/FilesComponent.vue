<template>
    <div>

        <p class="control" v-if="files.length > 0">
            <span class="select">

                <select name="file"
                        v-model="activeFileId">
                    <option v-for="file in files"
                            :value="file.id">
                        {{ file.path }}
                    </option>
                </select>

            </span>
        </p>

        <pre v-if="activeFile !== null">
            <code :class="testerType">{{ activeFile.contents }}</code>
        </pre>

    </div>
</template>

<script>

    import File from '../../models/File';

    export default {

        props: {
            submission: { required: true },
            testerType: { required: true },
        },

        data() {
            return {
                files: [],
                activeFileId: null,
            };
        },

        computed: {
            activeFile() {
                if (this.files.length === 0) {
                    return null;
                }

                return this.files.find(file => {
                    return file.id === this.activeFileId;
                });
            }
        },

        watch: {
            submission() {
                this.getFiles();
            }
        },

        mounted() {
            this.getFiles();
        },

        methods: {
            getFiles() {
                File.findBySubmission(this.submission.id, files => {
                    this.files = files;
                    if (files.length > 0) {
                        this.activeFileId = files[0].id;
                    }
                });
            }
        }
    }
</script>
