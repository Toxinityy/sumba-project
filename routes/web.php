<?php

use App\ViewModels\PartnerData;
use App\ViewModels\PostData;
use App\ViewModels\SchoolData;
use App\ViewModels\StatData;
use App\ViewModels\TierData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/*
 | The root redirects rather than serving content, so there is exactly one
 | canonical URL per page per locale. Accept-Language only decides WHICH
 | locale; it never causes the same URL to serve two different languages.
 */
Route::get('/', function (Request $request) {
    $preferred = $request->getPreferredLanguage(['id', 'en']);

    return redirect('/'.($preferred === 'en' ? 'en' : config('locales.default')));
});

foreach (config('locales.supported') as $locale) {
    $segments = collect(config('locales.segments'))
        ->map(fn (array $paths) => $paths[$locale]);

    // Each locale gets its own literal prefix (rather than a single {locale}
    // wildcard) because the path segments themselves are translated, so the
    // route table differs per locale, not just the prefix. The locale is
    // attached as a route default below so the setlocale middleware — which
    // reads $request->route('locale') — can see it and 404 unknown locales
    // instead of silently falling back.
    Route::prefix($locale)
        ->middleware('setlocale')
        ->name("{$locale}.")
        ->group(function () use ($segments, $locale) {
            // Pages that need request-time data (the fixtures below resolve
            // against the locale the setlocale middleware just set) use
            // Route::get + view() rather than Route::view(), whose $data
            // argument is evaluated once at route registration and can't
            // see app()->getLocale(). Pages whose content is entirely in the
            // translation files stay on Route::view().
            // The landing page carries the whole narrative (see
            // resources/views/pages/home.blade.php), so it pulls from every
            // fixture rather than three. 'anakalang' is the lead school and is
            // removed from the rail list below it so it appears once, not
            // twice; the voice is Ibu Maria Bulu's own quote from her profile,
            // rather than a fourth copy of the same words in a lang file.
            Route::get('/', fn () => view('pages.home', [
                'stats' => StatData::scale(),
                'challenge' => StatData::challenge(),
                'featuredSchool' => SchoolData::find('anakalang'),
                'schools' => array_values(array_filter(
                    SchoolData::all(),
                    fn (array $school) => $school['slug'] !== 'anakalang',
                )),
                'stories' => PostData::recent(3),
                'voice' => PostData::find('ibu-maria-bulu')['quote'],
                'partners' => PartnerData::all(),
            ]))->name('home')->defaults('locale', $locale);

            // About and Children's Homes moved off Route::view() (I5): both
            // now resolve a fixture (App\ViewModels\AboutData / HomeData)
            // that reads app()->getLocale(), which Route::view()'s $data
            // argument can't see (it's evaluated once at registration).
            Route::get($segments['about'], fn () => view('pages.about', [
                'about' => \App\ViewModels\AboutData::get(),
            ]))->name('about')->defaults('locale', $locale);

            Route::get($segments['schools'], fn () => view('pages.schools', [
                'schools' => SchoolData::all(),
            ]))->name('schools.index')->defaults('locale', $locale);

            // {slug} is a real URI parameter, so LocalizedUrl's route-parameter
            // forwarding (App\Support\LocalizedUrl::parametersFor) carries it
            // across the language switch without extra wiring here.
            Route::get($segments['schools'].'/{slug}', function (string $slug) {
                $school = SchoolData::find($slug);

                // A school with only the directory shape (no 'lede') has no
                // detail page yet — 404 rather than render a half-built page.
                abort_unless($school !== null && isset($school['lede']), 404);

                return view('pages.school', ['school' => $school]);
            })->name('schools.show')->defaults('locale', $locale);

            Route::get($segments['homes'], fn () => view('pages.homes', [
                'home' => \App\ViewModels\HomeData::get(),
            ]))->name('homes.index')->defaults('locale', $locale);

            Route::get($segments['stories'], fn () => view('pages.stories', [
                // Ask for more than exist: the index shows every story there
                // is, and grows without a change here when posts are added.
                'stories' => PostData::recent(24),
            ]))->name('stories.index')->defaults('locale', $locale);

            // Story detail: Hero → Lede → Quote → Next step (spec §5). {slug}
            // is a real URI parameter, same forwarding note as schools.show
            // above. Only PostData::recent()'s three profiles have full
            // detail content; a photo-essay slug (or an unknown one) 404s
            // rather than rendering a page with no lede/quote to show.
            Route::get($segments['stories'].'/{slug}', function (string $slug) {
                $post = PostData::find($slug);

                abort_unless($post !== null && isset($post['quote']), 404);

                return view('pages.story', ['post' => $post]);
            })->name('stories.show')->defaults('locale', $locale);

            Route::get($segments['give'], fn () => view('pages.give', [
                'tiers' => TierData::all(),
            ]))->name('give')->defaults('locale', $locale);

            Route::view($segments['contact'], 'pages.contact')->name('contact')->defaults('locale', $locale);

            // The enquiry this site's primary CTA ("Partner with us") leads
            // to. Spam protection is a honeypot plus a throttle, both of
            // which cost nothing and block the automated volume a small
            // ministry site attracts; neither asks a CSR officer to read
            // distorted letters to talk to us.
            Route::post($segments['contact'], function (Request $request) {
                // The honeypot field is hidden from sighted users and from
                // screen readers, so anything in it is a bot. 422 rather
                // than a redirect: nothing here should look like success.
                abort_if(filled($request->input('website')), 422);

                $fields = $request->validate([
                    'name' => ['required', 'string', 'max:120'],
                    'organisation' => ['nullable', 'string', 'max:120'],
                    'email' => ['required', 'email', 'max:254'],
                    'message' => ['required', 'string', 'max:5000'],
                ]);

                // Mail::raw, not a Mailable and a Blade template: this is an
                // internal notification with four fields, read by one person.
                // replyTo makes the reply go to the enquirer rather than to
                // the server's own from-address.
                Mail::raw(
                    __('contact.form.heading')."

".
                    __('contact.form.name').': '.$fields['name']."
".
                    __('contact.form.organisation').': '.($fields['organisation'] ?? '—')."
".
                    __('contact.form.email').': '.$fields['email']."

".
                    $fields['message'],
                    fn ($mail) => $mail->to(config('mail.contact_to'))
                        ->replyTo($fields['email'], $fields['name'])
                        ->subject(__('contact.form.heading').' — '.$fields['name'])
                );

                return back()->with('contact.sent', true);
            })->middleware('throttle:5,1')->name('contact.send')->defaults('locale', $locale);
            Route::view($segments['safeguarding'], 'pages.safeguarding')->name('safeguarding')->defaults('locale', $locale);

            // Gallery, Partners, Impact and Projects are deferred past launch
            // (spec §10) — built in this pass so the whole site is clickable
            // for review, not because launch scope changed.
            Route::get($segments['gallery'], fn () => view('pages.gallery', [
                'essays' => PostData::photoEssays(),
            ]))->name('gallery.index')->defaults('locale', $locale);

            Route::get($segments['partners'], fn () => view('pages.partners'))
                ->name('partners')->defaults('locale', $locale);

            Route::get($segments['impact'], fn () => view('pages.impact', [
                'stats' => StatData::all(),
                'stories' => PostData::recent(3),
            ]))->name('impact')->defaults('locale', $locale);

            Route::get($segments['projects'], fn () => view('pages.projects'))
                ->name('projects')->defaults('locale', $locale);
        });
}

// A component gallery, not a public page. Registered outside production so
// the design system can be reviewed on staging without appearing on the site.
if (! app()->environment('production')) {
    Route::view('/gallery', 'gallery')->name('gallery');
}
