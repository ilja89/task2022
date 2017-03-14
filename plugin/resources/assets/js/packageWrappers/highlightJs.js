var hljs = require("highlight.js/lib/highlight.js");

// Only load languages we need. Can also alias javang
hljs.registerLanguage('java', require('highlight.js/lib/languages/java'));
hljs.registerLanguage('javang', require('highlight.js/lib/languages/java'));
hljs.registerLanguage('python', require('highlight.js/lib/languages/python'));
hljs.registerLanguage('prolog', require('highlight.js/lib/languages/prolog'));

hljs.configure({
    tabReplace: '    ',
});

window.hljs = hljs;
