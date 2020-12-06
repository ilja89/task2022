class CharonFormat {

    static formatCharons(ch) {
        for (let i = 0; i < ch.length; i++) {
            CharonFormat.getNamesForLabs(ch[i].defense_labs)
        }
        return ch
    }

    static getNamesForLabs(labs) {
        for (let i = 0; i < labs.length; i++) {
            labs[i].name = CharonFormat.getDayTimeFormat(new Date(labs[i].start))
                + ' (' + CharonFormat.getNiceDate(new Date(labs[i].start)) + ')'
        }
    }

    static getDayTimeFormat(start) {
        try {
            let daysDict = {0: 'P', 1: 'E', 2: 'T', 3: 'K', 4: 'N', 5: 'R', 6: 'L'};
            return daysDict[start.getDay()] + start.getHours();
        } catch (e) {
            return ""
        }
    }

    static getNiceDate(date) {
        try {
            let month = (date.getMonth() + 1).toString();
            if (month.length === 1) {
                month = "0" + month
            }
            return date.getDate() + '.' + month + '.' + date.getFullYear()
        } catch (e) {
            return ""
        }
    }

    static getNiceTime(time) {
        try {
            let mins = time.getMinutes().toString();
            if (mins.length === 1) {
                mins = "0" + mins;
            }
            return time.getHours() + ":" + mins
        } catch (e) {
            return ""
        }

    }

}

export default CharonFormat