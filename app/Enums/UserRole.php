<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case Coordinator = 'coordinator';
    case Teacher = 'teacher';
}
