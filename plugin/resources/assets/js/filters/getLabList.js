//return string showing main info about lab
export default function getLabList({start, name, defenders_num}) {
    let date = `${start.split(' ')[0]}`;
    let time = `${start.split(' ')[1]}`;
    let time_return = time.split(':')
    return date + " " + time_return[0] + ":" + time_return[1] + (name ? " " + name : "") + " - " + defenders_num + " defences in the queue";
}
