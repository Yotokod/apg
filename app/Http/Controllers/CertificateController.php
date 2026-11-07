<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\CertificateService;

class CertificateController extends Controller
{
    /** Public verification + printable certificate (lookup by unguessable code only). */
    public function show(string $code)
    {
        $certificate = Certificate::where('code', strtoupper($code))->firstOrFail();

        return response()->view('certificate.show', ['certificate' => $certificate, 'specimen' => false])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /** Admin preview with the current identity settings; nothing is stored. */
    public function preview(CertificateService $certificates)
    {
        abort_unless(in_array(auth()->user()?->role, ['admin', 'superadmin'], true), 403);

        return response()->view('certificate.show', ['certificate' => $certificates->specimen(), 'specimen' => true])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
