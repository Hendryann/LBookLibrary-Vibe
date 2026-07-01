<?php

namespace App\Enums;

enum Role: string
{
    case MEMBER = 'member';
    case LIBRARIAN = 'librarian';
    case ADMIN = 'admin';
}