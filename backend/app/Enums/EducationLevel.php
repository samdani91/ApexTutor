<?php
namespace App\Enums;
enum EducationLevel: string {
    case Phd = 'phd';
    case Masters = 'masters';
    case Bachelor = 'bachelor';
    case Hsc = 'hsc';
    case Ssc = 'ssc';
    case OLevel = 'o_level';
    case ALevel = 'a_level';
    case Other = 'other';
}
