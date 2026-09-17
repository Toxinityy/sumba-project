<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->guardAgainstUnconfiguredMailInProduction();
    }

    /**
     * C1: a deploy that follows docs/deployment.md step 6 exactly, but
     * skips the mail keys (there is no `.env.example` copy step on cPanel —
     * every key has to be hand-typed), produces two failures at once: real
     * enquiries silently land in a log file instead of an inbox, AND the
     * placeholder address `halo@contoh.org` ("contoh" = Indonesian for
     * "example") gets printed on the public contact page as the ministry's
     * real address. A due-diligence donor reading a placeholder email on a
     * page asking them for money is worse than downtime, so this fails
     * loudly at boot instead of failing silently for months.
     */
    private function guardAgainstUnconfiguredMailInProduction(): void
    {
        if (! $this->app->environment('production')) {
            return;
        }

        if (config('mail.contact_to') === 'halo@contoh.org') {
            throw new \RuntimeException(
                'CONTACT_TO is still the placeholder address (halo@contoh.org). '.
                'Set a real CONTACT_TO in the host .env before serving in production — '.
                'see docs/deployment.md step 6.'
            );
        }

        if (config('mail.default') === 'log') {
            throw new \RuntimeException(
                'MAIL_MAILER is still "log" in production — contact-form enquiries would be '.
                'written to a log file and never delivered. Set MAIL_MAILER (and the SMTP keys '.
                'it needs) in the host .env — see docs/deployment.md step 6.'
            );
        }
    }
}
