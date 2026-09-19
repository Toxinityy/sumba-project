<?php

use Illuminate\Support\Facades\Route;

/*
 | Spec §4: light --accent is 4.26:1 on --surface-sunk and 3.92:1 on
 | --badge-bg, below AA. So accent-coloured text may not sit on those grounds.
 |
 | Three things render accent text: the `text-accent` utility, and the
 | editorial .ed-label / .ed-txtlink, which switch to --badge-ink only under
 | .on-sunk / .on-tint (landing.css), not under the utility grounds bg-sunk /
 | bg-badge. Each is walked up to its NEAREST ground, since a raised card
 | inside a sunk section is a raised ground again.
 */
function accentOnForbiddenGround(string $html): array {
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $has = fn (DOMElement $el, string $class) => in_array($class, preg_split('/\s+/', $el->getAttribute('class')), true);

    $grounds = ['bg-surface', 'bg-raised', 'bg-sunk', 'bg-badge', 'bg-inverse', 'bg-accent',
        'on-surface', 'on-raised', 'on-sunk', 'on-tint', 'on-inverse', 'on-chapter'];
    $violations = [];

    foreach ($xpath->query('//*[@class]') as $el) {
        $utility = $has($el, 'text-accent');
        $editorial = $has($el, 'ed-label') || $has($el, 'ed-txtlink');
        if (! $utility && ! $editorial) {
            continue;
        }

        $ground = null;
        for ($node = $el; $node instanceof DOMElement && $ground === null; $node = $node->parentNode) {
            foreach ($grounds as $g) {
                if ($has($node, $g)) {
                    $ground = $g;
                    break;
                }
            }
        }

        // The editorial classes carry their own override under on-sunk/on-tint.
        $forbidden = $utility
            ? ['bg-sunk', 'bg-badge', 'on-sunk', 'on-tint']
            : ['bg-sunk', 'bg-badge'];

        if (in_array($ground, $forbidden, true)) {
            $violations[] = "<{$el->tagName} class=\"{$el->getAttribute('class')}\"> on {$ground}: "
                .trim(substr($el->textContent, 0, 60));
        }
    }

    return $violations;
}

it('never renders accent text on a sunk or tinted ground', function () {
    $urls = collect(Route::getRoutes()->getRoutesByMethod()['GET'])
        ->filter(fn ($r) => preg_match('/^(id|en)\./', (string) $r->getName()) && ! str_contains($r->uri(), '{'))
        ->map(fn ($r) => '/'.$r->uri())
        ->merge(['/id/sekolah/anakalang', '/en/schools/anakalang', '/id/cerita/ibu-maria-bulu', '/en/stories/ibu-maria-bulu'])
        ->unique();

    expect($urls->count())->toBeGreaterThan(20);

    $violations = [];
    foreach ($urls as $url) {
        foreach (accentOnForbiddenGround($this->get($url)->assertOk()->getContent()) as $v) {
            $violations[] = "{$url}: {$v}";
        }
    }

    expect($violations)->toBe([]);
});
