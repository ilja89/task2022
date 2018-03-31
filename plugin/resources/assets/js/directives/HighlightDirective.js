import { copyTextToClipboard } from '../helpers/clipboard'

const insertCopyButton = (element) => {
    const $previousButtons = element.parentNode.querySelectorAll('.button--copy')
    if ($previousButtons) {
        $previousButtons.forEach($button => $button.remove())
    }

    element.insertAdjacentHTML(
        'afterend', '<button class="button button--copy">Copy</button>'
    )

    const $copyButton = element.parentNode.querySelector('.button--copy')
    $copyButton.addEventListener('click', () => {
        // For some reason, innerText gives two line endings for
        // each line so we have to replace them with one...
        const text = element.innerText.replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/\n\n/, '\n')

        const successful = copyTextToClipboard(text)

        if (successful) {
            $copyButton.innerHTML = 'Copied!'
        } else {
            $copyButton.innerHTML = 'Error!'
        }

        setTimeout(() => {
            $copyButton.innerHTML = 'Copy'
        }, 4000)
    })
}

export default {

    deep: true,

    bind(el, binding) {
        let targets = el.querySelectorAll('code')

        targets.forEach(target => {
            if (binding.value) {
                target.innerHTML = binding.value
            }

            if (target.parentElement.nodeName === 'PRE') {
                // Only add a copy button for non-inline code
                insertCopyButton(target)
            }

            window.hljs.highlightBlock(target)
        })
    },

    componentUpdated(el, binding) {
        let targets = el.querySelectorAll('code')

        targets.forEach(target => {
            if (binding.value) {
                target.innerHTML = binding.value
            }

            if (target.parentElement.nodeName === 'PRE') {
                // Only add a copy button for non-inline code
                insertCopyButton(target)
            }

            window.hljs.highlightBlock(target)
        })
    },
};
