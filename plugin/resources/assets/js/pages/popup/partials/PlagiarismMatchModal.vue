<template>
    <v-dialog v-model="isActive" width="95vw" style="position: relative; z-index: 3000"
              transition="dialog-bottom-transition" scrollable>
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
            <v-toolbar :color="color" dark>
                <v-spacer></v-spacer>

                <v-btn color="error" @click="isActive = false">
                    Close
                </v-btn>
            </v-toolbar>

            <v-card-text class="pt-4" style="height: 95%">
                <div style="height: 25%;">
                    <div class="info-field headline" style="text-align: center;height: 100%; overflow-y: scroll;">
                        {{ match.uniid }} - {{ match.percentage }}%<br>
                        <span style="font-size: 14px;color: #0a0a0a">Commit hash: {{match.commit_hash ? match.commit_hash.slice(0, 8) : 'No commit' }}</span><br>
                        <v-btn :href="'#/grading/' + match.user_id" target="_blank">
                            Student overview
                            <v-icon aria-label="Match information" role="button" aria-hidden="false">mdi-open-in-new</v-icon>
                        </v-btn>
                        <v-btn :href="'#/submissions/' + match.submission_id" target="_blank">
                            Submission
                            <v-icon aria-label="Match information" role="button" aria-hidden="false">mdi-open-in-new</v-icon>
                        </v-btn>
                        <v-btn v-if="match.gitlab_commit_at" :href="match.gitlab_commit_at" target="_blank">
                            GitLab
                            <v-icon aria-label="Match information" role="button" aria-hidden="false">mdi-open-in-new</v-icon>
                        </v-btn>
                    </div>
                    <div class="info-field" style="height: 100%;overflow-y: scroll">
                        <v-simple-table dense style="overflow-y: auto;width: 100%; margin-left: auto; margin-right: auto">
                            <template v-slot:default>
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="d-flex justify-center">
                                                {{match.uniid}}'s blocks
                                            </div>
                                        </th>
                                        <th>
                                            <div class="d-flex justify-center">
                                                Lines
                                            </div>
                                        </th>
                                        <th>
                                            <div class="d-flex justify-center">
                                                {{match.other_uniid}}'s blocks
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr v-for="similarity in similaritiesTable.similarities" :key="similarity.id">
                                    <td>
                                        <div class="d-flex justify-center">
                                            <v-btn :color="similarity.color"
                                                   @click="goToLine(similaritiesTable.matchId + '-0', similarity.lines_start)">
                                                {{ similarity.lines_start }} - {{ similarity.lines_end }} ({{similarity.section_percentage}}%)
                                            </v-btn>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-center">
                                            <v-btn :color="similarity.color"
                                                   @click="goToLineBoth(similaritiesTable.matchId, similarity.lines_start, similarity.other_lines_start)">
                                                {{ similarity.section_size}}
                                            </v-btn>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-center">
                                            <v-btn :color="similarity.color"
                                                   @click="goToLine(similaritiesTable.matchId + '-1', similarity.other_lines_start)">
                                                {{ similarity.other_lines_start }} - {{ similarity.other_lines_end }} ({{similarity.other_section_percentage}}%)
                                            </v-btn>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </template>
                        </v-simple-table>
                    </div>
                    <div class="info-field headline" style="text-align: center;height: 100%; overflow-y: scroll;">
                        {{ match.other_uniid }} - {{ match.other_percentage }}%<br>
                        <span style="font-size: 14px;color: #0a0a0a">Commit hash: {{match.other_commit_hash ? match.other_commit_hash.slice(0, 8) : 'No commit' }}</span><br>
                        <v-btn :href="'#/grading/' + match.other_user_id" target="_blank">
                            Student overview
                            <v-icon aria-label="Match information" role="button" aria-hidden="false">mdi-open-in-new</v-icon>
                        </v-btn>
                        <v-btn :href="'#/submissions/' + match.other_submission_id" target="_blank">
                            Submission
                            <v-icon aria-label="Match information" role="button" aria-hidden="false">mdi-open-in-new</v-icon>
                        </v-btn>
                        <v-btn v-if="match.other_gitlab_commit_at" :href="match.other_gitlab_commit_at" target="_blank">
                            GitLab
                            <v-icon aria-label="Match information" role="button" aria-hidden="false">mdi-open-in-new</v-icon>
                        </v-btn>
                    </div>
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
                        height="100%"
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
                        height="100%"
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
import {ToggleButton} from "../../../components/partials";
import AceEditor from 'vuejs-ace-editor';

export default {
    name: "plagiarism-match-modal",

    components: {
        AceEditor,
        ToggleButton
    },

    props: {
        match: {required: true},
        color: {required: true}
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
                similarity['section_percentage'] = (match.percentage * similarity.section_size / match.lines_matched).toFixed(1);
                similarity['other_section_percentage'] = (match.other_percentage * similarity.other_section_size / match.lines_matched).toFixed(1);
            })

            return {
                matchId: match.id,
                similarities: updatedSimilarities,
            }
        },
        activeFile() {
            let match = this.match;

            return {
                contents: match.code.trim(),
            }
        },
        activeOtherFile() {
            let match = this.match

            return {
                contents: match.other_code.trim(),
            }
        },
    },

    methods: {
        goToLineBoth(editorName, lines_start, other_lines_start) {
            this.goToLine(editorName + "-0", lines_start)
            this.goToLine(editorName + "-1", other_lines_start)
        },
        goToLine(editorName, lines_start) {
            let editor = ace.edit(editorName);

            editor.resize(true);
            editor.scrollToLine(lines_start, true, true, function () {
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
                editor.session.addMarker(new Range(similarity.lines_start - 1, 0, similarity.lines_end, 0), similarityClass, "line")
                editor2.session.addMarker(new Range(similarity.other_lines_start - 1, 0, similarity.other_lines_end, 0), similarityClass, "line")
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
.info-field {
    width: 33.33%;
    float: left;
    padding: 5px;
}
</style>
