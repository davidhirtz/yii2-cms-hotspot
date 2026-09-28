<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Widgets;

use Closure;
use Hirtz\Cms\Hotspot\Modules\ModuleTrait;
use Hirtz\Skeleton\Html\Div;
use Override;
use Stringable;
use yii\base\ViewNotFoundException;

class Artwork extends \Hirtz\Cms\Widgets\Artwork
{
    use ModuleTrait;

    final public const string DEFAULT_HOTSPOT_VIEW_FILE = '@hotspot/../resources/views/widgets/_hotspots.php';

    protected string|false $hotspotViewFile = 'widgets/_hotspots';

    /**
     * @var list<Closure>|null
     */
    private ?array $hotspotWrapperClosures = null;

    /**
     * @param Closure(Div): Div $wrapper
     */
    public function hotspotWrapper(Closure $wrapper): static
    {
        $this->hotspotWrapperClosures[] = $wrapper;
        return $this;
    }

    public function hotspotViewFile(string|false $hotspotViewFile): static
    {
        $this->hotspotViewFile = $hotspotViewFile;
        return $this;
    }

    #[Override]
    protected function renderMedia(): ?Stringable
    {
        $content = parent::renderMedia();
        $hotspots = $this->renderHotspots();

        if (!$hotspots) {
            return $content;
        }

        $wrapper = Div::make()
            ->class('relative')
            ->content($content, $hotspots);

        return $this->evaluate($this->hotspotWrapperClosures, $wrapper);
    }

    protected function renderHotspots(): ?string
    {
        $hotspots = $this->hotspotViewFile && static::getModule()->allowsHotspots($this->asset)
            ? ($this->asset->getRelatedRecords()['hotspots'] ?? null)
            : null;

        if (!$hotspots) {
            return null;
        }

        // The view is the page's own, resolved beside it; a project that never wrote one gets the bundle's.
        try {
            return $this->view->render($this->hotspotViewFile, ['hotspots' => $hotspots]);
        } catch (ViewNotFoundException) {
            return $this->view->render(self::DEFAULT_HOTSPOT_VIEW_FILE, ['hotspots' => $hotspots]);
        }
    }
}
