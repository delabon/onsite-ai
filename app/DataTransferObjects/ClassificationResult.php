<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Enums\MessageCategory;

final readonly class ClassificationResult
{
    public function __construct(
        public bool $success,
        public MessageCategory $category,
        public string $confidence,
        public string $rawResponse = '',
        public string $modelUsed = '',
        public string $error = ''
    ) {
    }

    public function toArray(): array
    {
        $arr = get_object_vars($this);
        $arr['category'] = $this->category->value;

        return $arr;
    }
}
