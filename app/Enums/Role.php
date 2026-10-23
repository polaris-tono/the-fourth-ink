<?php

namespace App\Enums;

enum Role: string
{
    case Reader = 'reader';
    case Contributor = 'contributor';
    case Admin = 'admin';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}