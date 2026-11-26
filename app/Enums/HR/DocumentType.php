<?php

declare(strict_types=1);

namespace App\Enums\HR;

enum DocumentType: string
{
    case Passport = 'passport';
    case Diploma = 'diploma';
    case LabourBook = 'labour_book';
    case DrivingLicence = 'driving_licence';
    case MilitaryCertificate = 'military_certificate';
}
