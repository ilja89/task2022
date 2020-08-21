<template>
    <modal :isActive="isActive" @modal-was-closed="$emit('modal-was-closed')" v-if="submission !== null">
        <template slot="header">
            <p class="modal-card-title">{{ translate('submissionText') }} {{ submission.git_hash }}</p>
        </template>

        <div class="content">

            <div v-if="hasCommitMessage">
                <h3>{{ translate('commitMessageText') }}</h3>
                <p>{{ submission.git_commit_message }}</p>
            </div>

            <div v-if="hasMail">
                <h3>{{ translate('testerFeedbackText') }}</h3>
                <pre v-html="submission.mail"></pre>
            </div>

            <h3>{{ translate('filesText') }}</h3>
            <files-component-without-tree :submission="submission" :testerType="testerType" :isRound="true">
            </files-component-without-tree>
        </div>

    </modal>
</template>

<script>
    import { Modal, FilesComponentWithoutTree } from '../../../components/partials'
    import { Translate } from '../../../mixins'

    export default {
        mixins: [ Translate ],

        components: { Modal, FilesComponentWithoutTree },

        props: {
            submission: { required: true },
        },

        data() {
            return {

                testerType: '',
            }
        },

        computed: {
            isActive() {
                return this.submission !== null
            },

            hasCommitMessage() {
                return this.submission.git_commit_message !== null && this.submission.git_commit_message.length > 0
            },

            hasMail() {
                return this.submission.mail !== null && this.submission.mail.length > 0
            },
        },

        mounted() {
            this.testerType = window.testerType
        },
    }
</script>
