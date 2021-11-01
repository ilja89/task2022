import {formatName} from "./formatting";

export const latestSubmissionsChunks = latestSubmissions => {
    const chunkSize = 2
    let chunkIndex = 0

    let chunks = []
    let chunk = {
        id: chunkIndex,
        subs: []
    }
    latestSubmissions.forEach(submission => {
        if (chunk.subs.length < chunkSize) {
            chunk.subs.push(submission)
        } else {
            chunkIndex++
            chunks.push(chunk)
            chunk = {
                id: chunkIndex,
                subs: [submission]
            }
        }
    })

    if (chunk.subs.length) {
        chunks.push(chunk)
    }

    return chunks
}

export const formatStudentResults = submission => {
    return submission.users.map(user => {
        let results = submission.results
            .filter(result => result.user_id === user.id)
            .sort((a, b) => a.grade_type_code - b.grade_type_code)
            .map(result => result.calculated_result)
            .join(', ');

        return formatName(user) + ' ('  + results + ')';
    }).join(' | ');
}
