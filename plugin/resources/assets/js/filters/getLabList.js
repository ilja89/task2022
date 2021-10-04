// return string showing main info about lab

export default function getLabList({start, name, defendersNum, estimatedStartTime}) {
    let startContents = start.split(' ');
    let date = `${startContents[0]}`;
    let time = `${startContents[1]}`;
    let timeReturn = time.split(':')

    if (estimatedStartTime) {
        estimatedStartTime = estimatedStartTime.split(" ");
        estimatedStartTime = estimatedStartTime[1].split(":");
        estimatedStartTime = estimatedStartTime[0] + ":" + estimatedStartTime[1];
    }

    return date + " " + timeReturn[0] + ":" + timeReturn[1] + (name ? " " + name : "") + " - " +
        (estimatedStartTime
            ? defendersNum + " defences in the queue, est. start time: " + estimatedStartTime
            : "Lab is fully booked, registration is unavailable");
}
