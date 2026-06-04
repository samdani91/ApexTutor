<?php
namespace App\Enums;
enum UserRole: string {
    case Tutor = 'tutor';
    case Guardian = 'guardian';
    case Student = 'student';
    case SuperAdmin = 'super_admin';
}
