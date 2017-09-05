<template>
    <div>

        <p class="control tabs-right select-container files-select" v-if="files.length > 0">
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

        <div class="columns is-gapless code-container" :class="{ 'is-round': isRound }" v-if="activeFile !== null">

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

    import { File } from '../../models';

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
                    contents: file.contents.trim().replace(/</g, '&lt;').replace(/>/g, '&gt;'),
                    numbers: file.contents.trim().split(/\r\n|\r|\n/).length,
                }
            },

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
                    this.files = files
                    this.formattedFiles = []

                    if (files.length > 0) {
                        this.activeFileId = files[0].id
                    }
                })
            },

            handleFileClicked(file) {
                this.activeFileId = file.id
            },
        }
    }
</script>

<style lang="scss" scoped>

    $code-font-size: 14px;
    $code-line-height: 23px;

    .line-number {
        float: right;
        padding-left: 10px;
        padding-right: 10px;
        font-size: $code-font-size;
        line-height: $code-line-height;
        font-family: monospace;
    }

    .columns.code-container {

        .line-number-container {
            display: flex;
            flex-direction: column;
            background: darken(#fafafa, 5%);
            border: 1px solid #dbdbdb;
            padding-top: 1.25rem;
        }

        .file-tree-container {
            overflow: auto;
        }
    }

    pre.code {
        border: 1px solid #dbdbdb;
        border-left: none;
        overflow-x: scroll;
        background-color: #fafafa;

        code {
            padding: 1.25rem 1.25rem 1.25rem 0.5rem;
            line-height: $code-line-height;
            font-size: $code-font-size;
            font-family: monospace;
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
