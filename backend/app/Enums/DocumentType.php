<?php
namespace App\Enums;
enum DocumentType: string {
    case UniversityId = 'university_id';
    case Nid = 'nid';
    case Passport = 'passport';
    case BirthCertificate = 'birth_certificate';
    case SscMarksheet = 'ssc_marksheet';
    case SscCertificate = 'ssc_certificate';
    case HscMarksheet = 'hsc_marksheet';
    case HscCertificate = 'hsc_certificate';
    case OLevelMarksheet = 'o_level_marksheet';
    case ALevelMarksheet = 'a_level_marksheet';
    case Additional = 'additional';
}
