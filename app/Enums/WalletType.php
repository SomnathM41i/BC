<?php

namespace App\Enums;

enum WalletType: string
{
    case Business = 'business';
    case Project = 'project';
    case Owner = 'owner';
    case Contractor = 'contractor';
}
