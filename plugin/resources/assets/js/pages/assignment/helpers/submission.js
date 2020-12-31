/**
 * Calculate the weighted score
 * @param submission {Object}
 * @returns {string}
 */
export const getSubmissionWeightedScore = submission => {
    let totalWeight = 0;
    let totalWeightedScore = 0;

    for (let i = 0; i < submission.test_suites.length; i++) {
        const suite = submission.test_suites[i];
        totalWeight += suite.weight
        totalWeightedScore += suite.weight * suite.grade
    }

    return (totalWeightedScore / Math.max(totalWeight, 1)).toFixed(2);
}
