<template>
    <v-card class="mx-auto" max-width="70vw" outlined raised>
        <div
                class="columns is-gapless code-container"
                :class="{ 'is-round': isRound }"
                v-if="activeFile !== null">

            <div class="column is-narrow file-tree-container is-one-quarter">
                <file-tree
                        :data="formattedFiles"
                        @file-clicked="handleFileClicked"
                >
                </file-tree>
            </div>

            <div class="column  is-narrow">
                <div class="line-number-container">
                <span
                        v-for="n in activeFile.numbers"
                        class="line-number-position"
                >
                    <span class="line-number">{{ n }}</span>
                </span>
                </div>
            </div>

            <div class="column code-column">
                <pre class="code" v-highlightjs="activeFile.contents"><code :class="testerType"></code></pre>
            </div>
        </div>
    </v-card>

</template>

<script>

    import FileTree from './FileTree'

    export default {

        components: {FileTree},

        props: {
            submission: {required: true},
            testerType: {required: true},
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
            }
        },

        computed: {
            activeFile() {
                if (this.files.length === 0) {
                    return null
                }

                let file = this.files.find(file => {
                    return file.id === this.activeFileId
                })

                return {
                    id: file.id,
                    path: file.path,
                    contents: file.contents.trim().replace(/</g, '&lt;').replace(/>/g, '&gt;'),
                    numbers: file.contents ? file.contents.trim().split(/\r\n|\r|\n/).length : 0,
                }
            },
        },

        watch: {
            submission() {
                this.getFiles()
            },
        },

        mounted() {
            this.getFiles()
        },

        methods: {
            getFiles() {


                this.files = this.submission.files
                this.formattedFiles = []

                this.files.forEach(file => {
                    this.addFormattedFile(file)
                })

                if (this.files.length > 0) {
                    this.activeFileId = this.files[0].id

                    this.formattedFiles[0] = this.compressFiles(this.formattedFiles[0])
                }

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

            compressFiles(file) {

                if (typeof file.contents === 'string') {
                    // Is file
                    return {...file}
                }

                if (file.contents.length === 1) {
                    // Is folder with only one item inside
                    // So should be merged with child
                    const newContents = file.contents.map(this.compressFiles)

                    let newFile
                    const child = newContents[0]
                    if (typeof child.contents !== 'string') {
                        // The one child is a folder
                        newFile = {
                            title: file.title + '/' + child.title,
                            contents: child.contents,
                        }
                    } else {
                        newFile = {...file}
                    }

                    return newFile
                }

                // Has multiple children
                return {
                    ...file,
                    contents: file.contents.map(this.compressFiles)
                }
            },
        },
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
            height: 100%;
            padding-top: 1.25rem;
            padding-bottom: 1.25rem;

            background: darken(#fafafa, 5%);
            border: 1px solid #dbdbdb;
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
        height: 100%;
        padding: 0;

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
