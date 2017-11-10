<template>
    <div class="columns is-gapless code-container" :class="{ 'is-round': isRound }" v-if="activeFile !== null">

        <div class="column is-narrow file-tree-container is-one-quarter">
            <file-tree :data="formattedFiles" @file-clicked="handleFileClicked">
            </file-tree>
        </div>

        <div class="column line-number-container is-narrow">
            <span class="line-number-position" v-for="n in activeFile.numbers">
                <span class="line-number">{{ n }}</span>
            </span>
        </div>

        <div class="column code-column">
            <div
                    class="code-copy-container"
                    :class="{ sticky: codeCopySticky }"
                    id="code-copy-container"
                    @click="handleCopyClicked"
            >
                <div class="code-copy">
                    Copy code
                </div>
            </div>
            <pre class="code" v-highlightjs="activeFile.contents"><code :class="testerType"></code></pre>
        </div>
    </div>
</template>

<script>

    import { File } from '../../models';
    import FileTree from './FileTree'

    export default {

        components: { FileTree },

        props: {
            submission: { required: true },
            testerType: { required: true },
            isRound: {
                type: Boolean,
                default: true,
            },
        },

        data() {
            return {
                files: [],
                activeFileId: null,
                formattedFiles: [],
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

                    this.files.forEach(file => {
                        this.addFormattedFile(file)
                    })

                    if (files.length > 0) {
                        this.activeFileId = files[0].id
                    }
                })
            },

            handleFileClicked(file) {
                this.activeFileId = file.id
            },

            addFormattedFile(file) {
                let path = file.path
                let pathArray = []

                while (path.includes('/')) {
                    const slashIndex = path.indexOf('/')
                    const beforeSlash = path.substring(0, slashIndex)
                    path = path.substring(slashIndex + 1)
                    pathArray.push(beforeSlash)
                }

                let currentContext = this.formattedFiles
                while (pathArray.length) {
                    const currentFolder = pathArray.shift()
                    const hasFolder = currentContext.find((context) => {
                        return context.title == currentFolder
                    })

                    if (hasFolder) {
                        currentContext = hasFolder.contents
                    } else {
                        const newContext = {
                            title: currentFolder,
                            contents: [],
                        }

                        currentContext.push(newContext)
                        currentContext = newContext.contents
                    }
                }

                currentContext.push({
                    title: path,
                    contents: file.contents,
                    submission_id: file.submission_id,
                    id: file.id,
                })
            },

            handleCopyClicked() {
                copyTextToClipboard(this.activeFile.contents)
            },
        },
    }

    function copyTextToClipboard(text) {
        let textArea = document.createElement("textarea");

        textArea.style.position = 'fixed';
        textArea.style.top = 0;
        textArea.style.left = 0;

        textArea.style.width = '2em';
        textArea.style.height = '2em';

        textArea.style.padding = 0;

        textArea.style.border = 'none';
        textArea.style.outline = 'none';
        textArea.style.boxShadow = 'none';

        textArea.style.background = 'transparent';

        textArea.value = text;

        document.body.appendChild(textArea);

        textArea.select();

        try {
            const successful = document.execCommand('copy')
            const message = successful
                ? 'Code copied to clipboard'
                : 'Error copying code'

            window.VueEvent.$emit('show-notification', message, successful ? 'info' : 'error');
        } catch (err) {
            const message = 'Unable to copy';

            window.VueEvent.$emit('show-notification', message, 'error');
        }

        document.body.removeChild(textArea);
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

    .code-column {
        position: relative;
        overflow-x: scroll;
    }

    .code-copy-container {
        position: absolute;
        top: 20px;
        right: 15px;
        padding: 10px 15px;
        cursor: pointer;

        .code-copy {
            border-bottom: 1px solid #4f5f6f;
        }

        &:hover {
            color: darken(#4f5f6f, 15%);

            .code-copy {
                border-bottom: 1px solid darken(#4f5f6f, 15%);
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
