<template>
    <div>

        <p class="control files-select" v-if="files.length > 0">
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

        <div class="columns code-container" :class="{ 'is-round': isRound }" v-if="activeFile !== null">
            <div class="column line-number-container is-narrow">
                <span class="line-number-position" v-for="n in activeFile.numbers">
                    <span class="line-number">{{ n }}</span>
                </span>
            </div>
            <pre class="column code" v-highlightjs="activeFile.contents"><code :class="testerType"></code></pre>
        </div>

    </div>
</template>

<script>

    import File from '../../models/File';

    export default {

        props: {
            submission: { required: true },
            testerType: { required: true },
            isRound: {
                type: Boolean,
                default: true,
            }
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

                return {
                    id: file.id,
                    path: file.path,
                    contents: file.contents.trim(),
                    numbers: file.contents.trim().split(/\r\n|\r|\n/).length,
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

<style lang="scss" scoped>
    .line-number-position {
        position: relative;
        top: 1px;
        height: 20px;
    }

    .line-number {
        float: right;
        font-size: 12px;
        padding-left: 10px;
        padding-right: 10px;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro', monospace;
    }

    .columns.code-container {
        position: relative;
        margin: 0;
        padding: 0;

        .line-number-container {
            display: flex;
            flex-direction: column;
            background: darken(#fafafa, 5%);
            border: 1px solid #dbdbdb;
            padding: 1.25rem 0;
        }
    }

    pre.code {
        padding: 0;
        border: 1px solid #dbdbdb;
        margin-left: -1px;
        overflow-x: scroll;

        code {
            overflow-x: scroll;
            padding: 1.25rem;
        }
    }

    .code-container.is-round {

        .line-number-container {
            border-bottom-left-radius: 5px;
            border-top-left-radius: 5px;
        }

        .code {
            border-top-right-radius: 5px;
            border-bottom-right-radius: 5px;

            pre {
                /* Otherwise this overlays the .code border radius (weird tips) */
                border-top-right-radius: 5px;
                border-bottom-right-radius: 5px;
            }
        }
    }

    @media (max-width: 768px) {
        .columns.code-container {

            .code code {
                padding-left: 1.25rem;
            }

            .line-number-container {
                display: none;
            }
        }
    }
</style>
