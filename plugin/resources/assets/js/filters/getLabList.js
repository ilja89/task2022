// return string showing main info about lab

export default function getLabList({start, name, defenders_num, estimated_start_time}) {
    let startContents = start.split(' ');
    let date = `${startContents[0]}`;
    let time = `${startContents[1]}`;
    let timeReturn = time.split(':')

    return date + " " + timeReturn[0] + ":" + timeReturn[1] + (name ? " " + name : "") + " - " +
        (estimated_start_time
            ? defenders_num + " defences in the queue - est. defence time: " +
                estimated_start_time.split(" ")[1].split(":")[0] + ":" +
                estimated_start_time.split(" ")[1].split(":")[1]
            : "Lab is fully booked");
}
