@php
    try {
        $metaPixelRaw = \App\Models\Setting::get('meta_pixel_id');
    } catch (\Throwable $e) {
        $metaPixelRaw = null;
    }

    $metaPixelId = is_string($metaPixelRaw) ? preg_replace('/\D+/', '', $metaPixelRaw) : null;
    $metaPixelId = is_string($metaPixelId) ? trim($metaPixelId) : null;

    if (!is_string($metaPixelId) || $metaPixelId === '') {
        $metaPixelId = null;
    }

    $metaPixelEventsRaw = session('meta_pixel_events', []);
    $metaPixelEvents = [];

    if (is_array($metaPixelEventsRaw)) {
        foreach ($metaPixelEventsRaw as $metaPixelEvent) {
            if (!is_array($metaPixelEvent)) {
                continue;
            }

            $eventName = isset($metaPixelEvent['event']) ? trim((string) $metaPixelEvent['event']) : '';
            if ($eventName === '') {
                continue;
            }

            $payload = isset($metaPixelEvent['payload']) && is_array($metaPixelEvent['payload'])
                ? $metaPixelEvent['payload']
                : [];

            $metaPixelEvents[] = [
                'event' => $eventName,
                'payload' => $payload,
            ];
        }
    }
@endphp

@if($metaPixelId)
    <script>
        (function (w, d, s, u, n, t, e) {
            if (w.fbq) return;
            n = w.fbq = function () {
                n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
            };
            if (!w._fbq) w._fbq = n;
            n.push = n;
            n.loaded = true;
            n.version = '2.0';
            n.queue = [];
            t = d.createElement(s);
            t.async = true;
            t.src = u;
            e = d.getElementsByTagName(s)[0];
            e.parentNode.insertBefore(t, e);
        })(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

        fbq('init', '{{ $metaPixelId }}');

        window.mailpurseMetaTrackCustom = function (eventName, payload) {
            if (typeof window.fbq !== 'function') {
                return;
            }

            window.fbq('track', eventName, payload || {});
        };

        const mailpurseTrackMetaPageView = () => {
            if (typeof window.fbq === 'function') {
                window.fbq('track', 'PageView');
            }
        };

        const mailpurseTrackServerMetaEvents = () => {
            if (typeof window.mailpurseMetaTrackCustom !== 'function') {
                return;
            }

            const events = @json($metaPixelEvents);
            if (!Array.isArray(events) || events.length === 0) {
                return;
            }

            events.forEach((eventItem) => {
                if (!eventItem || typeof eventItem.event !== 'string' || eventItem.event.trim() === '') {
                    return;
                }

                const payload = eventItem.payload && typeof eventItem.payload === 'object'
                    ? eventItem.payload
                    : {};

                window.mailpurseMetaTrackCustom(eventItem.event, payload);
            });
        };

        mailpurseTrackMetaPageView();
        mailpurseTrackServerMetaEvents();

        document.addEventListener('turbo:load', mailpurseTrackMetaPageView);
    </script>
    <noscript>
        <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1" alt="" />
    </noscript>
@endif
