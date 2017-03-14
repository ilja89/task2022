// import hljs from 'highlight.js';
// TODO: Fix highlight

const highlightDirective = {

    deep: true,

    bind(el, binding) {
        let targets = el.querySelectorAll('code');

        targets.forEach(target => {
            if (binding.value) {
                target.innerHTML = binding.value;
            }

            // hljs.highlightBlock(target);
        });
    },

    componentUpdated(el, binding) {
        let targets = el.querySelectorAll('code');

        targets.forEach(target => {
            if (binding.value) {
                target.innerHTML = binding.value;
                // hljs.highlightBlock(target);
            }
        });
    }
};

export default highlightDirective;
