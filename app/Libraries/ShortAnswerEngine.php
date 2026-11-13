<?php

namespace App\Libraries;

/**
 * ShortAnswerEngine
 * 
 * Thư viện so khớp đáp án trả lời ngắn.
 * Hỗ trợ nhiều đáp án đúng, mỗi đáp án có cấu hình riêng:
 *   - case_sensitive: phân biệt chữ hoa/thường
 *   - space_sensitive: phân biệt dấu cách
 */
class ShortAnswerEngine
{
    /**
     * So khớp câu trả lời của học sinh với danh sách đáp án đúng.
     * Trả về true nếu khớp với ÍT NHẤT MỘT đáp án.
     *
     * @param string $userAnswer       Câu trả lời của học sinh (raw input)
     * @param array  $acceptedAnswers  Mảng đáp án đúng, mỗi phần tử có:
     *                                 ['answer_text', 'case_sensitive', 'space_sensitive']
     * @return bool
     */
    public function checkAnswer(string $userAnswer, array $acceptedAnswers): bool
    {
        foreach ($acceptedAnswers as $accepted) {
            if ($this->matchOne($userAnswer, $accepted['answer_text'], (bool)$accepted['case_sensitive'], (bool)$accepted['space_sensitive'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * So khớp với một đáp án cụ thể.
     */
    public function matchOne(string $userAnswer, string $correctAnswer, bool $caseSensitive, bool $spaceSensitive): bool
    {
        $user    = $userAnswer;
        $correct = $correctAnswer;

        // Xử lý dấu cách
        if (!$spaceSensitive) {
            // Không phân biệt dấu cách: chuẩn hoá nhiều space → 1 space, rồi trim
            $user    = trim(preg_replace('/\s+/', ' ', $user));
            $correct = trim(preg_replace('/\s+/', ' ', $correct));
        } else {
            // Phân biệt dấu cách: chỉ trim 2 đầu
            $user    = trim($user);
            $correct = trim($correct);
        }

        // Xử lý chữ hoa/thường
        if (!$caseSensitive) {
            return mb_strtolower($user, 'UTF-8') === mb_strtolower($correct, 'UTF-8');
        }

        return $user === $correct;
    }

    /**
     * Chấm điểm một bài làm.
     * 
     * @param array $quizQuestions  Mảng câu hỏi từ short_answer_quiz_questions kèm question_text
     * @param array $acceptedAnswersMap  Map [question_id => [accepted_answers]]
     * @param array $studentAnswers  Map [question_id => answer_string]
     * @return array ['raw_score' => float, 'results' => [question_id => ['correct'=>bool, 'points'=>float, 'user_answer'=>string]]]
     */
    public function gradeSubmission(array $quizQuestions, array $acceptedAnswersMap, array $studentAnswers): array
    {
        $rawScore = 0.0;
        $results  = [];

        foreach ($quizQuestions as $qq) {
            $qId      = (int)$qq['question_id'];
            $points   = (float)$qq['points'];
            $userAns  = trim($studentAnswers[$qId] ?? '');
            $accepted = $acceptedAnswersMap[$qId] ?? [];

            $isCorrect = !empty($accepted) && $this->checkAnswer($userAns, $accepted);

            if ($isCorrect) {
                $rawScore += $points;
            }

            $results[$qId] = [
                'correct'    => $isCorrect,
                'points'     => $isCorrect ? $points : 0,
                'max_points' => $points,
                'user_answer'=> $userAns,
            ];
        }

        return [
            'raw_score' => round($rawScore, 2),
            'results'   => $results,
        ];
    }

    /**
     * Tính điểm phạt nộp chậm cho tất cả bài trong session.
     * 
     * Công thức:
     *   rank(S) = số học sinh có raw_score == raw_score(S) VÀ submitted_at < submitted_at(S)
     *   penalty(S) = penalty_coefficient * rank(S)
     *   final_score(S) = max(0, raw_score(S) - penalty(S))
     * 
     * Học sinh cùng raw_score VÀ cùng submitted_at → rank bằng nhau (không phạt nhau).
     *
     * @param array  $submissions        Mảng bài nộp (cần có: id, raw_score, submitted_at)
     * @param float  $penaltyCoefficient Hệ số phạt
     * @return array Map [submission_id => ['final_score'=>float, 'penalty_deducted'=>float, 'rank'=>int]]
     */
    public function calculatePenalties(array $submissions, float $penaltyCoefficient): array
    {
        $results = [];

        foreach ($submissions as $sub) {
            $subId      = (int)$sub['id'];
            $rawScore   = (float)$sub['raw_score'];
            $submittedAt = $sub['submitted_at'];

            if ($penaltyCoefficient <= 0) {
                $results[$subId] = [
                    'final_score'      => $rawScore,
                    'penalty_deducted' => 0.0,
                    'rank'             => 0,
                ];
                continue;
            }

            // Đếm số người có cùng raw_score VÀ nộp SỚM HƠN (submitted_at nhỏ hơn)
            $rank = 0;
            foreach ($submissions as $other) {
                if ((int)$other['id'] === $subId) continue;
                if (abs((float)$other['raw_score'] - $rawScore) < 0.001) {
                    // Cùng raw_score
                    if ($other['submitted_at'] !== null && $submittedAt !== null) {
                        if (strtotime($other['submitted_at']) < strtotime($submittedAt)) {
                            $rank++;
                        }
                    }
                }
            }

            $penalty    = round($penaltyCoefficient * $rank, 2);
            $finalScore = max(0, round($rawScore - $penalty, 2));

            $results[$subId] = [
                'final_score'      => $finalScore,
                'penalty_deducted' => $penalty,
                'rank'             => $rank,
            ];
        }

        return $results;
    }
}
