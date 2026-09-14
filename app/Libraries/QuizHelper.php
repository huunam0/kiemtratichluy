<?php

namespace App\Libraries;

class QuizHelper
{
    public static function solama(int $so = 1): string
    {
        $chusolama = [
            [1000, "M"], [900, "CM"], [500, "D"], [400, "CD"], [100, "C"], [90, "XC"],
            [50, "L"], [40, "XL"], [10, "X"], [9, "IX"], [5, "V"], [4, "IV"], [1, "I"]
        ];
        $kq = "";
        for ($i = 0; $i < count($chusolama); $i++) {
            while ($so >= $chusolama[$i][0]) {
                $kq .= $chusolama[$i][1];
                $so -= $chusolama[$i][0];
            }
        }
        return $kq;
    }

    public static function giaimademuc(string $stt, string $txt, bool $xaotron = false): string
    {
        $cauhoi = explode("«q&a»", $txt);
        $phandau = array_shift($cauhoi);
        $socauhoi = count($cauhoi);

        if ($socauhoi == 0) {
            return self::giaimacauhoi($phandau);
        }

        $danhdau = $socauhoi > 1;
        if ($xaotron) {
            shuffle($cauhoi);
        }
        for ($i = 0; $i < $socauhoi; $i++) {
            $phandau .= self::giaimacauhoi(($danhdau ? ($i + 1) . ". " : "") . $cauhoi[$i], $stt . "$i");
        }
        return $phandau;
    }

    public static function giaimadethi(string $txt): string
    {
        $xaotron = false;
        $demuc = explode("«demuc", $txt);
        $phandau = array_shift($demuc);
        $sodemuc = count($demuc);

        if ($sodemuc == 0) {
            return self::giaimacauhoi($phandau);
        }

        $danhdau = $sodemuc > 1;
        if (substr($demuc[0], 2, 1) == "»") {
            $xaotron = (substr($demuc[0], 0, 1) == "1");
            $demuc[0] = substr($demuc[0], 1);
        }
        if ($xaotron) {
            shuffle($demuc);
        }
        for ($i = 0; $i < $sodemuc; $i++) {
            $s = strstr($demuc[$i], "»", true);
            $phandau .= self::giaimademuc("$i", ($danhdau ? "### " . self::solama($i + 1) . ". " : "") . substr($demuc[$i], strlen($s) + 2), $s == "1");
        }
        return $phandau;
    }

    public static function giaimacauhoi(string $txt, string $ttmuc = ""): string
    {
        $stt = 0;

        // Choice questions
        $pattern = '~«chon1 (.*?)»~s';
        while (preg_match($pattern, $txt, $matches, PREG_OFFSET_CAPTURE)) {
            $muc = explode("¦", $matches[0][0][0] === '«' ? $matches[1][0] : $matches[1][0]);
            if (!isset($muc[3])) $muc[3] = "1";
            if (!isset($muc[4])) $muc[4] = "0";

            $loai = strlen($muc[1]) > 1 ? "checkbox" : "radio";
            $tuychon = str_split($muc[2] . "000");
            $tren1hang = 0;
            if ($tuychon[1] >= "0" && $tuychon[1] <= "9") {
                $tren1hang = intval($tuychon[1]);
                $tuychon[1] = $tuychon[2];
            }
            if ($tuychon[1] == "0") $tuychon[1] = "";

            $thay = "<span class='traloi' diem='" . $muc[3] . "' vid='" . $muc[4] . "' id='cau$ttmuc-$stt'>";
            $mucchon = explode("¸", $muc[0]);
            $cacmucchon = [];
            for ($i = 1; $i <= count($mucchon); $i++) {
                array_push($cacmucchon, [$mucchon[$i - 1], strpos($muc[1], "$i") !== false]);
            }

            if ($tuychon[0] == "1") {
                shuffle($cacmucchon);
            }

            for ($i = 0; $i < count($cacmucchon); $i++) {
                $thay .= "<span style='margin-left: 10px'><input class='cauhoi' type='$loai' dung='" . ($cacmucchon[$i][1] ? '1' : '0') . "' name='cau$ttmuc-$stt'/> ";
                if ($tuychon[1]) {
                    $thay .= chr(ord($tuychon[1]) + $i) . ". ";
                }
                $thay .= $cacmucchon[$i][0] . "</span>";
                if ($tren1hang > 0 && ($i + 1) % $tren1hang == 0) {
                    $thay .= "<br>";
                }
            }
            $thay .= "</span>";
            $txt = substr_replace($txt, $thay, $matches[0][1], strlen($matches[0][0]));
            $stt++;
        }

        // Fill in blank questions
        $pattern = '~«trong (.*?)»~s';
        while (preg_match($pattern, $txt, $matches, PREG_OFFSET_CAPTURE)) {
            $muc = explode("¦", $matches[1][0]);
            if (!isset($muc[2])) $muc[2] = "1";
            if (!isset($muc[3])) $muc[3] = "0";

            $thay = " <input class='traloi cauhoi' type='text' name='chon$stt' size='" . max(1, mb_strlen($muc[0]) - 2) . "' id='chon$stt' dung='" . htmlspecialchars($muc[0], ENT_QUOTES) . "' khop='" . $muc[1] . "' diem='" . $muc[2] . "' vid='" . $muc[3] . "'/> ";
            $txt = str_replace($matches[0][0], $thay, $txt);
            $stt++;
        }

        // Video watch links
        $pattern = '~«xemngay (.*?)»~s';
        while (preg_match($pattern, $txt, $matches, PREG_OFFSET_CAPTURE)) {
            $muc = explode("¦", $matches[1][0] . "¦0¦0");
            $thay = " <span class='xemngay' onclick='playOther(\"" . $muc[1] . "\"," . $muc[2] . "," . $muc[3] . ");'>" . $muc[0] . "</span> ";
            $txt = str_replace($matches[0][0], $thay, $txt);
            $stt++;
        }

        // Essay questions
        $pattern = '~«tuluan (.*?)»~s';
        while (preg_match($pattern, $txt, $matches, PREG_OFFSET_CAPTURE)) {
            $somuc = intval($matches[1][0]);
            $thay = "<form action='/action_page.php'>";
            for ($i = 0; $i < $somuc; $i++) {
                $thay .= "<input type='file' name='filename$i'>";
            }
            $thay .= "<input type='submit' value='Nộp'></form>";
            $txt = str_replace($matches[0][0], $thay, $txt);
            $stt++;
        }

        return $txt;
    }
}
