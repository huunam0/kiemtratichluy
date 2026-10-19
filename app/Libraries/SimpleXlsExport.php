<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * SimpleXlsExport
 *
 * Xuất file Excel định dạng .xlsx chuẩn (OpenXML) bằng PhpOffice\PhpSpreadsheet.
 * Hỗ trợ nhiều sheet, tiếng Việt có dấu, định dạng số, tự động căn chỉnh độ rộng cột
 * và mở mượt mà trên tất cả phiên bản MS Excel, LibreOffice, Google Sheets.
 */
class SimpleXlsExport
{
    protected array $sheets = [];

    /**
     * Thêm một sheet vào file Excel.
     *
     * @param string   $name     Tên sheet (tối đa 31 ký tự theo quy định của Excel)
     * @param array    $headers  Mảng tiêu đề cột ['Họ Tên', 'Điểm', ...]
     * @param array    $rows     Mảng các hàng dữ liệu (mảng hai chiều)
     * @param array    $colTypes Tùy chọn loại cột: 'i' = int, 'n' = float/decimal, 's' = string
     */
    public function addSheet(string $name, array $headers, array $rows, array $colTypes = []): void
    {
        // Excel giới hạn tên sheet tối đa 31 ký tự và không chứa các ký tự \ / ? * : [ ]
        $cleanName = preg_replace('/[\\\\\/\?\*\:\[\]]/', '', $name);
        $cleanName = mb_substr($cleanName, 0, 31, 'UTF-8');

        $this->sheets[] = [
            'name'     => $cleanName,
            'headers'  => $headers,
            'rows'     => $rows,
            'colTypes' => $colTypes,
        ];
    }

    /**
     * Tạo đối tượng Spreadsheet từ dữ liệu các sheets đã thêm.
     */
    public function buildSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        // Xóa sheet mặc định ban đầu
        $spreadsheet->removeSheetByIndex(0);

        $headerStyle = [
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
                'name'  => 'Arial',
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // Màu tím Indigo
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'D1D5DB'],
                ],
            ],
        ];

        $dataBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'E5E7EB'],
                ],
            ],
        ];

        foreach ($this->sheets as $sIndex => $sData) {
            $sheet = $spreadsheet->createSheet($sIndex);
            $sheet->setTitle($sData['name']);

            $colCount = count($sData['headers']);
            if ($colCount === 0) {
                continue;
            }

            $lastColLetter = Coordinate::stringFromColumnIndex($colCount);

            // 1. Thêm dòng tiêu đề (Row 1)
            $sheet->fromArray([$sData['headers']], null, 'A1');
            $sheet->getRowDimension(1)->setRowHeight(28);
            $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray($headerStyle);

            // 2. Thêm dữ liệu (Row 2 trở đi)
            if (!empty($sData['rows'])) {
                $sheet->fromArray($sData['rows'], null, 'A2');
                $rowCount = count($sData['rows']);
                $lastRow = $rowCount + 1;

                // Border cho toàn bộ vùng dữ liệu
                $sheet->getStyle("A2:{$lastColLetter}{$lastRow}")->applyFromArray($dataBorder);
                $sheet->getStyle("A2:{$lastColLetter}{$lastRow}")->getFont()->setName('Arial')->setSize(10);

                // Format từng cột theo colTypes
                foreach ($sData['colTypes'] as $colIdx => $type) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
                    $colRange  = "{$colLetter}2:{$colLetter}{$lastRow}";

                    if ($type === 'n') {
                        // Số thập phân 2 chữ số (VD: 8.50)
                        $sheet->getStyle($colRange)->getNumberFormat()->setFormatCode('#,##0.00');
                        $sheet->getStyle($colRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    } elseif ($type === 'i') {
                        // Số nguyên (VD: 1, 20)
                        $sheet->getStyle($colRange)->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->getStyle($colRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    } else {
                        // Chuỗi
                        $sheet->getStyle($colRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    }
                }
            }

            // 3. Tự động giãn độ rộng các cột
            for ($c = 1; $c <= $colCount; $c++) {
                $letter = Coordinate::stringFromColumnIndex($c);
                $sheet->getColumnDimension($letter)->setAutoSize(true);
            }

            // 4. Freeze dòng tiêu đề đầu tiên
            $sheet->freezePane('A2');
        }

        // Chọn sheet đầu tiên làm active sheet
        if ($spreadsheet->getSheetCount() > 0) {
            $spreadsheet->setActiveSheetIndex(0);
        }

        return $spreadsheet;
    }

    /**
     * Gửi file Excel (.xlsx) về trình duyệt để tải xuống.
     */
    public function download(string $filename = 'export.xlsx'): void
    {
        // Đảm bảo đuôi file là .xlsx
        if (!preg_match('/\.xlsx$/i', $filename)) {
            $filename = preg_replace('/\.xls$/i', '', $filename) . '.xlsx';
        }

        $spreadsheet = $this->buildSpreadsheet();
        $writer = new Xlsx($spreadsheet);

        // Xóa sạch buffer để tránh ký tự rác / BOM làm hỏng file zip xlsx
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer->save('php://output');
        exit;
    }
}
