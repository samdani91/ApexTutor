<?php
namespace App\Enums;
enum TutorStatus: string {
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
}
