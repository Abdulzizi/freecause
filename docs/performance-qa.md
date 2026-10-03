# Performance optimization manual QA

Run on the local Laravel site with test accounts and test petitions. These are
manual checks to perform; the checklist is not evidence of browser execution.

## Native signing feedback

- Guest, both stack and split forms: enter name and valid email, tick all consents,
  submit. Button immediately dims and shows spinner; confirmation page appears.
  Confirm signing and verify the normal thanks page and one signature.
- Guest validation: omit a consent, submit, verify the existing error appears and
  the button remains enabled without spinner. Correct it and submit normally.
- Verified logged-in user: submit comment/consents, verify confirmation and final
  signing still work. Already-signed user retains the existing signed message.
- Google OAuth enabled: verify the Google link remains clickable and unchanged;
  after OAuth return, submit the confirmation form normally.
- Double click or press Enter repeatedly while throttling the local network:
  only one native POST starts for the form. A request slower than eight seconds
  must keep the button disabled until navigation completes.
- Browser Offline mode before submit: a connection error appears inline, no POST
  starts, button stays usable. Return Online and retry; error clears and POST
  proceeds.
- Simulate a failed navigation after submission: the browser handles the native
  network failure. Use Back and verify the restored form has an enabled button
  without spinner. Native POST cannot observe HTTP/network response failures
  from the outgoing document; no AJAX callback is introduced.
- Use Back after successful navigation: form is usable without stale busy state.
- Enable reduced motion: the spinner remains visible without rotation.
- Disable JavaScript: both stages retain ordinary native form submission.

## Petition images

- Petition detail with cover: head preload and detail image use the same URL;
  the main image has high fetch priority, eager loading, async decoding.
- Petition detail without cover: no empty image preload; normal fallback remains.
- Home: no detail-cover preload; the single featured cover remains eager, with
  async decoding. Magazine images use lazy loading and async decoding.
- Petition list and category pages: no detail-cover preload; their petition
  listings render text rather than cover images.
