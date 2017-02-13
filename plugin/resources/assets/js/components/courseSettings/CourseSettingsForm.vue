<template>
    <div class="">

        <form :action="'/mod/charon/courses/' + form.course_id + '/settings'" method="post">

            <input type="hidden" name="_token" :value="csrf_token">

            <tester-settings-section
                    :form="form">
            </tester-settings-section>

            <!--<charon-fieldset>-->
                <!--<template slot="title">{{ translate('presets_title') }}</template>-->

                <!--<slot>-->
                    <!--<h2>More Hello Worlds!</h2>-->
                <!--</slot>-->
            <!--</charon-fieldset>-->

            <input type="submit" value="Save" class="btn btn-default">

        </form>

    </div>
</template>

<script>
    import TesterSettingsSection from './TesterSettingsSection.vue';
    import CharonFieldset from './../form/CharonFieldset.vue';
    import Translate from '../../mixins/translate';

    export default {
        mixins: [ Translate ],

        props: {
            form: { required: true },
            csrf_token: { required: true }
        },

        components: { CharonFieldset, TesterSettingsSection },

        mounted() {
            VueEvent.$on('unittests-git-was-changed', unittests_git => this.form.fields.unittests_git = unittests_git);
            VueEvent.$on('tester-type-was-changed', tester_type => this.form.fields.tester_type = tester_type);
        }
    }
</script>
