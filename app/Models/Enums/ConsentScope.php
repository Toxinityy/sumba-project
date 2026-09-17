<?php

namespace App\Models\Enums;

/*
 | Consent is per-use (spec §9): permission for a printed newsletter is not
 | permission for a public website. One record carries exactly one scope, so
 | "is this cleared for the web?" is a scope comparison rather than a judgement
 | call an editor makes from a free-text note.
 */
enum ConsentScope: string
{
    case Web = 'web';
    case Print = 'print';
    case Internal = 'internal';
}
