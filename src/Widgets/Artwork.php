<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Widgets;

use Closure;
use Hirtz\Skeleton\Html\Div;
use Override;
use Stringable;

class Artwork extends \Hirtz\Cms\Widgets\Artwork
{
    protected string|false $hotspotViewFile = 'widgets/_hotspots';

    protected ?Closure $hotspotWrapper = null;

    /**
     * @param Closure(Div): (string|Stringable|null)|null $wrapper
     * @return $this
     */
    public function hotspotWrapper(?Closure $wrapper): static
    {
        $this->hotspotWrapper = $wrapper;
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

        return $this->hotspotWrapper ? ($this->hotspotWrapper)($wrapper) : $wrapper;
    }

    protected function renderHotspots(): ?string
    {
        $hotspots = $this->asset->isAttributeVisible('#hotspots') && $this->hotspotViewFile
            ? ($this->asset->getRelatedRecords()['hotspots'] ?? null)
            : null;

        return $hotspots ? $this->view->render($this->hotspotViewFile, ['hotspots' => $hotspots]) : null;
    }
}
