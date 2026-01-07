// bundles/yii2-cms-hotspot/resources/assets/src/js/hotspot.ts

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

const csrfToken = Object.values(
    JSON.parse(document.querySelector('#wrap')!.getAttribute('hx-headers') as string) as object
).pop();

export const post = (url: string, formName: string, x: number, y: number, position: number) => {
    const params = new URLSearchParams();
    params.append(`${formName}[x]`, String(x));
    params.append(`${formName}[y]`, String(y));
    params.append(`${formName}[position]`, String(position));

    return fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken as string,
            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest',
        } as HeadersInit,
        body: params.toString(),
    });
};

export default (config: HotspotConfig) => {
    const $image = document.querySelector('[data-id="image"]') as HTMLImageElement;
    const $canvas = $image.parentElement!;

    const hotspots = config.hotspots || [];

    const clamp = (value: number, min: number, max: number) => Math.min(max, Math.max(min, value));

    const setHotspot = (data: HotspotData) => {
        const $btn = document.createElement('a') as HTMLAnchorElement;

        $btn.href = data.url;
        $btn.className = 'hotspot-btn';
        $btn.title = data.displayName;
        $btn.innerHTML = '<i class="hotspot-icon fas fa-plus"></i>';
        $btn.setAttribute('data-tooltip', '');
        $btn.style.position = 'absolute';
        $btn.style.touchAction = 'none';
        $btn.style.userSelect = 'none';

        $canvas.appendChild($btn);
        $canvas.style.zIndex = '1';

        const btnOffsetX = $btn.offsetWidth / 2;
        const btnOffsetY = $btn.offsetHeight / 2;

        let isPointerDown = false;
        let didDrag = false;
        let startPointerX = 0;
        let startPointerY = 0;
        let startLeft = 0;
        let startTop = 0;

        $btn.style.left = `calc(${data.x}% - ${btnOffsetX}px)`;
        $btn.style.top = `calc(${data.y}% - ${btnOffsetY}px)`;
        $btn.style.zIndex = String(zIndex++);

        const setDragging = (dragging: boolean) => {
            if (dragging) {
                const $tooltip: HTMLElement | null = $canvas.querySelector('.tooltip');

                if ($tooltip) {
                    $tooltip.remove();
                }

                $btn.style.zIndex = String(zIndex + 1);
            }
        };

        const withinCanvas = (left: number, top: number) => {
            const maxLeft = $canvas.clientWidth - $btn.offsetWidth;
            const maxTop = $canvas.clientHeight - $btn.offsetHeight;
            return {
                left: clamp(left, 0, Math.max(0, maxLeft)),
                top: clamp(top, 0, Math.max(0, maxTop)),
            };
        };

        const finishDrag = () => {
            if (!isPointerDown) {
                return;
            }

            isPointerDown = false;

            if (didDrag) {
                const x = ($btn.offsetLeft + btnOffsetX) / $canvas.clientWidth * 100;
                const y = ($btn.offsetTop + btnOffsetY) / $canvas.clientHeight * 100;

                void post(data.url, config.formName, x, y, zIndex + 1).then(() => {
                    didDrag = false;
                });
            }

            setTimeout(() => setDragging(false), 1000);
        };

        $btn.addEventListener('pointerdown', (e: PointerEvent) => {
            if (e.button !== 0) {
                return;
            }

            isPointerDown = true;
            didDrag = false;
            startPointerX = e.clientX;
            startPointerY = e.clientY;

            startLeft = Number.parseFloat($btn.style.left) || $btn.offsetLeft;
            startTop = Number.parseFloat($btn.style.top) || $btn.offsetTop;

            $btn.setPointerCapture(e.pointerId);
            setDragging(true);
            e.preventDefault();
        });

        $btn.addEventListener('pointermove', (e: PointerEvent) => {
            if (!isPointerDown) return;

            const dx = e.clientX - startPointerX;
            const dy = e.clientY - startPointerY;

            if (!didDrag && (Math.abs(dx) > 2 || Math.abs(dy) > 2)) {
                didDrag = true;
            }

            const next = withinCanvas(startLeft + dx, startTop + dy);

            $btn.style.left = `${next.left}px`;
            $btn.style.top = `${next.top}px`;
        });

        $btn.addEventListener('pointerup', () => finishDrag());
        $btn.addEventListener('pointercancel', () => finishDrag());

        $btn.addEventListener('click', (e: MouseEvent) => {
            if (didDrag) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
            }
        });

        return $btn;
    };

    let zIndex = 0;

    for (let i = 0; i < hotspots.length; i++) {
        setHotspot(hotspots[i]);
    }

    $image.addEventListener('dblclick', (e: MouseEvent) => {
        const rect = $image.getBoundingClientRect();
        const rawX = (e.clientX - rect.left) / rect.width * 100;
        const rawY = (e.clientY - rect.top) / rect.height * 100;

        const x = Math.round(rawX * 100) / 100;
        const y = Math.round(rawY * 100) / 100;

        post(config.url, config.formName, x, y, zIndex + 1)
            .then((response) => response.json())
            .then((data: HotspotData) => {
                const $hotspot = setHotspot(data);

                document.dispatchEvent(new CustomEvent('tooltip:init', {
                    detail: {
                        hotspots: [$hotspot]
                    }
                }));
            });
    });
};
