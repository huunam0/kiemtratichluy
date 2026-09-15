<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\FillBlankQuestionModel;
use App\Models\FillBlankQuestionVariantModel;

class FillBlankImportSeeder extends Seeder
{
    public function run()
    {
        $questionModel = new FillBlankQuestionModel();
        $variantModel  = new FillBlankQuestionVariantModel();

        $baseDir = ROOTPATH . 'temp/dien/chude';
        if (!is_dir($baseDir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($baseDir));
        $grouped = [];

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'html') {
                $filePath = $file->getPathname();
                $filename = $file->getFilename(); // e.g. ucln_1.html or max.html
                $relDir   = basename($file->getPath()); // e.g. boiuoc

                $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME); // ucln_1 or max
                // Group by question prefix (e.g., ucln_1 -> ucln, max_1 -> max, max -> max)
                $prefix = preg_replace('/_\d+$/', '', $nameWithoutExt);

                $title = strtoupper($relDir) . ' - ' . ucfirst($prefix);

                $content = file_get_contents($filePath);

                if (!isset($grouped[$title])) {
                    $grouped[$title] = [
                        'subject'     => 'Tin học',
                        'grade_level' => 11,
                        'variants'    => []
                    ];
                }

                $grouped[$title]['variants'][] = [
                    'variant_name' => $nameWithoutExt,
                    'content_raw'  => $content
                ];
            }
        }

        foreach ($grouped as $qTitle => $data) {
            $existing = $questionModel->where('title', $qTitle)->first();
            if ($existing) {
                $qId = $existing['id'];
            } else {
                $qId = $questionModel->insert([
                    'teacher_id'  => 1,
                    'school_id'   => 1,
                    'title'       => $qTitle,
                    'subject'     => $data['subject'],
                    'grade_level' => $data['grade_level'],
                ]);
            }

            foreach ($data['variants'] as $v) {
                $varExist = $variantModel->where('question_id', $qId)
                                         ->where('variant_name', $v['variant_name'])
                                         ->first();
                if (!$varExist) {
                    $variantModel->insert([
                        'question_id'  => $qId,
                        'variant_name' => $v['variant_name'],
                        'content_raw'  => $v['content_raw']
                    ]);
                }
            }
        }
    }
}
