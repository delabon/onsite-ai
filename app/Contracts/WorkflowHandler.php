<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;

interface WorkflowHandler
{
    public function handle(ParsedMessage $message, ClassificationResult $classification): void;
}
