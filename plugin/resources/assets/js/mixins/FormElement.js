export default {

    props: {
        name: { required: true },
        required: { required: false, default: false },
        label: { required: true },
        value: { required: true },
        helper_text: { required: false, default: null },
        input_class: { required: false, default: '' },
    },

    data() {
        return {
            input_value: ''
        }
    },

    watch: {
        value() {
            this.input_value = this.value;
        }
    },

    computed: {
        requiredClass() {
            return this.required ? 'required' : ''
        }
    },

    methods: {
        onInputChanged(event) {
            this.$emit('input-was-changed', this.input_value);
        }
    },

    mounted() {
        this.input_value = this.value;
    },
}
