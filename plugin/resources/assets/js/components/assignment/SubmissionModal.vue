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

            <files-component :submission="submission" testerType=""></files-component>
        </div>

    </modal>
</template>

<script>
    import Modal from '../partials/Modal.vue';
    import Translate from '../../mixins/translate';
    import FilesComponent from '../../popup/components/FilesComponent.vue';

    export default {
        mixins: [ Translate ],

        components: { Modal, FilesComponent },

        props: {
            submission: { required: true },
        },

        computed: {
            isActive() {
                return this.submission !== null;
            },

            hasCommitMessage() {
                return this.submission.git_commit_message !== null && this.submission.git_commit_message.length > 0;
            },
        }
    }
</script>
