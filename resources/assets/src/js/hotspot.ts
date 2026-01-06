interface HotspotConfig {
    id: string;
    formName: string;
    url: string;
    hotspots: HotspotData[];
}

interface HotspotData {
    id: string;
    displayName: string;
    x: number;
    y: number;
    url: string;
}

const csrfToken = Object.values(JSON.parse(document.querySelector('#wrap')!.getAttribute('hx-headers') as string) as Object).pop();

export const post = (url: string, formName: string, x: number, y: number) => {
    const params = new URLSearchParams();
    params.append(`${formName}[x]`, String(x));
    params.append(`${formName}[y]`, String(y));

    return fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken,
            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
        } as HeadersInit,
        body: params.toString(),
    });
};

export default (config: HotspotConfig) => {
    const $image = document.querySelector('[data-id="image"]') as HTMLImageElement;
    const $canvas = $image.parentElement!;

    const hotspots = config.hotspots || [];

    const setHotspot = (data: HotspotData) => {

        const btn = document.createElement('a') as HTMLAnchorElement;

        btn.href = data.url;
        btn.className = 'hotspot-btn';
        btn.title = data.displayName;
        btn.innerHTML = '<i class="hotspot-icon fas fa-plus"></i>';

        btn.setAttribute('data-tooltip', '');

        $canvas.appendChild(btn);

        const btnOffsetX = btn.offsetWidth / 2;
        const btnOffsetY = btn.offsetHeight / 2;

        btn.style.left = `calc(${data.x}% - ${btnOffsetX}px)`;
        btn.style.top = `calc(${data.y}% - ${btnOffsetY}px)`;
        btn.style.zIndex = String(zIndex++);

        // .draggable({
        //     containment: $canvas,
        //     start: function () {
        //         $btn.css('z-index', zIndex + 1).tooltip('disable').tooltip('hide').addClass('dragging');
        //     },
        //     stop: function () {
        //         $.post(data.url, setCoordinateFormFields({
        //             x: ($btn.position().left + btnOffsetX) / $canvas.width() * 100,
        //             y: ($btn.position().top + btnOffsetY) / $canvas.height() * 100,
        //             position: zIndex + 1
        //         }));
        //
        //         setTimeout(function () {
        //             $btn.tooltip('enable').removeClass('dragging');
        //         }, 1);
        //     }
        // })
        // .on('click', function (e) {
        //     if ($btn.hasClass('dragging')) {
        //         e.preventDefault();
        //     }
        // });
    }

    let zIndex = 0;
    let i: number;

    $canvas.classList.add('hotspot-canvas');

    for (i = 0; i < hotspots.length; i++) {
        setHotspot(hotspots[i]);
    }

    $image.addEventListener('dblclick', function (e: MouseEvent) {
        const rect = $image.getBoundingClientRect();
        const rawX = (e.clientX - rect.left) / rect.width * 100;
        const rawY = (e.clientY - rect.top) / rect.height * 100;

        const x = Math.round(rawX * 100) / 100;
        const y = Math.round(rawY * 100) / 100;

        post(config.url, config.formName, x, y)
            .then((response) => response.json())
            .then((data: HotspotData) => setHotspot(data));
    });
};