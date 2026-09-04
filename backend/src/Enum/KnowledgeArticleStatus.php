<?php

namespace App\Enum;

enum KnowledgeArticleStatus: string
{
    case BROUILLON = 'BROUILLON';
    case VALIDATION = 'VALIDATION';
    case PUBLIE = 'PUBLIE';
}

