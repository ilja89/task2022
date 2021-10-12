// return string showing main info about lab

export default function getLabList({start, name, defenders_num, estimated_start_time}) {

    let date = new Date(start);
    let locales = "et";
    let timeOptions = { hour: '2-digit', minute: '2-digit' };

    return date.toLocaleDateString(locales) + " " + date.toLocaleTimeString(locales, timeOptions) +
        (name ? " " + name : "") + " - " +
        (estimated_start_time
            ? defenders_num + " defences in queue - est. defence time: " +
                new Date(estimated_start_time).toLocaleTimeString(locales, timeOptions)
            : "Not enough time left in lab queue");
}
