//return string showing main info about lab
export default function getLabList({start, name, defenders_num, estimatedStartTime}) {
    let date = `${start.split(' ')[0]}`;
    let time = `${start.split(' ')[1]}`;
    let time_return = time.split(':')
    if (estimatedStartTime) {
        estimatedStartTime = estimatedStartTime.split(" ");
        estimatedStartTime = estimatedStartTime[1].split(":");
        estimatedStartTime = estimatedStartTime[0] + ":" + estimatedStartTime[1];
    }
    return date + " " + time_return[0] + ":" + time_return[1] + (name ? " " + name : "") + " - " + (estimatedStartTime ?
        defenders_num + " defences in the queue, est. start time: " + estimatedStartTime : "Lab is fully booked, registration is unavailable");
}