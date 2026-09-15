<?php

namespace App\Libraries;

class FillBlankEngine
{
    private array $bbBien = [];

    /**
     * Parses raw BBCode question content into dynamic HTML content and extracts correct answers.
     * Replaces [trong]answer[/trong] with <span class="chotrong" id="trong_N" data-index="N">__________</span>
     * Returns array ['html' => string, 'answers' => array, 'total_blanks' => int]
     */
    public function parseQuestion(string $text): array
    {
        $this->bbBien = [];

        // 1. Process BBCode dynamic tags [bien], [bthuc], [chon], [chon0], [chonso]
        $processedText = $this->parseBBCode($text);

        // 2. Parse [trong]...[/trong] tags
        $answers = [];
        $blankIndex = 0;

        $pattern = '~\[trong\](.*?)\[/trong\]~is';
        $finalHtml = preg_replace_callback($pattern, function ($matches) use (&$answers, &$blankIndex) {
            $cleanText = preg_replace('/<\/?(b|i|u|span|code|strong|em|p|div|pre)\b[^>]*>/i', '', $matches[1]);
            $rawAnswer = trim(html_entity_decode($cleanText, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $answers[$blankIndex] = $rawAnswer;
            $span = '<span class="chotrong" id="trong_' . $blankIndex . '" data-index="' . $blankIndex . '" title="Chỗ trống thứ ' . ($blankIndex + 1) . '">__________</span>';
            $blankIndex++;
            return $span;
        }, $processedText);

        return [
            'html'         => $finalHtml,
            'answers'      => $answers,
            'total_blanks' => count($answers),
        ];
    }

    /**
     * Compare student answer with correct answer.
     * spaceOption: 0 = strip all whitespace, 1 = normalize spaces to single space, 2 = exact match
     */
    public function compareAnswer(string $userAns, string $correctAns, bool $caseSensitive = false, int $spaceOption = 0): bool
    {
        $u = trim($userAns);
        $c = trim($correctAns);

        if (!$caseSensitive) {
            $u = mb_strtoupper($u, 'UTF-8');
            $c = mb_strtoupper($c, 'UTF-8');
        }

        if ($spaceOption === 0) {
            // Remove all whitespace
            $u = preg_replace('/\s+/', '', $u);
            $c = preg_replace('/\s+/', '', $c);
        } elseif ($spaceOption === 1) {
            // Replace multiple spaces with single space
            $u = preg_replace('/\s+/', ' ', $u);
            $c = preg_replace('/\s+/', ' ', $c);
        }

        return $u === $c;
    }

    private function parseBBCode(string $text): string
    {
        $pattern = '~\[bien1=(.*?)\]~s';
        if (preg_match($pattern, $text)) {
            $this->bbBien = [];
        }

        $v = 0;
        $maxLoops = 100;
        $loop = 0;
        while (preg_match('~\[(chonso|chon0|chon|bien|bthuc)~s', $text, $matches, PREG_OFFSET_CAPTURE, $v) && $loop < $maxLoops) {
            $loop++;
            $text2 = $this->bbCodeStep($text, $matches[1][0], $matches[1][1] - 1);
            if ($text2 === $text) {
                $v += 5;
            } else {
                $text = $text2;
                $v = 0;
            }
        }

        return $text;
    }

    private function bbCodeStep(string $text, string $code, int $v): string
    {
        switch ($code) {
            case 'bien':
                $pattern = '~\[bien([0-9]+)=(.*?)\]~s';
                $kk = preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $v);
                if (!$kk || $matches[0][1] != $v) {
                    $pattern = '~\[bien([0-9]+)\]~s';
                    if (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $v)) {
                        if ($matches[0][1] != $v) {
                            break;
                        }
                        if (preg_match('~\[(chonso|chon0|chon|bien|bthuc)~s', $matches[0][0], $match2, PREG_OFFSET_CAPTURE, 5)) {
                            $text = $this->bbCodeStep($text, $match2[1][0], $match2[1][1] + $matches[0][1] - 1);
                        } else {
                            $stt = (int)$matches[1][0];
                            if (isset($this->bbBien[$stt])) {
                                $text = str_replace($matches[0][0], $this->bbBien[$stt], $text);
                            }
                        }
                    }
                    break;
                }
                if (preg_match('~\[(chonso|chon0|chon|bien|bthuc)~s', $matches[0][0], $match2, PREG_OFFSET_CAPTURE, 5)) {
                    $text = $this->bbCodeStep($text, $match2[1][0], $match2[1][1] + $matches[0][1] - 1);
                } else {
                    $newid = (int)$matches[1][0];
                    $danhsach = explode(',', $matches[2][0]);
                    if (count($danhsach) > 1 && !empty($this->bbBien)) {
                        foreach ($this->bbBien as $biencu) {
                            $idx = array_search($biencu, $danhsach, true);
                            if ($idx !== false) {
                                array_splice($danhsach, $idx, 1);
                            }
                        }
                    }
                    $len = count($danhsach);
                    if ($len > 0) {
                        $ngau = rand(0, 1000) % $len;
                        $this->bbBien[$newid] = $danhsach[$ngau];
                        $text = str_replace($matches[0][0], $danhsach[$ngau], $text);
                    }
                }
                break;

            case 'bthuc':
                $pattern = '~\[bthuc (.*?)\]~s';
                if (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $v)) {
                    if ($matches[0][1] != $v) {
                        break;
                    }
                    if (preg_match('~\[(chonso|chon0|chon|bien|bthuc)~s', $matches[0][0], $match2, PREG_OFFSET_CAPTURE, 5)) {
                        $text = $this->bbCodeStep($text, $match2[1][0], $match2[1][1] + $matches[0][1] - 1);
                    } else {
                        $expr = $matches[1][0];
                        $ket = $this->evaluateMathExpression($expr);
                        $text = substr($text, 0, $matches[0][1]) . $ket . substr($text, $matches[0][1] + strlen($matches[0][0]));
                    }
                }
                break;

            case 'chon':
                $pattern = '~\[chon (.*?)\]~s';
                if (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $v)) {
                    if ($matches[0][1] != $v) {
                        break;
                    }
                    if (preg_match('~\[(chonso|chon0|chon|bien|bthuc)~s', $matches[0][0], $match2, PREG_OFFSET_CAPTURE, 5)) {
                        $text = $this->bbCodeStep($text, $match2[1][0], $match2[1][1] + $matches[0][1] - 1);
                    } else {
                        $danhsach = explode(',', $matches[1][0]);
                        $len = count($danhsach);
                        $ngau = rand(0, 1000) % $len;
                        $text = substr($text, 0, $matches[0][1]) . $danhsach[$ngau] . substr($text, $matches[0][1] + strlen($matches[0][0]));
                    }
                }
                break;

            case 'chon0':
                $pattern = '~\[chon0 (.*?)\]~s';
                if (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $v)) {
                    if ($matches[0][1] != $v) {
                        break;
                    }
                    if (preg_match('~\[(chonso|chon0|chon|bien|bthuc)~s', $matches[0][0], $match2, PREG_OFFSET_CAPTURE, 5)) {
                        $text = $this->bbCodeStep($text, $match2[1][0], $match2[1][1] + $matches[0][1] - 1);
                    } else {
                        $danhsach = explode(',', $matches[1][0]);
                        $danhsach[] = '';
                        $len = count($danhsach);
                        $ngau = rand(0, 1000) % $len;
                        $text = substr($text, 0, $matches[0][1]) . $danhsach[$ngau] . substr($text, $matches[0][1] + strlen($matches[0][0]));
                    }
                }
                break;

            case 'chonso':
                $pattern = '~\[chonso (.*?)\]~s';
                if (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $v)) {
                    if ($matches[0][1] != $v) {
                        break;
                    }
                    if (preg_match('~\[(chonso|chon0|chon|bien|bthuc)~s', $matches[0][0], $match2, PREG_OFFSET_CAPTURE, 5)) {
                        $text = $this->bbCodeStep($text, $match2[1][0], $match2[1][1] + $matches[0][1] - 1);
                    } else {
                        $danhsach0 = explode(',', $matches[1][0]);
                        $danhsach = [];
                        foreach ($danhsach0 as $ptu) {
                            $mut = explode('-', $ptu);
                            if (count($mut) == 2 && is_numeric($mut[0]) && is_numeric($mut[1])) {
                                for ($i = (int)$mut[0]; $i <= (int)$mut[1]; $i++) {
                                    $danhsach[] = $i;
                                }
                            } else {
                                $danhsach[] = $ptu;
                            }
                        }
                        $len = count($danhsach);
                        $ngau = rand(0, 1000) % $len;
                        $text = substr($text, 0, $matches[0][1]) . $danhsach[$ngau] . substr($text, $matches[0][1] + strlen($matches[0][0]));
                    }
                }
                break;
        }

        return $text;
    }

    private function evaluateMathExpression(string $expr): string
    {
        $cleanExpr = preg_replace('/[^0-9\+\-\*\/\%\(\)\s]/', '', $expr);
        if (empty($cleanExpr)) {
            return '';
        }
        try {
            $val = eval("return ({$cleanExpr});");
            return (string)$val;
        } catch (\Throwable $e) {
            return '';
        }
    }
}
