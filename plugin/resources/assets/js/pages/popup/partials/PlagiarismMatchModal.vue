<template>
    <v-dialog v-model="isActive" width="95vw" style="position: relative; z-index: 3000"
              transition="dialog-bottom-transition">
        <template v-slot:activator="{ on, attrs }">
            <v-btn icon
                   :class="{ signal: 'green'}"
                   @click="onClickMatchInformation"
                   v-bind="attrs"
                   v-on="on"
            >
                <v-icon aria-label="Match information" role="button" aria-hidden="false">mdi-eye</v-icon>
            </v-btn>
        </template>

        <v-card style="background-color: white; overflow-y: hidden" height="90vh">
            <v-toolbar dark>
                <span class="headline">({{ this.match.percentage }}%) - ({{ this.match.other_percentage }}%)</span>
                <a v-bind:href="this.match.moss_url" target="_blank">Moss match url</a>
                <v-spacer></v-spacer>

                <v-btn color="error" @click="isActive = false">
                    Close
                </v-btn>
            </v-toolbar>

            <v-card-text class="pt-4" style="height: 95%">
                <div style="width: 20%; max-height: 30%; overflow-y: auto;margin-left: auto; margin-right: auto">
                    <v-simple-table dense>
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th style="text-align: center;">
                                    {{ match.uniid }}
                                </th>
                                <th style="text-align: center;">
                                    {{ match.other_uniid }}
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="similarity in similaritiesTable.similarities" :key="similarity.id">
                                <td>
                                    <div class="d-flex justify-center">
                                        <v-btn :color="similarity.color"
                                               @click="goToLine(similaritiesTable.matchId + '-0', similarity.lines)">
                                            {{ similarity.lines }}
                                        </v-btn>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-center">
                                        <v-btn :color="similarity.color"
                                               @click="goToLine(similaritiesTable.matchId + '-1', similarity.other_lines)">
                                            {{ similarity.other_lines }}
                                        </v-btn>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </div>
                <div class="field" style="height: 70%">
                    <AceEditor
                        class="editor"
                        v-model="activeFile.contents"
                        id="content"
                        v-bind:id="this.match.id + '-0'"
                        @init="editorInit"
                        :lang="testerType"
                        theme="crimson_editor"
                        width="100%"
                        height="500px"
                        :options="{
                    fontSize: 14,
                    enableSnippets: true,
                    showLineNumbers: true,
                    tabSize: 4,
                    showPrintMargin: false,
                    showGutter: true,
                    readOnly: true,
                    }"
                    />
                </div>
                <div class="field" style="height: 70%">
                    <AceEditor
                        class="editor"
                        v-model="activeOtherFile.contents"
                        id="content2"
                        v-bind:id="this.match.id + '-1'"
                        @init="editorInit"
                        :lang="testerType"
                        theme="crimson_editor"
                        width="100%"
                        height="500px"
                        :options="{
                    fontSize: 14,
                    enableSnippets: true,
                    showLineNumbers: true,
                    tabSize: 4,
                    showPrintMargin: false,
                    showGutter: true,
                    readOnly: true,
                    }"
                    />
                </div>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script>
import {Translate} from '../../../mixins'
import {ToggleButton} from "../../../components/partials";
import MatchFilesComponent from "../../../components/partials/MatchFilesComponent";
import MatchSimilaritiesComponent from "../../../components/partials/MatchSimilaritiesComponent";
import AceEditor from 'vuejs-ace-editor';

export default {
    name: "plagiarism-match-modal",

    mixins: [Translate],

    components: {
        AceEditor,
        MatchSimilaritiesComponent,
        MatchFilesComponent,
        ToggleButton
    },

    props: {
        match: {required: true}
    },

    data() {
        return {
            isActive: false,
            testerType: "python",
            similarityColors: [
                '#ffee45',
                '#95ec38',
                '#5cace7',
                '#cd8dea',
                '#ea8d8d'
            ]
        }
    },

    computed: {
        similaritiesTable() {
            let match = this.match;

            let updatedSimilarities = [];
            let counter = 0;
            this.match.similarities.forEach(similarity => {
                similarity['color'] = this.similarityColors[counter % 5]
                updatedSimilarities.push(similarity)
                counter += 1;
            })

            return {
                matchId: match.id,
                similarities: updatedSimilarities,
            }
        },
        activeFile() {
            let match = this.match;

            return {
                contents: match.code.trim().replace(/</g, '&lt;').replace(/>/g, '&gt;'),
            }
        },
        activeOtherFile() {
            let match = this.match

            return {
                contents: match.other_code.trim().replace(/</g, '&lt;').replace(/>/g, '&gt;'),
            }
        },
    },

    methods: {
        goToLine(editorName, lines) {
            let editor = ace.edit(editorName);
            let linesSplit = lines.split('-');

            editor.resize(true);
            editor.scrollToLine(parseInt(linesSplit[0]) - 1, true, true, function () {
            })
        },
        sleep(ms) {
            return new Promise((resolve => {
                setTimeout(resolve, ms);
            }))
        },
        async onClickMatchInformation() {
            this.isActive = true;
            await this.sleep(100);
            this.showSimilaritiesColor();
        },
        showSimilaritiesColor() {
            let match = this.match
            let editor = ace.edit(match.id + "-0");
            let editor2 = ace.edit(match.id + "-1");
            let Range = ace.acequire('ace/range').Range;
            let counter = 0
            this.match.similarities.forEach(similarity => {
                let similarityClass = 'similarity-color-' + counter % 5;
                let linesSplit = similarity.lines.split("-");
                let otherLinesSplit = similarity.other_lines.split("-");
                editor.session.addMarker(new Range(parseInt(linesSplit[0]) - 2, 0, parseInt(linesSplit[1]) - 1, 0), similarityClass, "line")
                editor2.session.addMarker(new Range(parseInt(otherLinesSplit[0]) - 2, 0, parseInt(otherLinesSplit[1]) - 1, 0), similarityClass, "line")
                counter += 1
            })
        },
        /**
         * Ace-code editor now supports only html, python, javascript, java, prolog and C#,
         * but more languages in these method like these: require('brace/mode/language'), where
         * language is programming language you need.
         * For example: require('brace/mode/python').
         */
        editorInit: function () {
            require('brace/ext/language_tools') //language extension prerequsite...
            require('brace/mode/html') //language
            require('brace/mode/python')
            require('brace/mode/javascript')
            require('brace/mode/java')
            require('brace/mode/prolog')
            require('brace/mode/csharp')
            require('brace/mode/less')
            require('brace/theme/crimson_editor')
            require('brace/snippets/python') //snippet
            require('brace/snippets/javascript')
            require('brace/snippets/java')
            require('brace/snippets/prolog')
            require('brace/snippets/csharp')
        }
    },
}
</script>

<style>
.similarity-color-0 {
    position: absolute;
    background: #ffee45;
    z-index: 20;
}

.similarity-color-1 {
    position: absolute;
    background: #95ec38;
    z-index: 20;
}

.similarity-color-2 {
    position: absolute;
    background: #5cace7;
    z-index: 20;
}

.similarity-color-3 {
    position: absolute;
    background: #cd8dea;
    z-index: 20;
}

.similarity-color-4 {
    position: absolute;
    background: #ea8d8d;
    z-index: 20;
}

.field {
    width: 50%;
    float: left;
    padding: 5px;
}
</style>
