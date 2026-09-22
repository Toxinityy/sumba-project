import { initTheme } from "./theme";

initTheme();

const formError = document.querySelector('form [aria-invalid="true"]')
    ?? document.querySelector('form [role="alert"][tabindex="-1"]');
if (formError) {
    addEventListener("load", () => formError.focus(), { once: true });
}

const contactSwitcher = document.querySelector("[data-contact-anchor]");

if (contactSwitcher) {
    const links = [...contactSwitcher.querySelectorAll("a[hreflang]")]
        .map(link => ({ link, pageUrl: link.href }));
    const syncContactLinks = () => {
        const atContact = location.hash === `#${contactSwitcher.dataset.contactAnchor}`;
        for (const { link, pageUrl } of links) {
            link.href = atContact ? link.dataset.contactUrl : pageUrl;
        }
    };

    syncContactLinks();
    addEventListener("hashchange", syncContactLinks);
}
