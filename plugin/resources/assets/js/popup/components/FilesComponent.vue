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

        <div class="columns code-container" v-if="activeFile !== null">
            <div class="column line-number-container" v-html="activeFile.numbers"></div>
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
                let numbers = file.contents.trim().replace(/^.*$/gm, function() {
                    return '<span class="line-number-position"><span class="line-number" data-pseudo-content="' + line++ + '"></span></span>';
                });

                return {
                    id: file.id,
                    path: file.path,
                    contents: file.contents,
                    numbers: numbers,
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
        top: 1px;
        height: 20px;
    }

    .line-number {
        position: absolute;
        text-align: right;
        right: 10px;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro', monospace;
        font-size: 12px;
    }

    .columns.code-container {
        position: relative;
        margin: 0;
        padding: 0;

        .line-number-container {
            display: flex;
            flex-direction: column;
            width: 40px;
            position: absolute;
            background: darken(#fafafa, 5%);
            border: 1px solid #dbdbdb;
            padding: 1.25rem 0 1.25rem 0;
            border-bottom-left-radius: 5px;
            border-top-left-radius: 5px;
        }
    }

    pre.code {
        padding: 0;
        border: 1px solid #dbdbdb;
        border-radius: 5px;

        code {
            /* Otherwise this overlays the .code border radius (weird tips) */
            border-radius: 5px;
            overflow-x: scroll;
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
