import {getSubmissionWeightedScore} from "./submission";

export const getColor = (submission, registrations = []) => {
    if (defendedSubmission(submission)) return 'success'
    else if (Number.parseFloat(getSubmissionWeightedScore(submission)) < 0.01) return 'red';
    else if (registeredSubmission(submission.id, registrations)) return 'teal';
    else return `light-blue darken-${getColorDarknessByPercentage(getSubmissionWeightedScore(submission) / 100)}`;
}

const defendedSubmission = submission => {
    try {
        const last = submission.results[submission.results.length - 1];
        return parseFloat(last['calculated_result']) !== 0.0 && last['grade_type_code'] === 1001;
    } catch (e) {
        return false
    }
}

const registeredSubmission = (submissionId, registrations = []) => {
    let test = registrations.find(x => x.submission_id === submissionId);
    if (test != null) {
        test = test['submission_id'];
        return submissionId === test;
    }
}

const getColorDarknessByPercentage = (percentage, maxDarkness = 3) => {
    return maxDarkness - Math.floor(maxDarkness * percentage);
}
