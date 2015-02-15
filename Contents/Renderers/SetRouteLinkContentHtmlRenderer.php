<?php
namespace Grout\Cyantree\ManagedModule\Contents\Renderers;

use Cyantree\Grout\Set\Content;
use Cyantree\Grout\Set\ContentRenderer;
use Cyantree\Grout\Tools\StringTools;
use Grout\Cyantree\ManagedModule\Contents\SetRouteLinkContent;

class SetRouteLinkContentHtmlRenderer extends ContentRenderer
{

    public function render(Content $content)
    {
        /** @var $content SetRouteLinkContent */

        $url = StringTools::escapeHtml(
                $content->route->getUrl(
                        array_merge($content->routeParameters, array($content->setIdField => $content->set->getId())),
                        true,
                        $content->urlParameters
                )
        );
        $label = StringTools::escapeHtml($content->linkLabel);


        return "<a href=\"{$url}\">{$label}</a>";
    }
}
