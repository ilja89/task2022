<template>
    <div>

        <p class="control output-tab-select" v-if="files.length > 0">
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

        <pre v-if="activeFile !== null" v-highlightjs="activeFile.contents">
            <code :class="testerType"></code>
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

                let file = this.files.find(file => {
                    return file.id === this.activeFileId;
                });
                let line = 1;
                let contents = file.contents.replace(/^/gm, function() {
                    return '<span class="line-number-position"><span class="line-number" data-pseudo-content="' + line++ + ' |"></span></span>';
                });

                return {
                    id: file.id,
                    path: file.path,
                    contents: contents,
                }
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

<style lang="scss">
    [data-pseudo-content]::before,
    [data-pseudo-content--before]::before,
    [data-pseudo-content--after]::after {
        content: attr(data-pseudo-content);
    }

    .line-number-position {
        position: relative;
        top: 3px;
    }

    .line-number {
        position: absolute;
        text-align: right;
        right: 17px;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro', monospace;
        font-size: 12px;
    }
</style>
