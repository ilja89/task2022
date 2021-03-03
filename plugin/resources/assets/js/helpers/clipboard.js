export const copyTextToClipboard = (text) => {
    let textArea = document.createElement("textarea")

    textArea.style.position = 'fixed'
    textArea.style.top = 0
    textArea.style.left = 0

    textArea.style.width = '2em'
    textArea.style.height = '2em'

    textArea.style.padding = 0

    textArea.style.border = 'none'
    textArea.style.outline = 'none'
    textArea.style.boxShadow = 'none'

    textArea.style.background = 'transparent'

    textArea.value = text

    document.body.appendChild(textArea)

    textArea.select()

    try {
        const successful = document.execCommand('copy')
        const message = successful
            ? 'Code copied to clipboard'
            : 'Error copying code'

        document.body.removeChild(textArea)

        if (window.VueEvent) {
            window.VueEvent.$emit('show-notification', message, successful ? 'info' : 'error')
        }

        return successful
    } catch (err) {
        document.body.removeChild(textArea)

        const message = 'Unable to copy'

        if (window.VueEvent) {
            window.VueEvent.$emit('show-notification', message, 'error')
        }

        return false
    }
}

