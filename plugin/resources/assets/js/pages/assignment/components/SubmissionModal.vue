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

            <h3>{{ translate('filesText') }}</h3>
            <files-component :submission="submission" :testerType="testerType" :isRound="true"></files-component>
        </div>

    </modal>
</template>

<script>
    import { Modal, FilesComponent } from '../../../components/partials';
    import { Translate } from '../../../mixins';

    export default {
        mixins: [ Translate ],

        components: { Modal, FilesComponent },

        props: {
            submission: { required: true },
        },

        data() {
            return {
                testerType: '',
            };
        },

        computed: {
            isActive() {
                return this.submission !== null;
            },

            hasCommitMessage() {
                return this.submission.git_commit_message !== null && this.submission.git_commit_message.length > 0;
            },
        },

        mounted() {
            this.testerType = window.testerType;
        }
    }
</script>
