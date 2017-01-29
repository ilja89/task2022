<template>

    <div class="search-container">
        <label for="student-search" class="student-search-label">
            <svg xmlns="http://www.w3.org/2000/svg"  version="1.1" x="0px" y="0px" viewBox="0 0 451 451" style="enable-background:new 0 0 451 451;" xml:space="preserve">
                <g><path d="M447.05,428l-109.6-109.6c29.4-33.8,47.2-77.9,47.2-126.1C384.65,86.2,298.35,0,192.35,0C86.25,0,0.05,86.3,0.05,192.3   s86.3,192.3,192.3,192.3c48.2,0,92.3-17.8,126.1-47.2L428.05,447c2.6,2.6,6.1,4,9.5,4s6.9-1.3,9.5-4   C452.25,441.8,452.25,433.2,447.05,428z M26.95,192.3c0-91.2,74.2-165.3,165.3-165.3c91.2,0,165.3,74.2,165.3,165.3   s-74.1,165.4-165.3,165.4C101.15,357.7,26.95,283.5,26.95,192.3z"/></g>
            </svg>
        </label>
        <autocomplete
                :url="'/mod/charon/api/courses/' + getCourseId() + '/students/search'"
                anchor="fullname"
                :on-select="getData"
                class-name="student-search"
                id="student-search"
                placeholder="Student name"
                :min="2"
                :custom-params="{ keyword: student_name }"
                :on-input="onStudentNameChanged">
        </autocomplete>

        <svg xmlns="http://www.w3.org/2000/svg" class="student-search-clear-btn" version="1.1" viewBox="0 0 64 64" enable-background="new 0 0 64 64"
                @click="clearClicked">
            <g><path d="M28.941,31.786L0.613,60.114c-0.787,0.787-0.787,2.062,0,2.849c0.393,0.394,0.909,0.59,1.424,0.59   c0.516,0,1.031-0.196,1.424-0.59l28.541-28.541l28.541,28.541c0.394,0.394,0.909,0.59,1.424,0.59c0.515,0,1.031-0.196,1.424-0.59   c0.787-0.787,0.787-2.062,0-2.849L35.064,31.786L63.41,3.438c0.787-0.787,0.787-2.062,0-2.849c-0.787-0.786-2.062-0.786-2.848,0   L32.003,29.15L3.441,0.59c-0.787-0.786-2.061-0.786-2.848,0c-0.787,0.787-0.787,2.062,0,2.849L28.941,31.786z"/></g>
        </svg>
    </div>

</template>

<script>
    import autocomplete from 'vue2-autocomplete-js';

    import AccessContext from '../../../mixins/accessContext';

    export default {
        mixins: [ AccessContext ],

        components: { autocomplete },

        data() {
            return {
                student_name: ''
            };
        },

        methods: {
            getData(data) {
                console.log(data);
            },

            onStudentNameChanged(name) {
                this.student_name = name;
            },

            clearClicked() {
                this.$children.forEach((child) => {
                    if (child.$options._componentTag === 'autocomplete') {
                        child.setValue('');
                    }
                });
            }
        }
    }
</script>
