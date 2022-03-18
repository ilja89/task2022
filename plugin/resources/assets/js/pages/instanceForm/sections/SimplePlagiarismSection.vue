<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr_DS">

        <legend class="ftoggler">{{ translate('plagiarism_title') }}</legend>

        <div class="fcontainer clearfix fitem">

            <label v-if="form.plagiarismSettings.courseExists">
                <input
                    type="checkbox"
                    name="plagiarism_create_update_charon"
                    v-model="form.plagiarism_create_update_charon"
                    :value="form.plagiarism_create_update_charon"
                />
                {{ translate('plagiarism_course_found_label') }}
            </label>

            <label v-else>
                <input
                    type="checkbox"
                    name="plagiarism_create_update_charon"
                    v-model="form.plagiarism_create_update_charon"
                    :value="form.plagiarism_create_update_charon"
                    disabled
                />
                {{ translate('plagiarism_course_not_found_label') }}
            </label>

        </div>

    </fieldset>

</template>

<script>
import {Translate} from "../../../mixins";

export default {
    mixins: [ Translate ],

    props: {
        form: {required: true}
    },

    data() {
        return {
            courseFoundMsg: 'Create or Update Charon in Plagiarism',
            courseNotFoundMsg: 'Course not detected in Plagiarism. Check your connection to Plagiarism or make sure you have set all Plagiarism settings for this course'
        }
    }
}
</script>
