import hljs from 'highlight.js';

const highlightDirective = {
    deep: true,
    bind: function (el, binding) {
        let targets = el.querySelectorAll('code');

        targets.forEach(target => {
            if (binding.value) {
                target.innerHTML = binding.value;
            }

            hljs.highlightBlock(target);
        });
    },
    componentUpdated: function (el, binding) {
        let targets = el.querySelectorAll('code');

        targets.forEach((target) => {
            if (binding.value) {
                target.innerHTML = binding.value;
                hljs.highlightBlock(target);
            }
        });
    }
};

export default highlightDirective;
