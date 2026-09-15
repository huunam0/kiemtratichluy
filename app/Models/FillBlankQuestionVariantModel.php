<?php

namespace App\Models;

use CodeIgniter\Model;

class FillBlankQuestionVariantModel extends Model
{
    protected $table            = 'fill_blank_question_variants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'question_id', 'variant_name', 'content_raw'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function getVariantsByQuestion(int $questionId)
    {
        return $this->where('question_id', $questionId)->orderBy('id', 'ASC')->findAll();
    }

    public function getRandomVariant(int $questionId)
    {
        $variants = $this->getVariantsByQuestion($questionId);
        if (empty($variants)) {
            return null;
        }
        return $variants[array_rand($variants)];
    }
}
