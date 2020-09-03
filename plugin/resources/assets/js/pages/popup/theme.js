import colors from 'vuetify/lib/util/colors'

export default {
    light: {
        primary: colors.purple,
        secondary: colors.grey.darken1,
        accent: colors.shades.black,
        error: colors.red.accent3,
        background: colors.indigo.lighten5 // Added variable
    },
    dark: {
        primary: colors.blue.lighten3,
        background: colors.indigo.base // If using base color, be use to mark as such to get HEX value
    },
}